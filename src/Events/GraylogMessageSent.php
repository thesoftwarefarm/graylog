<?php

namespace TsfCorp\Graylog\Events;

use TsfCorp\Graylog\Models\GraylogModel;

class GraylogMessageSent
{
    /**
     * @var \TsfCorp\Graylog\Models\GraylogModel
     */
    private $message;

    /**
     * GraylogMessageFailedSending constructor.
     * @param \TsfCorp\Graylog\Models\GraylogModel $message
     */
    public function __construct(GraylogModel $message)
    {
        $this->message = $message;
    }

    /**
     * @return \TsfCorp\Graylog\Models\GraylogModel
     */
    public function getMessage()
    {
        return $this->message;
    }
}