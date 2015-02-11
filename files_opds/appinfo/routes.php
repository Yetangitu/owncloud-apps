<?php

/**
 * ownCloud - Files_Opds App
 *
 * @author Frank de Lange
 * @copyright 2015 Frank de Lange
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 */


$this->create('opds_catalog', '/')
        ->actionInclude('files_opds/index.php');
$this->create('opds_catalog_admin_settings', 'ajax/admin.php')
        ->actionInclude('files_opds/ajax/admin.php');
$this->create('opds_catalog_personal_settings', 'ajax/personal.php')
        ->actionInclude('files_opds/ajax/personal.php');
$this->create('opds_catalog_clear_bookshelf', 'ajax/clear_bookshelf.php')
        ->actionInclude('files_opds/ajax/clear_bookshelf.php');
$this->create('opds_catalog_schedule_rescan', 'ajax/schedule_rescan.php')
        ->actionInclude('files_opds/ajax/schedule_rescan.php');
