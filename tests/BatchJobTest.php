<?php

use Mockery as m;
use PHPUnit\Framework\TestCase;

class BatchJobTest extends TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testReleaseDoesntDeleteButDoesUpdate()
    {
        $job = new \stdClass();
        $job->payload = '{"job":"foo","data":["data"]}';
        $job->id = 4;
        $job->queue = 'default';
        $job->attempts = 1;

        /** @var \LukeWaite\LaravelQueueAwsBatch\Jobs\BatchJob $batchJob */
        $batchJob = $this->getMockBuilder('LukeWaite\LaravelQueueAwsBatch\Jobs\BatchJob')->setMethods(null)->setConstructorArgs([
            m::mock('Illuminate\Container\Container'),
            $batchQueue = m::mock('LukeWaite\LaravelQueueAwsBatch\Queues\BatchQueue'),
            $job,
            'default'
        ])->getMock();

        $batchQueue->shouldReceive('release')->once();
        $batchQueue->shouldNotReceive('deleteReserved');

        $batchJob->release(0);
    }

    /**
     * @expectedException \LukeWaite\LaravelQueueAwsBatch\Exceptions\UnsupportedException
     */
    public function testThrowsExceptionOnReleaseWIthDelay()
    {
        $job = new \stdClass();
        $job->payload = '{"job":"foo","data":["data"]}';
        $job->id = 4;
        $job->queue = 'default';
        $job->attempts = 1;

        /** @var \LukeWaite\LaravelQueueAwsBatch\Jobs\BatchJob $batchJob */
        $batchJob = $this->getMockBuilder('LukeWaite\LaravelQueueAwsBatch\Jobs\BatchJob')->setMethods(null)->setConstructorArgs([
            m::mock('Illuminate\Container\Container'),
            $batchQueue = m::mock('LukeWaite\LaravelQueueAwsBatch\Queues\BatchQueue'),
            $job,
            'default'
        ])->getMock();

        $batchQueue->shouldNotReceive('release');
        $batchQueue->shouldNotReceive('deleteReserved');

        $batchJob->release(10);
    }
}
