## Laravel Queue for AWS Batch
This package is currently built out for Laravel 5.1 as most of my work relies LTS builds. Support will be coming for 5.4.

### Usage
1. Your Laravel application will need to be dockerized and pushed into a container registry of your choice. The `ENTRYPOINT`
   should be set to `artisan`. 

2. Add a new queue to your `config/queues.php` config file's `connections` array:
```json
    [
        'batch' => [
            'driver' => 'batch',
            'table' => 'jobs',
            'queue' => 'first-run-job-queue',
            'jobDefinition' => 'my-job-definition',
            'expire' => 60,
            'region' => 'us-east-1'
        ]
    ]
```
This queue transport depends on being able to write it's queue jobs to a database queue. In this example, it writes it's
jobs to the `jobs` table. You'll need to use the `artisan queue:table` to create a migration to create this table.

3. Create an AWS Batch job queue with the same name as the `queue` config setting. This is where the Batch connector
will push your jobs into Batch. In this case, my queue name would be `first-run-job-queue`.

4. Create a AWS Batch job definition for each queue you define that looks something like this:
```json
{
    "jobDefinitionName": "my-laravel-application",
    "type": "container",
    "parameters": {},
    "retryStrategy": {
        "attempts": 10
    },
    "containerProperties": {
        "image": "<your docker image>",
        "vcpus": 1,
        "memory": 256,
        "command": [
            "queue:work-batch",
            "first-run-job-queue",
            "Ref::jobId",
            "--tries=3"
        ],
        "volumes": [],
        "environment": [],
        "mountPoints": [],
        "ulimits": []
    }
}
```
Here, you configure your container to start, run the `queue:work-batch` command (assuming `artisan` is your entrypoint)
and pass in the name of the queue, `first-run-job-queue` as well as the `Ref::jobId`` param, which is passed in when
the Batch connector creates the job.

It is important that you configure a retryStrategy with more "attempts" than you are running `tries` if you provide that
argument. Otherwise, Batch will not retry your job if it fails. Laravel 5.1 does not write to the failed job queue until
the _next_ run after tries has been exceeded by jobs failing. Newer versions will write to the queue in the same run, so
this requirement can be relaxed later.

6. Add the Service Provider to your application:
    * In `config/app.php` add to the `providers` array: `LukeWaite\LaravelQueueAwsBatch\BatchQueueServiceProvider::class`
    
    
### Known Issues
* `$delay` is not supported, for initial job sends or for retries. This is a limitation on the Batch side, and as it's
our runner, and the DB queue is just backing it to ship the jobs more easily, we don't have an easy work around. If you
require delayed jobs for your use case, at this point my recommendation would be to use a regular DB queue, and to fire
a job into it which will fire your batch job at the correct time.
