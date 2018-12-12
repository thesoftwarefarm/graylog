<?php

namespace TsfCorp\Graylog\Jobs;

use Exception;
use Gelf\Publisher;
use Gelf\Transport\HttpTransport;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Throwable;
use TsfCorp\Graylog\Models\GraylogModel;

class GraylogJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var $id
     */
    private $id;
    /**
     * @var \TsfCorp\Graylog\Models\GraylogModel
     */
    private $message;

    /**
     * GraylogJob constructor.
     *
     * @param $id
     */
    public function __construct($id)
    {
        $this->id = $id;
        $this->message = GraylogModel::find($this->id);
    }

    /**
     *
     * @throws \Exception
     */
    public function handle()
    {
        if ( ! $this->message)
        {
            throw new Exception('Message id [' . $this->id . '] not found.');
        }

        $publisher = new Publisher(new HttpTransport(config('graylog.ip'), config('graylog.port')));

        $this->send($publisher);
    }

    /**
     * Sends a message to graylog server
     *
     * @param \Gelf\Publisher $publisher
     * @return bool
     */
    public function send(Publisher $publisher)
    {
        if(config('app.env') != 'production')
        {
            $this->message->status = 'failed';
            $this->message->notes = 'Pushing to graylog is disabled in non production environment.';
            $this->message->save();

            return false;
        }

        if($this->message->retries >= config('graylog.max_retries'))
        {
            $this->message->status = 'failed';
            $this->message->notes = 'Max retry limit reached.';
            $this->message->save();

            return false;
        }

        try
        {
            $message = $this->message->toGelfMessage();
        }
        catch (Throwable $t)
        {
            $this->message->status = 'failed';
            $this->message->notes = $t->getMessage();
            $this->message->save();

            return false;
        }

        try
        {
            // send to Graylog server
            $publisher->publish($message);

            // delete the record
            $this->message->delete();
        }
        catch (Throwable $t)
        {
            $this->message->status = 'failed';
            $this->message->notes = $t->getMessage();
            $this->message->save();

            $this->message->retry();

            return false;
        }

        return true;
    }
}