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

/**
 * Config class for publishing as OPDS
 */
class Config
{
	/**
	 * @brief get user config value
	 *
	 * @param string $key value to retrieve
	 * @param string $default default value to use
	 * @return string retrieved value or default
	 */
	public static function get($key, $default) {
		return \OC::$server->getConfig()->getUserValue(\OC_User::getUser(), 'files_opds', $key, $default);
	}

	/**
	 * @brief set user config value
	 *
	 * @param string $key key for value to change
	 * @param string $value value to use
	 * @return bool success
	 */
	public static function set($key, $value) {
		return \OC::$server->getConfig()->setUserValue(\OC_User::getUser(), 'files_opds', $key, $value);
	}

	/**
	 * @brief get app config value
	 *
	 * @param string $key value to retrieve
	 * @param string $default default value to use
	 * @return string retrieved value or default
	 */
	public static function getApp($key, $default) {
		return \OC::$server->getConfig()->getAppValue('files_opds', $key, $default);
	}

	/**
	 * @brief set app config value
	 *
	 * @param string $key key for value to change
	 * @param string $value value to use
	 * @return bool success
	 */
	public static function setApp($key, $value) {
		return \OC::$server->getConfig()->setAppValue('files_opds', $key, $value);
	}
	
	/**
	 * @brief get preview status
	 * 
	 * @param string format
	 * @return bool (true = enabled, false = disabled)
	 */
	public static function getPreview($format) {
		$enablePreviewProviders = \OC::$server->getConfig()->getSystemValue('enabledPreviewProviders', null);
		if (!($enablePreviewProviders === null)) {
			return in_array($format, $enablePreviewProviders);
		}
		return false;
	}

	/**
	 * @brief enable/disable preview for selected format
	 *
	 * @param string format
	 * @param bool enable (true = enable, false = disable, default = false)
	 * @return bool
	 */
	public static function setPreview($format, $enable = 'false') {
		$enablePreviewProviders = \OC::$server->getConfig()->getSystemValue('enabledPreviewProviders', null);
		if ($enable == 'true') {
			if ($enablePreviewProviders === null) {
				// set up default providers
				$enablePreviewProviders = array();
				array_push($enablePreviewProviders,
					'OC\Preview\Image',
					'OC\Preview\MP3',
					'OC\Preview\TXT',
					'OC\Preview\MarkDown');
			}
			if (!(in_array($format,$enablePreviewProviders))) {
				array_push($enablePreviewProviders, $format);
			}
		} else {
			if (!($enablePreviewProviders == null)) {
				$enablePreviewProviders = array_diff($enablePreviewProviders, array($format));
			}
		}

		if (!(\OC::$server->getConfig()->setSystemValue('enabledPreviewProviders', $enablePreviewProviders))) {
			Util::logWarn("Failed to enable " . $format . " preview provider (config.php readonly?)");
			return true;
		}
	}
}
