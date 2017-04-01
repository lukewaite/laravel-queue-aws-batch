<?php
/**
 * Laravel Queue for AWS Batch
 *
 * @author    Luke Waite <lwaite@gmail.com>
 * @copyright 2017 Luke Waite
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/lukewaite/laravel-queue-aws-batch
 */

namespace LukeWaite\LaravelQueueAwsBatch\Console;

use Illuminate\Console\Command;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Queue\QueueManager;
use Illuminate\Queue\Worker;
use LukeWaite\LaravelQueueAwsBatch\Exceptions\JobNotFoundException;
use LukeWaite\LaravelQueueAwsBatch\Exceptions\UnsupportedException;
use LukeWaite\LaravelQueueAwsBatch\Queues\BatchQueue;
use Symfony\Component\Debug\Exception\FatalThrowableError;

class QueueWorkBatchCommand extends Command
{
    protected $name = 'queue:work-batch';

    protected $description = 'Run a Job for the AWS Batch queue';

    protected $signature = 'queue:work-batch {connection} {job_id} {--tries=}';

    protected $manager;
    protected $worker;
    protected $exceptions;

    public function __construct(QueueManager $manager, Worker $worker, Handler $exceptions)
    {
        $this->worker = $worker;
        $this->manager = $manager;
        $this->exceptions = $exceptions;
        parent::__construct();
    }

    public function fire()
    {

        try {
            $this->runJob();
        } catch (\Exception $e) {
            if ($this->exceptions) {
                $this->exceptions->report($e);
            }
            exit(1);
        } catch (\Throwable $e) {
            if ($this->exceptions) {
                $this->exceptions->report(new FatalThrowableError($e));
            }
            exit(1);
        }
    }

    protected function runJob()
    {
        $maxTries = $this->option('tries');
        $delay = 0;

        $connectionName = $this->argument('connection');
        $jobId = $this->argument('job_id');

        /** @var BatchQueue $connection */
        $connection = $this->manager->connection($connectionName);


        if (!$connection instanceof BatchQueue) {
            throw new UnsupportedException('queue:work-batch can only be run on batch queues');
        }

        $job = $connection->getJobById($jobId, $connectionName);

        // If we're able to pull a job off of the stack, we will process it and
        // then immediately return back out.
        if (!is_null($job)) {
            return $this->worker->process(
                $this->manager->getName($connectionName), $job, $maxTries, $delay
            );
        }

        // If we hit this point, we haven't processed our job
        throw new JobNotFoundException("No job was returned");
    }
}