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

namespace LukeWaite\LaravelQueueAwsBatch\Jobs;

use Illuminate\Queue\Jobs\DatabaseJob;
use LukeWaite\LaravelQueueAwsBatch\Exceptions\UnsupportedException;

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
     * @param int $delay
     *
     * @return void
     * @throws UnsupportedException
     */
    public function release($delay = 0)
    {
        if ($delay != 0) {
            throw new UnsupportedException('The BatchJob does not support releasing back onto the queue with a delay');
        }

        $this->released = true;

        $this->database->release($this->queue, $this->job, 0);
    }

    /**
     * We override the base database attempts method to try to read out the
     * `AWS_BATCH_JOB_ATTEMPT` environment variable. If that fails, we fallback
     * to the db attempts count.
     *
     * https://docs.aws.amazon.com/batch/latest/userguide/job_env_vars.html
     *
     * @return int
     */
    public function attempts()
    {
        return env('AWS_BATCH_JOB_ATTEMPT', parent::attempts());
    }
}
