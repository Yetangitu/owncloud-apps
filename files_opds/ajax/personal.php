<?php

/**
 * ownCloud - Files_Opds App
 *
 * @author Frank de Lange
 * @copyright 2014 Frank de Lange
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 */

namespace OCA\Files_Opds;

\OCP\JSON::callCheck();
\OCP\JSON::checkLoggedIn();

$l = new \OC_L10N('files_opds');

$opdsEnable = isset($_POST['opdsEnable']) ? $_POST['opdsEnable'] : 'false';
$rootPath = isset($_POST['rootPath']) ? $_POST['rootPath'] : null;
$fileTypes = isset($_POST['fileTypes']) ? $_POST['fileTypes'] : '';

if (!is_null($rootPath)){
        if (\OC\Files\Filesystem::file_exists($rootPath) === false ){
		\OCP\JSON::error(
			array(
				'data' => array('message'=> $l->t('Directory does not exist!'))
			)
		);
        } else {
        	Config::set('root_path', $rootPath);
        	\OCP\JSON::success(
                array(
                        'data' => array('message'=> $l->t('Settings updated successfully.'))
                        )
        	);
	}
        Config::set('enable', $opdsEnable);
        Config::set('file_types', $fileTypes);
	Config::set('id', Util::genUuid());
        exit();
}

exit();

