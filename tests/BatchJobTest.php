<?php

namespace DNXLabs\LaravelQueueAwsBatch\Tests;

use DNXLabs\LaravelQueueAwsBatch\Exceptions\UnsupportedException;
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

        /** @var \DNXLabs\LaravelQueueAwsBatch\Jobs\BatchJob $batchJob */
        $this->batchJob = $this->getMockBuilder('DNXLabs\LaravelQueueAwsBatch\Jobs\BatchJob')->setMethods(null)->setConstructorArgs([
            m::mock('Illuminate\Container\Container'),
            $this->batchQueue = m::mock('DNXLabs\LaravelQueueAwsBatch\Queues\BatchQueue'),
            $this->job,
            'testConnection',
            'defaultQueue'
        ])->getMock();
    }

    public function testReleaseDoesntDeleteButDoesUpdate()
    {
        $this->expectNotToPerformAssertions();
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
