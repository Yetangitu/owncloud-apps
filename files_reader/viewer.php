<?php

OCP\App::checkAppEnabled('files_reader');

$dir = isset($_GET['dir']) ? $_GET['dir'] : '';
$file = isset($_GET['file']) ? $_GET['file'] : '';
$share = isset($_GET['share']) ? $_GET['share'] : '';

// TODO: add mime type detection and load the template
$mime = "application/zip+epub";

// download link varies by sharing status, compose it here
$dllink = $share === ''
	// ? OC_Helper::linkTo('files', 'ajax/download.php', array('dir' => urldecode($dir), 'files' => urldecode($file), 'share' => $share ))
	? OC_Helper::linkTo('files', 'ajax/download.php', array('dir' => $dir, 'files' => $file, 'share' => $share ))
	: OC_Helper::linkToPublic('files') . '&t=' . rawurlencode($share) . '&download';

// needed for css/script inclusion
$base = OC_Helper::linkTo('files_reader', '');

$title = htmlentities($file);

$page = new OCP\Template( 'files_reader', 'reader');
$page->assign('title', $title);
$page->assign('dllink', $dllink);
$page->assign('base', $base);
$page->printPage();
