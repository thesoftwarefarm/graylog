<?php

namespace TsfCorp\Graylog;

use Exception;
use Throwable;
use TsfCorp\Graylog\Jobs\GraylogJob;
use TsfCorp\Graylog\Models\GraylogModel;

class GraylogMessage
{
    /**
     * @var string
     */
    private $project;
    /**
     * @var string
     */
    private $short_message;
    /**
     * @var string
     */
    private $full_message;
    /**
     * @var mixed
     */
    private $timestamp;
    /**
     * @var int
     */
    private $level;
    /**
     * @var array
     */
    private $additionals = [];
    /**
     * @var string
     */
    private $subsystem;
    /**
     * @var array
     */
    private $context;
    /**
     * @var \TsfCorp\Graylog\Models\GraylogModel
     */
    private $model;

    /**
     * PSR-3 log levels
     */
    const EMERGENCY = 0;
    const ALERT     = 1;
    const CRITICAL  = 2;
    const ERROR     = 3;
    const WARNING   = 4;
    const NOTICE    = 5;
    const INFO      = 6;
    const DEBUG     = 7;

    /**
     * GraylogMessage constructor.
     */
    public function __construct()
    {
        $this->project = config('graylog.project');
        $this->timestamp = microtime(true);
        $this->level = self::ALERT;
        $this->subsystem = 'GENERIC';
    }

    /**
     * @param $level
     * @return $this
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @param $message
     * @return $this
     */
    public function setShortMessage($message)
    {
        $this->short_message = $message;

        return $this;
    }

    /**
     * @param $message
     * @return $this
     */
    public function setFullMessage($message)
    {
        $this->full_message = $message;

        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function setAdditional($key, $value)
    {
        // ignore the key if it's empty
        if(!$key) return $this;

        $this->additionals[$key] = $value;

        return $this;
    }

    /**
     * Given a payload (array, object, etc), it encodes it to JSON
     * and sets it as a "_context" additional field.
     *
     * @param $context mixed
     *
     * @return $this
     */
    public function setContext($context)
    {
        $this->context = json_encode($context);

        return $this;
    }

    /**
     * Given a string, it sets it as a "_subsystem" additional field.
     *
     * @param string $subsystem
     *
     * @return $this
     */
    public function setSubsystem($subsystem = "")
    {
        $this->subsystem = $subsystem;

        return $this;
    }

    /**
     * @return array
     */
    private function toArray()
    {
        $message = [
            'project' => $this->project,
            'short_message' => $this->short_message,
            'full_message' => $this->full_message,
            'timestamp' => $this->timestamp,
            'level' => $this->level,
            'additionals' => $this->getAdditionals()
        ];

        // return only the non-empty values
        return array_filter($message, function($record){
            return is_bool($record) ||
                is_int($record) ||
                is_float($record) ||
                (is_array($record) && count($record)) ||
                (is_string($record) && strlen($record));
        });
    }

    /**
     * @return \TsfCorp\Graylog\Models\GraylogModel|null
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @return array
     */
    private function getAdditionals()
    {
        $additionals = [
            'subsystem' => $this->subsystem
        ];

        if($this->context)
        {
            $additionals['context'] = $this->context;
        }

        foreach($this->additionals as $key => $value)
        {
            $additionals[$key] = $value;
        }

        return $additionals;
    }

    /**
     * @param \Throwable $throwable
     * @return string
     */
    public static function fullMessageFromThrowable(Throwable $throwable)
    {
        return sprintf("%s: %s in %s:%s\nStack trace:\n%s",
            get_class($throwable),
            $throwable->getMessage(),
            $throwable->getFile(),
            $throwable->getLine(),
            $throwable->getTraceAsString());
    }

    /**
     * Stores the entire message into database
     *
     * @return $this
     */
    public function enqueue()
    {
        $this->model = new GraylogModel;
        $this->model->project = config('graylog.project');
        $this->model->payload = json_encode($this->toArray());
        $this->model->status = 'pending';
        $this->model->save();

        return $this;
    }

    /**
     * Dispatches a job which sends the message to Graylog server
     *
     * @return void
     * @throws \Exception
     */
    public function dispatch()
    {
        if ( ! $this->model)
        {
            throw new Exception('There is no message to be dispatched.');
        }

        $this->model->dispatchJob();
    }
}