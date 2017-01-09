<?php
/**
 * ownCloud - Files_Opds app
 *
 * Copyright (c) 2014 Frank de Lange
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OCA\Files_Opds;

use OCP\Util;

$l = \OC::$server->getL10N('files_opds');

\OCP\Util::addScript('files_opds', 'personal');

$tmpl = new \OCP\Template('files_opds', 'personal');
$opdsEnable = Config::get('enable', false);
$tmpl->assign('opdsEnable-checked', ($opdsEnable === 'true') ? 'checked="checked"' : '');
$tmpl->assign('opdsEnable-value', ($opdsEnable === 'true') ? '1' : '0');
$tmpl->assign('rootPath', Config::get('root_path', '/Library'));
$tmpl->assign('fileTypes', Config::get('file_types', ''));
$tmpl->assign('skipList', Config::get('skip_list', 'metadata.opf,cover.jpg'));
$tmpl->assign('feedTitle', Config::get('feed_title', $l->t("%s's Library", \OCP\User::getDisplayName())));
$tmpl->assign('bookshelf-count', Bookshelf::count());
$tmpl->assign('feedUrl', Util::linkToAbsolute('','index.php') . '/apps/files_opds/');

return $tmpl->fetchPage();

