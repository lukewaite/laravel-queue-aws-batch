<?php
/**
 * Laravel Queue for AWS Batch
 *
 * @author    Luke Waite <lwaite@gmail.com>
 * @copyright 2017 Luke Waite
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/lukewaite/laravel-queue-aws-batch
 */


namespace LukeWaite\LaravelQueueAwsBatch\Queues;


use Aws\Batch\BatchClient;
use Illuminate\Database\Connection;
use Illuminate\Queue\DatabaseQueue;
use LukeWaite\LaravelQueueAwsBatch\Exceptions\UnsupportedException;
use LukeWaite\LaravelQueueAwsBatch\Jobs\BatchJob;

class BatchQueue extends DatabaseQueue
{

    /**
     * The AWS Batch client
     *
     * @var $batch BatchClient
     */
    protected $batch;

    protected $jobDefinition;

    public function __construct(
        Connection $database,
        $table,
        $default = 'default',
        $expire = 60,
        $jobDefinition,
        BatchClient $batch
    ) {
        $this->jobDefinition = $jobDefinition;
        $this->batch = $batch;
        parent::__construct($database, $table, $default, $expire);
    }


    public function push($job, $data = '', $queue = null)
    {
        return $this->pushToBatch($queue, $this->createPayload($job, $data),
            str_replace('\\', '_', (string)get_class($job)));
    }

    public function pushRaw($payload, $queue = null, array $options = [])
    {
        return $this->pushToBatch($queue, $payload, 'raw-job');
    }

    /**
     * Push a raw payload to the database, then to AWS Batch, with a given delay.
     *
     * @param  string|null $queue
     * @param  string $payload
     * @param  string $jobName
     * @param  int $attempts
     * @return mixed
     */
    public function pushToBatch($queue, $payload, $jobName, $attempts = 0)
    {
        $jobId = $this->pushToDatabase(0, $queue, $payload, $attempts);

        return $this->batch->submitJob([
            'jobDefinition' => $this->jobDefinition,
            'jobName' => $jobName,
            'jobQueue' => $this->getQueue($queue),
            'parameters' => [
                'jobId' => $jobId
            ]
        ]);
    }

    public function getJobById($id, $queue)
    {
        $job = $this->database->table($this->table)->where('id', $id)->first();

        return new BatchJob(
            $this->container, $this, $job, $queue
        );
    }


    public function pop($queue = null)
    {
        throw new UnsupportedException('The BatchQueue does not support running via a regular worker. Instead, you should use the queue:batch-work command with a job id.');
    }

    public function bulk($jobs, $data = '', $queue = null)
    {
        // This could be implemented, but it's not in first pass.
        throw new UnsupportedException('The BatchQueue does not currently support the bulk() operation.');
    }

    public function later($delay, $job, $data = '', $queue = null)
    {
        throw new UnsupportedException('The BatchQueue does not support the later() operation.');
    }

}