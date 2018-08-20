# Release Notes for 1.x

## [v1.1.0] (2018-08-20)

### Added
* Added support for containerOverrides ([#11c0184](https://github.com/lukewaite/laravel-queue-aws-batch/pull/30))

## [v1.0.2] (2018-07-20)

### Fixed
Fix: Jobs sometimes run until max tries hit without hitting laravel's queue failure method ([#3a25d14e](https://github.com/lukewaite/laravel-queue-aws-batch/commit/3a25d14e7cb3fb6c8d769d92ce0b93d08961ed3d))

## [v1.0.1] (2018-07-15)

### Fixed
* Correctly set the queue on construction of the BatchJob - fixes an issues where
failing jobs were written into the failed jobs table with the connection name as
the queue name. ([#2aa745b](https://github.com/lukewaite/laravel-queue-aws-batch/pull/29))

## [v1.0.0] (2017-04-09)

### Added
* Support for Laravel 5.2, and 5.3 ([#25](https://github.com/lukewaite/laravel-queue-aws-batch/pull/25))

### Fixed
* Fix: Re-throw exception in command handler, don't explicitly `exit()` ([#5cc05a8](https://github.com/lukewaite/laravel-queue-aws-batch/commit/5cc05a88c497ade72b81916a16384bdb69107bd5))

## [v0.2.1] (2017-04-07)

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

[Unreleased]: https://github.com/lukewaite/laravel-queue-aws-batch/compare/v1.1.0...HEAD
[v1.1.0]: https://github.com/lukewaite/laravel-queue-aws-batch/compare/v1.0.2...v1.1.0
[v1.0.2]: https://github.com/lukewaite/laravel-queue-aws-batch/compare/v1.0.1...v1.0.2
[v1.0.1]: https://github.com/lukewaite/laravel-queue-aws-batch/compare/v1.0.0...v1.0.1
[v1.0.0]: https://github.com/lukewaite/laravel-queue-aws-batch/compare/v0.2.1...v1.0.0
[v0.2.1]: https://github.com/lukewaite/laravel-queue-aws-batch/compare/v0.2.0...v0.2.1