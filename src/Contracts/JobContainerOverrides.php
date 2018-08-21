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

namespace LukeWaite\LaravelQueueAwsBatch\Contracts;

/**
 * Should return an array representing the contents of the containerOverrides
 * property documented in the AWS Batch SubmitJob API reference.
 *
 * In the event of no overrides, should return null.
 *
 * https://docs.aws.amazon.com/batch/latest/APIReference/API_SubmitJob.html
 *
 * [
 *   "command": ["string"],
 *   "environment": [
 *     [
 *       "name": "string",
 *       "value": "string"
 *     ]
 *   ],
 *   "memory": number,
 *   "vcpus": number
 * ]
 *
 * Interface JobContainerOverrides
 * @package LukeWaite\LaravelQueueAwsBatch\Contracts
 */
interface JobContainerOverrides
{
    public function getBatchContainerOverrides();
}
