<?php
/**
 * Laravel Queue for AWS Batch
 *
 * @author    Luke Waite <lwaite@gmail.com>
 * @copyright 2017 Luke Waite
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/lukewaite/laravel-queue-aws-batch
 */


namespace LukeWaite\LaravelQueueAwsBatch\Jobs;

use Illuminate\Queue\Jobs\DatabaseJob;

class BatchJob extends DatabaseJob
{

    /**
     * The database queue instance.
     *
     * @var \LukeWaite\LaravelQueueAwsBatch\Queues\BatchQueue
     */
    protected $database;

    /**
     * Release the job back into the queue.
     *
     * Here we need to retain the same jobId, so Batch can retry it, so we need to override the parent.
     *
     * @param  int  $delay
     * @return void
     */
    public function release($delay = 0)
    {
        $this->released = true;

        $this->database->release($this->queue, $this->job, $delay);
    }

}