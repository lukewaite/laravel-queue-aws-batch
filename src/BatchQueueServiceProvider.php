<?php

/**
 * Laravel Queue for AWS Batch.
 *
 * @author    Luke Waite <lwaite@gmail.com>
 * @copyright 2017 Luke Waite
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 *
 * @link      https://github.com/lukewaite/laravel-queue-aws-batch
 */

namespace LukeWaite\LaravelQueueAwsBatch;

use Illuminate\Queue\Queue;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\ServiceProvider;
use LukeWaite\LaravelQueueAwsBatch\Connectors\BatchConnector;
use LukeWaite\LaravelQueueAwsBatch\Console\QueueWorkBatchCommand;

class BatchQueueServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->commands(QueueWorkBatchCommand::class);
    }

    public function boot(QueueManager $queue)
    {
        $this->registerBatchConnector($queue);
    }

    /**
     * Register the Batch queue connector.
     *
     * @param QueueManager $manager
     *
     * @return void
     */
    protected function registerBatchConnector(QueueManager $manager)
    {
        $manager->addConnector('batch', function () {
            return new BatchConnector($this->app['db']);
        });
    }
}
