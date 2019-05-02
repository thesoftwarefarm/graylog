<?php

namespace TsfCorp\Graylog\Tests;

use Psr\Log\LogLevel;
use TsfCorp\Graylog\GraylogMessage;
use TsfCorp\Graylog\Jobs\GraylogJob;
use TsfCorp\Graylog\Models\GraylogModel;

class GraylogMessageTest extends TestCase
{
    public function test_all_details_are_saved_in_database()
    {
        $message = (new GraylogMessage)
            ->setLevel(GraylogMessage::ERROR)
            ->setShortMessage('Short message.')
            ->setFullMessage('Full message.')
            ->setSubsystem('subsystem')
            ->setAdditional('custom_1', 'value_1')
            ->setAdditional('custom_2', 'value_2')
            ->setContext([
                'prop' => 'value'
            ])
            ->enqueue();

        $this->assertEquals(config('graylog.project'), $message->getModel()->project);
        $this->assertNotEmpty($message->getModel()->payload);
        $this->assertEquals('pending', $message->getModel()->status);

        $payload = json_decode($message->getModel()->payload);

        $this->assertEquals(config('graylog.project'), $payload->project);
        $this->assertEquals('Short message.', $payload->short_message);
        $this->assertEquals('Full message.', $payload->full_message);
        $this->assertEquals(GraylogMessage::ERROR, $payload->level);
        $this->assertEquals('subsystem', $payload->additionals->subsystem);
        $this->assertEquals('value_1', $payload->additionals->custom_1);
        $this->assertEquals('value_2', $payload->additionals->custom_2);
        $this->assertEquals(json_encode(['prop' => 'value']), $payload->additionals->context);
        $this->assertNotEmpty($payload->timestamp);
    }

    public function test_throws_exception_when_creating_gelf_message_if_payload_is_malformed()
    {
        $message = new GraylogModel;
        $message->payload = '{prop:value}';
        $message->save();

        $this->expectExceptionMessage('Malformed json payload in message id: '.$message->id);

        $message->toGelfMessage();
    }

    public function test_gelf_message_is_created_based_on_payload()
    {
        $message = (new GraylogMessage)
            ->setLevel(GraylogMessage::ERROR)
            ->setShortMessage('Short message.')
            ->setFullMessage('Full message.')
            ->setSubsystem('my_subsystem')
            ->setAdditional('custom_1', 'value_1')
            ->setAdditional('custom_2', 'value_2')
            ->setContext([
                'prop' => 'value'
            ])
            ->enqueue();

        $gelf_message = $message->getModel()->toGelfMessage();

        $this->assertEquals(config('graylog.project'), $gelf_message->getHost());
        $this->assertEquals(LogLevel::ERROR, $gelf_message->getLevel());
        $this->assertEquals('Short message.', $gelf_message->getShortMessage());
        $this->assertEquals('Full message.', $gelf_message->getFullMessage());
        $this->assertNotEmpty($gelf_message->getTimestamp());

        $additionals = [
            "subsystem" => "my_subsystem",
            "context" => '{"prop":"value"}',
            "custom_1" => "value_1",
            "custom_2" => "value_2",
        ];

        $this->assertEquals($additionals, $gelf_message->getAllAdditionals());
    }

    public function test_status_is_changed_when_a_job_is_dispatched()
    {
        $message = new GraylogModel;
        $message->payload = '{prop:value}';
        $message->save();

        $this->expectsJobs(GraylogJob::class);

        $message->dispatchJob();

        $this->assertEquals('queued', $message->fresh()->status);
    }

    public function test_enqueue_method_inserts_the_record_and_job_not_dispatched()
    {
        $this->doesntExpectJobs(GraylogJob::class);

        $message = (new GraylogMessage)->enqueue();

        $this->assertEquals('pending', $message->getModel()->status);
    }

    public function test_dispatch_method_throws_exception_if_model_not_set()
    {
        $this->expectExceptionMessage('There is no message to be dispatched.');

        (new GraylogMessage)->dispatch();
    }

    public function test_message_is_added_in_database_and_job_dispatched()
    {
        $this->expectsJobs(GraylogJob::class);

        (new GraylogMessage)->enqueue()->dispatch();
    }
}