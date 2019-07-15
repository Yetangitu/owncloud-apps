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

\OC_JSON::callCheck();
\OC_JSON::checkLoggedIn();

$l = \OC::$server->getL10N('files_opds');

$opdsEnable = isset($_POST['opdsEnable']) ? $_POST['opdsEnable'] : 'false';
$rootPath = isset($_POST['rootPath']) ? $_POST['rootPath'] : '/Library';
$fileTypes = isset($_POST['fileTypes']) ? $_POST['fileTypes'] : '';
$skipList = isset($_POST['skipList']) ? $_POST['skipList'] : 'metadata.opf,cover.jpg';
$feedTitle = isset($_POST['feedTitle']) ? $_POST['feedTitle'] : $l->t("%s's Library", \OC_User::getDisplayName());

if (!strlen($rootPath) ||
    \OC\Files\Filesystem::isValidPath($rootPath) === false || 
    \OC\Files\Filesystem::file_exists($rootPath) === false ) {
	\OC_JSON::error(
		array(
			'data' => array('message'=> $l->t('Directory does not exist!'))
		)
	);
} else {
	Config::set('root_path', $rootPath);
        Config::set('enable', $opdsEnable);
        Config::set('file_types', $fileTypes);
        Config::set('skip_list', $skipList);
        Config::set('feed_title', $feedTitle);
	Config::set('id', Util::genUuid());

       	\OC_JSON::success(
	array(
		'data' => array('message'=> $l->t('Settings updated successfully.'))
		)
	);
}

exit();

