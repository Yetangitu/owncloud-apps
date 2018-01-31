<?php

/**
 * ownCloud - Files_Reader App
 *
 * @author Frank de Lange
 * @copyright 2014,2018 Frank de Lange
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 */

namespace OCA\Files_Reader;

\OCP\JSON::callCheck();
\OCP\JSON::checkLoggedIn();

$l = \OC::$server->getL10N('files_reader');

$EpubEnable = isset($_POST['EpubEnable']) ? $_POST['EpubEnable'] : 'false';
$PdfEnable = isset($_POST['PdfEnable']) ? $_POST['PdfEnable'] : 'false';
$CbxEnable = isset($_POST['CbxEnable']) ? $_POST['CbxEnable'] : 'false';

Config::set('epub_enable', $EpubEnable);
Config::set('pdf_enable', $PdfEnable);
Config::set('cbx_enable', $CbxEnable);

\OCP\JSON::success(
    array(
		'data' => array('message'=> $l->t('Settings updated successfully.'))
    )
);

exit();

