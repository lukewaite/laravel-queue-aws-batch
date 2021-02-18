<?php

namespace LukeWaite\LaravelQueueAwsBatch\Tests;

use LukeWaite\LaravelQueueAwsBatch\Exceptions\UnsupportedException;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class BatchJobTest extends TestCase
{
    public function tearDown(): void
    {
        m::close();
    }

    public function setUp(): void
    {
        $this->job = new \stdClass();
        $this->job->payload = '{"job":"foo","data":["data"]}';
        $this->job->id = 4;
        $this->job->queue = 'default';
        $this->job->attempts = 1;

        /** @var \LukeWaite\LaravelQueueAwsBatch\Jobs\BatchJob $batchJob */
        $this->batchJob = $this->getMockBuilder('LukeWaite\LaravelQueueAwsBatch\Jobs\BatchJob')->setMethods(null)->setConstructorArgs([
            m::mock('Illuminate\Container\Container'),
            $this->batchQueue = m::mock('LukeWaite\LaravelQueueAwsBatch\Queues\BatchQueue'),
            $this->job,
            'testConnection',
            'defaultQueue'
        ])->getMock();
    }

    public function testReleaseDoesntDeleteButDoesUpdate()
    {
        $this->batchQueue->shouldReceive('release')->once();
        $this->batchQueue->shouldNotReceive('deleteReserved');

        $this->batchJob->release(0);
    }

    public function testThrowsExceptionOnReleaseWIthDelay()
    {
        $this->expectException(UnsupportedException::class);

        $this->batchQueue->shouldNotReceive('release');
        $this->batchQueue->shouldNotReceive('deleteReserved');

        $this->batchJob->release(10);
    }
}
