# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [2.1.2] - June 11th, 2016
### Changed
- Updated development dependencies
- Updated documentation
- Switched from [`semistandard`](http://npm.im/semistandard) to [`xo`](http://npm.im/xo) for coding standards
- Tested in [jQuery 3.0](https://blog.jquery.com/2016/06/09/jquery-3-0-final-released/)

## [2.1.1] - August 31st, 2015
### Fixed
- Corrected version information in the source

## [2.1.0] - August 31st, 2015
### Changed
- Switched to [Keep a CHANGELOG](http://keepachangelog.com) in CHANGELOG.md
- Moved to [JavaScript Semi-Standard Coding Style](http://npm.im/semistandard)
- Updated development dependencies

## [2.0.2] - June 5th, 2015
### Added
- Added code coverage
- Added [jsDelivr CDN](http://www.jsdelivr.com/#!jquery-once) automated support
- Added [cdnjs](https://github.com/cdnjs/cdnjs) automated support

## [2.0.1] - May 5th, 2015
### Changed
- Updated development dependencies
- Updated documentation

## [2.0.0] - January 20th, 2015
### Fixed
- Fixed type checking of the `id` parameter of `.once()` as optional
  - From [@theodoreb](http://github.com/theodoreb)
- Fixed inline code documentation
  - From [@yched](http://github.com/yched)

### Added
- Added performance improvement through [`.data()`](http://api.jquery.com/data/)
use rather than class attributes
- Added `findOnce()` function to allow filtering once'd elements
- Added automated testing through [Mocha](http://mochajs.org)
- Added [jsdoc-to-markdown](https://github.com/75lb/jsdoc-to-markdown) to
automatically build API documentation

### Changed
- Switched to [ESLint](http://eslint.org) for code linting
- Removed unneeded cache variable
- Removed function callback in order to promote jQuery chaining standards

## [1.2.6] - August 31, 2013
### Changed
- Fixed Bower
- Updated documentation

## [1.2.4] - June 13, 2013
### Added
- Added jquery.once.min.js to the file meta data
- Added removeOnce() test

### Changed
- Don't limit jQuery.once usage to jQuery 1.8.
- Updated documentation

## [1.2.3] - June 13, 2013
### Added
- Added tests
- Fixed documentation

## [1.2.1] - May 18, 2013
### Added
- Added UMD support
- Added Bower support

## [1.2.0] - April 5, 2013
### Added
- Added jQuery Once

[unreleased]: https://github.com/RobLoach/jquery-once/compare/2.1.2...HEAD
[2.1.2]: https://github.com/RobLoach/jquery-once/compare/2.1.1...2.1.2
[2.1.1]: https://github.com/RobLoach/jquery-once/compare/2.1.0...2.1.1
[2.1.0]: https://github.com/RobLoach/jquery-once/compare/2.0.2...2.1.0
[2.0.2]: https://github.com/RobLoach/jquery-once/compare/2.0.1...2.0.2
[2.0.1]: https://github.com/RobLoach/jquery-once/compare/2.0.0...2.0.1
[2.0.0]: https://github.com/RobLoach/jquery-once/compare/1.2.6...2.0.0
[1.2.6]: https://github.com/RobLoach/jquery-once/compare/1.2.4...1.2.6
[1.2.4]: https://github.com/RobLoach/jquery-once/compare/1.2.3...1.2.4
[1.2.3]: https://github.com/RobLoach/jquery-once/compare/1.2.1...1.2.3
[1.2.1]: https://github.com/RobLoach/jquery-once/compare/1.2.0...1.2.1
[1.2.0]: https://github.com/RobLoach/jquery-once/compare/7db530a0bd48f249c5f0df4fab02e93444623889...1.2.0
