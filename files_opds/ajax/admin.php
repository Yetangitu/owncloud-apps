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
$defaults = new \OC_Defaults();

$l = new \OC_L10N('files_opds');

if (isset($_POST['opdsCoverX'])) {
	// set dimensions, using sane defaults just in case
	$opdsCoverX = isset($_POST['opdsCoverX']) ? (int) $_POST['opdsCoverX'] : 200;
	$opdsCoverY = isset($_POST['opdsCoverY']) ? (int) $_POST['opdsCoverY'] : 200;
	$opdsThumbX = isset($_POST['opdsThumbX']) ? (int) $_POST['opdsThumbX'] : 36;
	$opdsThumbY = isset($_POST['opdsThumbY']) ? (int) $_POST['opdsThumbY'] : 36;
	$opdsFeedSubtitle = isset($_POST['opdsFeedSubtitle']) ? $_POST['opdsFeedSubtitle'] : $l->t("%s OPDS catalog", $defaults->getName());

	Config::setApp('cover-x', $opdsCoverX);
	Config::setApp('cover-y', $opdsCoverY);
	Config::setApp('thumb-x', $opdsThumbX);
	Config::setApp('thumb-y', $opdsThumbX);
	Config::setApp('feed_subtitle', $opdsFeedSubtitle);
} else {
	// set preview preferences
	$opdsPreviewEpub = $_POST['opdsPreviewEpub'];
	$opdsPreviewPdf = $_POST['opdsPreviewPdf'];
	$opdsPreviewOpenOffice = $_POST['opdsPreviewOpenOffice'];
	$opdsPreviewMsOffice = $_POST['opdsPreviewMsOffice'];

	Config::setPreview('OC\Preview\Epub',$opdsPreviewEpub);
	Config::setPreview('OC\Preview\PDF',$opdsPreviewPdf);
	Config::setPreview('OC\Preview\OpenDocument',$opdsPreviewOpenOffice);
	Config::setPreview('OC\Preview\StarOffice',$opdsPreviewOpenOffice);
	Config::setPreview('OC\Preview\MSOfficeDoc',$opdsPreviewMsOffice);
	Config::setPreview('OC\Preview\MSOffice2003',$opdsPreviewMsOffice);
	Config::setPreview('OC\Preview\MSOffice2007',$opdsPreviewMsOffice);
}

\OCP\JSON::success(
array(
	'data' => array('message'=> $l->t('Settings updated successfully.'))
	)
);

exit();

