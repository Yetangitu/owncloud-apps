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

class Preference extends Entity {

    protected $userId;  // user for whom this preference is valid
    protected $scope;   // scope (default or specific renderer)
    protected $fileId;  // file for which this preference is set
    protected $name;    // preference name
    protected $value;   // preference value
    protected $lastModified;    // modification timestamp
}

