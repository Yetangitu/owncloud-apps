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

\OCP\App::checkAppEnabled('files_opds');

Util::authenticateUser();

/* Refuse access if user disabled opds support */
if (Config::get('enable', 'false') === 'false') {
	Util::changeHttpStatus(403);
	exit();
}

/* id defaults to 'root' (meaning 'serve root feed') */
$id = isset($_GET['id']) ? $_GET['id'] : 'root';

/* if either pid or tid is set, serve preview image for id */
if (isset($_GET['pid'])) {
	$id = (int) $_GET['pid'];
	$type = 'cover';
}

if (isset($_GET['tid'])) {
	$id = (int) $_GET['tid'];
	$type = 'thumbnail';
}

# this is a bit ugly: $id either contains a numerical ID or a command string. If it contains the latter,
# move the content to $type for the Feed to interpret and switch in the library root numerical ID
if (ctype_alpha($id)) {
	$type = $id;
	$rootFolder = \OC::$server->getUserFolder(\OC::$server->getUserSession()->getUser()->getUID());
	$id = $rootFolder->getId();
}

$dir = \OC\Files\Filesystem::normalizePath(\OC\Files\Filesystem::getPath($id));
$root = Config::get('root_path', '/Library');

/* Only feed files descending from designated root directory */
if (!(Files::isChild($root,$dir))) {
	$dir = $root;
}

$dirInfo = \OC\Files\Filesystem::getFileInfo($dir);

/* If requested resource is a file, serve it, otherwise produce opds feed */
switch ($dirInfo->getType()) {
	case 'file':
		if ($type) {
			Feed::servePreview($dir,$type);
		} else {
			Feed::serveFile($dir,$id);
		}
		break;
	case 'dir':
		Feed::serveFeed($dir, $id, $type);
		break;
	default:
		Util::logWarn("I don't know how to handle files of type " . $dirInfo->getType());
		break;
}
