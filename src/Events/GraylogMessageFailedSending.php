<?php

namespace TsfCorp\Graylog\Events;

use Throwable;
use TsfCorp\Graylog\Models\GraylogModel;

class GraylogMessageFailedSending
{
    /**
     * @var \TsfCorp\Graylog\Models\GraylogModel
     */
    private $message;
    /**
     * @var \Throwable|null
     */
    private $exception;

    /**
     * GraylogMessageFailedSending constructor.
     * @param \TsfCorp\Graylog\Models\GraylogModel $message
     * @param \Throwable|null $exception
     */
    public function __construct(GraylogModel $message, Throwable $exception = null)
    {
        $this->message = $message;
        $this->exception = $exception;
    }

    /**
     * @return \TsfCorp\Graylog\Models\GraylogModel
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return \Throwable|null
     */
    public function getException()
    {
        return $this->exception;
    }
}