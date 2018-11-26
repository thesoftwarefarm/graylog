<?php

namespace TsfCorp\Graylog\Tests;

use Exception;
use Gelf\Publisher;
use Mockery;
use TsfCorp\Graylog\GraylogMessage;
use TsfCorp\Graylog\Jobs\GraylogJob;
use TsfCorp\Graylog\Models\GraylogModel;

class GraylogMessageJobTest extends TestCase
{
    public function test_job_throws_exception_if_record_not_found()
    {
        $this->expectExceptionMessage('Message id [0] not found.');

        (new GraylogJob(0))->handle();
    }

    public function test_send_method_returns_false_if_gelf_message_can_not_be_created()
    {
        $message = GraylogModel::forceCreate([
            'payload' => '{prop:value}'
        ]);

        $job = new GraylogJob($message->id);

        $publisher = Mockery::mock(Publisher::class);

        $this->assertFalse($job->send($publisher));

        // message should not be deleted
        $this->assertNotNull(GraylogModel::find($message->id));
    }

    public function test_send_method_returns_false_if_when_sending_to_graylog_an_exception_is_thrown()
    {
        $message = (new GraylogMessage)->enqueue();
        $job = new GraylogJob($message->getModel()->id);

        $publisher = Mockery::mock(Publisher::class);
        $publisher->shouldReceive('publish')->andThrow(Exception::class, 'Some exception');

        $this->assertFalse($job->send($publisher));

        // message should not be deleted
        $this->assertNotNull(GraylogModel::find($message->getModel()->id));
    }

    public function test_send_method_returns_true_if_message_is_sent()
    {
        $message = (new GraylogMessage)->enqueue();
        $job = new GraylogJob($message->getModel()->id);

        $publisher = Mockery::mock(Publisher::class);
        $publisher->shouldReceive('publish');

        $this->assertTrue($job->send($publisher));

        // if message was successfully sent to graylog, then the record should be deleted
        $this->assertNull(GraylogModel::find($message->getModel()->id));
    }
}