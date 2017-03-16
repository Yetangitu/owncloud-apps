<?php

/**
 * ownCloud - Files_Reader App
 *
 * @author Frank de Lange
 * @copyright 2015 - 2017 Frank de Lange
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 */

namespace OCA\Files_Reader\AppInfo;

use OCP\AppFramework\App;
use OCP\Util;

\OCA\Files_Reader\Hooks::register();
Util::addscript('files_reader', 'plugin');

if(class_exists('\\OCP\\AppFramework\\Http\\EmptyContentSecurityPolicy')) {
	$manager = \OC::$server->getContentSecurityPolicyManager();
	$csp = new \OCP\AppFramework\Http\EmptyContentSecurityPolicy();
	$csp->addAllowedStyleDomain('\'self\'');
	$csp->addAllowedStyleDomain('blob:');	
	$csp->addAllowedScriptDomain('\'self\'');
	$csp->addAllowedFrameDomain('\'self\'');
	$csp->addAllowedChildSrcDomain('\'self\'');
	$csp->addAllowedFontDomain('\'self\'');
	$csp->addAllowedImageDomain('blob:');
	$manager->addDefaultPolicy($csp);
}
