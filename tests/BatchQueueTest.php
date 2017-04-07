<?php

class BatchQueueTest extends \PHPUnit_Framework_TestCase
{
    public function testPushProperlyPushesJobOntoDatabase()
    {
        $queue = $this->getMockBuilder('LukeWaite\LaravelQueueAwsBatch\Queues\BatchQueue')->setMethods(['currentTime'])->setConstructorArgs([
            $database = Mockery::mock('Illuminate\Database\Connection'),
            'table',
            'default',
            '60',
            'jobdefinition',
            $batch = Mockery::mock('Aws\Batch\BatchClient')
        ])->getMock();

        $database->shouldReceive('table')->with('table')->andReturn($query = Mockery::mock('StdClass'));
        $queue->expects($this->any())->method('currentTime')->will($this->returnValue('time'));

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

        $batch->shouldReceive('submitJob')->once()->andReturnUsing(function ($array) {
            $this->assertEquals('jobdefinition', $array['jobDefinition']);
            $this->assertEquals('foo', $array['jobName']);
            $this->assertEquals('default', $array['jobQueue']);
            $this->assertEquals(['jobId'=>100], $array['parameters']);
        });

        $queue->push('foo', ['data']);
    }
}
