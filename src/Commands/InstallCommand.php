<?php

namespace TsfCorp\Graylog\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'graylog:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install graylog resources';

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
        if ( ! $this->confirm('This will install graylog config file and optionally migration file. Do you wish to continue?'))
        {
            $this->comment('Aborted.');
            return;
        }

        $this->comment('Publishing graylog config file...');
        $this->callSilent('vendor:publish', ['--tag' => 'graylog-config']);

        if ($this->confirm('Do you wish to publish migration file?'))
        {
            $this->comment('Publishing graylog migration file...');
            $this->callSilent('vendor:publish', ['--tag' => 'graylog-migrations']);
        }

        $this->info('Graylog was installed successfully.');
    }
}
