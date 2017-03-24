## UNRELEASED
### Added
 - Reader now supports PDF
 - PDF should work more or less like EPUB, ie. double page spreads are supported
 - optional double-buffering for faster rendering, can be disabled for low-memory devices
 - optional selectable text layer, can be disabled for low-memory devices

## 1.0.0 - 2017-03-15
### Added
 - Reader now supports CBR/CBZ ('comics') files
 - Book position ('cursor') is saved on server and restored on next invocation
 - Default settings (independent of fileid) and file-specific settings are saved and restored
 - Bookmarks and annotations (notes) are saved and restored (bookmarks are a type of annotation).
 - Full-text search implemented.
 - Framework to support more file format renderers
 - hooks added to remove defaults, settings and annotations/bookmarks for deleted files or users
 - epubreader
   * night mode now works more reliably
   * new 'day mode', ie. user-defined colours
   * new font settings: font weight
   * column width user-configurable
   * new mode: maximize reader area, for small-screen devices
   * page turn arrows optional, hidden by default
 - cbreader
   * supports CBR (rar) and CBZ (zip) archives
   * single and double page (spread) mode, auto-adjusts to screen geometry
   * optional image enhancement filters
   * seamless full screen mode (where browser allows user full control of experience, ie. not on apple)

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
