<?php

namespace TsfCorp\Graylog\Commands;

use Illuminate\Console\Command;
use TsfCorp\Graylog\Models\GraylogModel;

class GraylogCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'graylog:dispatch-jobs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Long running process which fetches pending messages and dispatches jobs in order to be sent to Graylog';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $start_time = time();

        while (true)
        {
            if(time() - $start_time > 86400)
            {
                exit();
            }

            $messages = $this->fetchPendingMessages();

            if ( ! $messages->count())
            {
                usleep(333333);
            }

            /** @var GraylogModel $message */
            foreach($messages as $message)
            {
                $this->info("Dispatching job for graylog message id: {$message->id}");

                $message->dispatchJob();
            }
        }
    }

    /**
     * @return mixed
     */
    private function fetchPendingMessages()
    {
        return GraylogModel::where('status', 'pending')
            ->orderBy('id', 'asc')
            ->take(20)
            ->get();
    }
}
