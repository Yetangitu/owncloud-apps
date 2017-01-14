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

$l = \OC::$server->getL10N('files_opds');

\OCP\Util::addScript('files_opds', 'admin');
\OCP\Util::addStyle('files_opds', 'settings');
$defaults = new \OC_Defaults();

$formats = array(
	["epub" => Config::getPreview('OC\Preview\Epub') ? 1 : 0 ],
	["fb2" => Config::getPreview('OC\Preview\FB2') ? 1 : 0 ],
	["pdf" => Config::getPreview('OC\Preview\PDF') ? 1 : 0],
	["opendocument" => Config::getPreview('OC\Preview\OpenDocument') ? 1 : 0],
	["msoffice" => Config::getPreview('OC\Preview\MSOfficeDoc') ? 1 : 0]
	);

$tmpl = new \OCP\Template('files_opds', 'admin');
$tmpl->assign('feedSubtitle', Config::getApp('feed-subtitle', $l->t("%s OPDS catalog", $defaults->getName())));
$tmpl->assign('isbndbKey', Config::getApp('isbndb-key', ''));
$tmpl->assign('googleKey', Config::getApp('google-key', ''));
$tmpl->assign('previewFormats', $formats);
$tmpl->assign('cover-x', Config::getApp('cover-x', '200'));
$tmpl->assign('cover-y', Config::getApp('cover-y', '200'));
$tmpl->assign('thumb-x', Config::getApp('thumb-x', '36'));
$tmpl->assign('thumb-y', Config::getApp('thumb-y', '36'));

return $tmpl->fetchPage();
