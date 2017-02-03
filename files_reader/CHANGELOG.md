## Unreleased
### Added
 - Reader now supports CBR/CBZ ('comics') files
 - Book position is saved on server and restored on next invocation
 - Framework to support more file format renderers

## 0.8.3 - 2017-02-02
### Fixed
 . #31: ReferenceError: cleanStartTextContent is not defined, caused by failure to declare local var in epub.js

## 0.8.3 - 2017-02-01
### Fixed
 - missing $title parameter in template/reader.php caused warnings in log, fixed

## 0.8.2 - 2017-01-10
### Fixed
 - Nextcloud-port broke compatibility with Owncloud due to OC not supporting CSPv3, workaround implemented

## 0.8.1 - 2017-01-09
### Added
 - Modified info.xml, added screenshots

## 0.8.0 - 2017-01-09
### Added
 - new version 0.2.15 of Futurepress epub.js renderer

### Changed
 - New logo
 - First release to be compatible with Nextcloud
