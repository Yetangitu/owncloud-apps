<?php
/**
 * @author Frank de Lange
 * @copyright 2015 Frank de Lange
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OCA\Files_Reader\Db;

use OCP\AppFramework\Db\Entity;

class Bookmark extends Entity {

    protected $userId;  // user
    protected $fileId;  // book (identified by fileId) for which this mark is valid
    protected $name;    // name, defaults to $location
    protected $value;   // bookmark value (format-specific, eg. page number for PDF, CFI for epub, etc)
    protected $lastModified;    // modification timestamp
}

