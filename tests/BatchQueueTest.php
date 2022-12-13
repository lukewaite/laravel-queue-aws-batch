<?php

namespace LukeWaite\LaravelQueueAwsBatch\Tests;

use Carbon\Carbon;
use LukeWaite\LaravelQueueAwsBatch\Exceptions\UnsupportedException;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

class BatchQueueTest extends TestCase
{
    public function setUp() : void
    {
        $this->queue = $this->getMockBuilder('LukeWaite\LaravelQueueAwsBatch\Queues\BatchQueue')->setMethods(null)->setConstructorArgs([
            $this->database = m::mock('Illuminate\Database\Connection'),
            'table',
            'default',
            '60',
            'jobdefinition',
            $this->batch = m::mock('Aws\Batch\BatchClient')
        ])->getMock();

        $this->queue->setContainer(m::mock('Illuminate\Container\Container'));
    }

    public function testPushProperlyPushesJobOntoDatabase()
    {
        $this->database->shouldReceive('table')->with('table')->andReturn($query = m::mock('StdClass'));

        $query->shouldReceive('insertGetId')->once()->andReturnUsing(function ($array) {
            $this->assertEquals('default', $array['queue']);
            $this->assertNotNull($array['payload'], 'Payload is not set');
            $this->assertEquals(TestJob::class, json_decode($array['payload'], true)['displayName']);
            $this->assertEquals(0, $array['attempts']);
            $this->assertNull($array['reserved_at']);
            $this->assertIsInt($array['available_at']);

            return 100;
        });

        $this->batch->shouldReceive('submitJob')->once()->andReturnUsing(function ($array) {
            $this->assertEquals('jobdefinition', $array['jobDefinition']);
            $this->assertEquals('LukeWaite_LaravelQueueAwsBatch_Tests_TestJob', $array['jobName']);
            $this->assertEquals('default', $array['jobQueue']);
            $this->assertEquals(['jobId' => 100], $array['parameters']);
        });

        $result = $this->queue->push(new TestJob());
        $this->assertEquals(100, $result);
    }

    public function testPushProperlySanitizesJobName()
    {
        $this->database->shouldReceive('table')->with('table')->andReturn($query = m::mock('StdClass'));

        $query->shouldReceive('insertGetId')->once()->andReturnUsing(function ($array) {
            return 1;
        });

        $this->batch->shouldReceive('submitJob')->once()->andReturnUsing(function ($array) {
            $this->assertMatchesRegularExpression('/^[a-zA-Z0-9_]+$/', $array['jobName']);
            $this->assertEquals('LukeWaite_LaravelQueueAwsBatch_Tests_TestJob', $array['jobName']);
        });

        $this->queue->push(new TestJob());
    }

    public function testGetJobById()
    {
        $testDate = Carbon::create(2016, 9, 4, 16);
        Carbon::setTestNow($testDate);

        $this->database->shouldReceive('beginTransaction')->once();
        $this->database->shouldReceive('table')->with('table')->andReturn($table = m::mock('StdClass'));
        $table->shouldReceive('lockForUpdate')->once()->andReturn($query = m::mock('StdClass'));
        $query->shouldReceive('where')->once()->with('id', 1)->andReturn($results = m::mock('StdClass'));
        $results->shouldReceive('first')->once()->andReturn($queryResult = m::mock('StdClass'));
        $queryResult->attempts = 0;
        $queryResult->queue = 'default';
        $queryResult->id = 1;

        $table->shouldReceive('where')->once()->with('id', 1)->andReturn($reserved = m::mock('StdClass'));
        $reserved->shouldReceive('update')->with(['reserved_at'=> 1473004800, 'attempts'=> 1])->once()->andReturn($job = m::mock('StdClass'));

        $this->database->shouldReceive('commit')->once();

        $this->queue->getJobById(1, 'default');

        Carbon::setTestNow();
    }

    public function testRelease()
    {
        $this->database->shouldReceive('table')->once()->with('table')->andReturn($table = m::mock('StdClass'));
        $table->shouldReceive('where')->once()->with('id', 4)->andReturn($query = m::mock('StdClass'));
        $query->shouldReceive('update')->once()->with([
            'attempts'    => 1,
            'reserved_at' => null,
        ])->andReturn(4);

        $job = new \stdClass();
        $job->payload = '{"job":"foo","data":["data"]}';
        $job->id = 4;
        $job->queue = 'default';
        $job->attempts = 1;

        $result = $this->queue->release('default', $job, 0);
        $this->assertEquals(4, $result);
    }

    public function testPopThrowsException()
    {
        $this->expectException(UnsupportedException::class);
        $this->expectExceptionMessage('The BatchQueue does not support running via a regular worker. Instead, you should use the queue:batch-work command with a job id.');

        $this->queue->pop('default');
    }

    public function testLaterThrowsException()
    {
        $this->expectException(UnsupportedException::class);
        $this->expectExceptionMessage('The BatchQueue does not support the later() operation.');

        $this->queue->later(10, 'default');
    }

    public function testReleaseWithDelayThrowsException()
    {
        $this->expectException(UnsupportedException::class);
        $this->expectExceptionMessage('The BatchJob does not support releasing back onto the queue with a delay');

        $job = new \stdClass();
        $job->payload = '{"job":"foo","data":["data"]}';
        $job->id = 4;
        $job->queue = 'default';
        $job->attempts = 1;

        $this->queue->release('default', $job, 10);
    }
}

class TestJob
{
    //
}
