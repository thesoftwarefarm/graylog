<?php

namespace TsfCorp\Graylog\Tests;

use function Composer\Autoload\includeFile;
use CreateGraylogTable;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    public function setUp()
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    protected function setUpDatabase()
    {
        include_once __DIR__.'/../database/migrations/2018_12_01_000000_create_graylog_table.php';

        (new CreateGraylogTable())->up();
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.env', 'production');
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('graylog.host', 'my_project');
        $app['config']->set('graylog.database_connection', 'sqlite');
        $app['config']->set('graylog.max_retries', 5);
    }
}