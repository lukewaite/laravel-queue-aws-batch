<?php

use Mockery as m;
use PHPUnit\Framework\TestCase;

class BatchQueueTest extends TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function setUp()
    {
        /** @var \LukeWaite\LaravelQueueAwsBatch\Queues\BatchQueue $queue */
        $this->queue = $this->getMockBuilder('LukeWaite\LaravelQueueAwsBatch\Queues\BatchQueue')->setMethods(null)->setConstructorArgs([
            $this->database = m::mock('Illuminate\Database\Connection'),
            'table',
            'default',
            '60',
            'jobdefinition',
            $this->batch = m::mock('Aws\Batch\BatchClient')
        ])->getMock();
    }

    public function testPushProperlyPushesJobOntoDatabase()
    {
        $this->database->shouldReceive('table')->with('table')->andReturn($query = m::mock('StdClass'));

        $query->shouldReceive('insertGetId')->once()->andReturnUsing(function ($array) {
            $this->assertEquals('default', $array['queue']);
            $this->assertNotNull($array['payload'], 'Payload is not set');
            $this->assertEquals(['data'], json_decode($array['payload'], 1)['data']);
            $this->assertEquals('foo', json_decode($array['payload'], 1)['job']);
            $this->assertEquals(0, $array['attempts']);
            $this->assertNull($array['reserved_at']);
            $this->assertInternalType('int', $array['available_at']);

            return 100;
        });

        $this->batch->shouldReceive('submitJob')->once()->andReturnUsing(function ($array) {
            $this->assertEquals('jobdefinition', $array['jobDefinition']);
            $this->assertEquals('foo', $array['jobName']);
            $this->assertEquals('default', $array['jobQueue']);
            $this->assertEquals(['jobId' => 100], $array['parameters']);
        });

        $this->queue->push('foo', ['data']);
    }

    public function testGetJobById()
    {
        $this->database->shouldReceive('table')->once()->with('table')->andReturn($query = m::mock('StdClass'));
        $query->shouldReceive('where')->once()->with('id', 1)->andReturn($results = m::mock('StdClass'));
        $results->shouldReceive('first')->once()->andReturn($queryResult = m::mock('StdClass'));
        $queryResult->attempts = 0;

        $this->queue->setContainer(m::mock('Illuminate\Container\Container'));

        $this->queue->getJobById(1, 'default');
    }

    public function testRelease()
    {
        $this->database->shouldReceive('table')->once()->with('table')->andReturn($query = m::mock('StdClass'));
        $query->shouldReceive('update')->once()->with([
            'id'          => 4,
            'attempts'    => 1,
            'reserved'    => 0,
            'reserved_at' => null,
        ]);

        $this->queue->setContainer(m::mock('Illuminate\Container\Container'));

        $job = new \stdClass();
        $job->payload = '{"job":"foo","data":["data"]}';
        $job->id = 4;
        $job->queue = 'default';
        $job->attempts = 1;

        $this->queue->release('default', $job, 0);
    }

    /**
     * @expectedException LukeWaite\LaravelQueueAwsBatch\Exceptions\UnsupportedException
     */
    public function testPopThrowsException()
    {
        $this->queue->pop('default');
    }

    /**
     * @expectedException LukeWaite\LaravelQueueAwsBatch\Exceptions\UnsupportedException
     */
    public function testLaterThrowsException()
    {
        $this->queue->later(10, 'default');
    }
}
