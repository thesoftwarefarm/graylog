<?php

namespace TsfCorp\Graylog\Models;

use Exception;
use Gelf\Message;
use Illuminate\Database\Eloquent\Model;
use TsfCorp\Graylog\Jobs\GraylogJob;

class GraylogModel extends Model
{
    protected $table = 'graylog';

    /**
     * @return \Illuminate\Config\Repository|mixed|string
     */
    public function getConnectionName()
    {
        return config('graylog.database_connection');
    }

    /**
     * @return \Gelf\Message
     * @throws \Exception
     */
    public function toGelfMessage()
    {
        $payload = json_decode($this->payload, true);

        if(json_last_error())
        {
            throw new Exception('Malformed json payload in message id: ' . $this->id);
        }

        $message = new Message();

        if(isset($payload['host']))
            $message->setHost($payload['host']);

        // Graylog requires a short message
        $message->setShortMessage(empty($payload['short_message']) ? 'No short message set.' : $payload['short_message']);

        if(isset($payload['full_message']))
            $message->setFullMessage($payload['full_message']);

        if(isset($payload['timestamp']))
            $message->setTimestamp($payload['timestamp']);

        if(isset($payload['level']))
            $message->setLevel($payload['level']);

        if(isset($payload['additionals']) && is_array($payload['additionals']))
        {
            foreach($payload['additionals'] as $additional_key => $additional_value)
            {
                $message->setAdditional($additional_key, $additional_value);
            }
        }

        return $message;
    }

    /**
     * Dispatches a job for current record
     */
    public function dispatchJob()
    {
        $this->status = 'queued';
        $this->save();

        dispatch(new GraylogJob($this->id));
    }
}