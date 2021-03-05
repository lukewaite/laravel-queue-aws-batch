<?php
/**
 * Laravel Queue for AWS Batch.
 *
 * @author    Luke Waite <lwaite@gmail.com>
 * @copyright 2017 Luke Waite
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 *
 * @link      https://github.com/dnxlabs/laravel-queue-aws-batch
 */

namespace DNXLabs\LaravelQueueAwsBatch\Connectors;

use Aws\Batch\BatchClient;
use Illuminate\Queue\Connectors\DatabaseConnector;
use Illuminate\Support\Arr;
use DNXLabs\LaravelQueueAwsBatch\Queues\BatchQueue;

class BatchConnector extends DatabaseConnector
{
    /**
     * Establish a queue connection.
     *
     * @param array $config
     *
     * @return \Illuminate\Contracts\Queue\Queue
     */
    public function connect(array $config)
    {
        return new BatchQueue(
            $this->connections->connection(Arr::get($config, 'connection')),
            $config['table'],
            $config['queue'],
            Arr::get($config, 'expire', 60),
            $config['jobDefinition'],
            new BatchClient([
                'region'  => $config['region'],
                'version' => '2016-08-10',
            ])
        );
    }
}
