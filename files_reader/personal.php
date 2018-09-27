<?php
/**
 * ownCloud - Files_Reader app
 *
 * Copyright (c) 2014,2018 Frank de Lange
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OCA\Files_Reader;

use OCP\Util;

#$l = \OC::$server->getL10N('files_reader');

$tmpl = new \OCP\Template('files_reader', 'settings-personal');
$EpubEnable = \OC::$server->getConfig()->getAppValue('epub_enable', 'true');
$PdfEnable = \OC::$server->getConfig()->getAppValue('pdf_enable', 'true');
$CbxEnable = \OC::$server->getConfig()->getAppValue('cbx_enable', 'true');
$tmpl->assign('EpubEnable', $EpubEnable);
$tmpl->assign('PdfEnable', $PdfEnable);
$tmpl->assign('CbxEnable', $CbxEnable);

return $tmpl->fetchPage();
