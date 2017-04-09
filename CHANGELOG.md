# Release Notes for 0.x

## [Unreleased]

### Added
* Support for Laravel 5.2, and 5.3

### Fixed
* Fix: Re-throw exception in command handler, don't explicitly `exit()`

## v0.2.1 (2017-04-07)

### Fixed
* Correct sanitation of job names which previously blocked creation of class based batch jobs [#22](https://github.com/lukewaite/laravel-queue-aws-batch/pull/22)
* Fix: Failing jobs throw exception when logging about failing (Array to String Conversion) [#d1a5023](https://github.com/lukewaite/laravel-queue-aws-batch/commit/6118f5bdf18935ce346d9628dcd1670f98d8e238)
* Fix: `BatchQueue` methods `->push` and `->update` now return the `id` of the entry [#d1a5023](https://github.com/lukewaite/laravel-queue-aws-batch/commit/6118f5bdf18935ce346d9628dcd1670f98d8e238)
* Fix: BatchQueue now properly releases failed jobs back into the queue [#d1a5023](https://github.com/lukewaite/laravel-queue-aws-batch/commit/6118f5bdf18935ce346d9628dcd1670f98d8e238)

## v0.2.0 (2017-04-07)

### Added
* Added exceptions on further unsupported operations. These two methods would both silently discard the `$delay` previously and set to 0.
  * `BatchQueue::release()` [#20](https://github.com/lukewaite/laravel-queue-aws-batch/pull/20)
  * `BatchJob::release()` [Diff](https://github.com/lukewaite/laravel-queue-aws-batch/pull/19/files#diff-fb4479932d3da5ac0014681d4beba72cR38)
* More complete test coverage ([#18](https://github.com/lukewaite/laravel-queue-aws-batch/pull/18), [#19](https://github.com/lukewaite/laravel-queue-aws-batch/pull/19))
