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
 * Utility class for OPDS
 */
class Util
{
	/**
	 * @brief Authenticate user by HTTP Basic Authentication
	 * with user name and password
	 */
	public static function authenticateUser() {
		if (!isset($_SERVER['PHP_AUTH_USER'])) {
			$defaults = new \OC_Defaults();
			$realm = $defaults->getName();
			header ("HTTP/1.0 401 Unauthorized");
			header ('WWW-Authenticate: Basic realm="' . $realm. '"');
                        exit();
                }

		$userName = $_SERVER['PHP_AUTH_USER'];

		// Check the password in the ownCloud database
                return self::checkPassword($userName, $_SERVER['PHP_AUTH_PW']);
        }

        /**
        * @brief Checks the password of a user. 
        * @param string $userName ownCloud user name whose password will be checked.
        * @param string $password ownCloud password.
        * @return bool True if the password is correct, false otherwise.
        *
        */
        private static function checkPassword($userName, $password) {

                // Check password normally
                if (\OCP\User::checkPassword($userName, $password) != false) {
                        return true;
                }

                return false;
        }

        /**
        * @brief Change HTTP response code.
        *
        * @param integer $statusCode The new HTTP status code.
        */
        public static function changeHttpStatus($statusCode) {

                $message = '';
                switch ($statusCode) {
                        case 400: $message = 'Bad Request'; break;
                        case 401: $message = 'Unauthorized'; break;
                        case 403: $message = 'Forbidden'; break;
                        case 404: $message = 'Not Found'; break;
                        case 500: $message = 'Internal Server Error'; break;
                        case 503: $message = 'Service Unavailable'; break;
                }

                // Set status code and status message in HTTP header
                header('HTTP/1.0 ' . $statusCode . ' ' . $message);
        }

	/**
	 * @brief generate v3 UUID based on display name and site url
	 *
	 * @return string uuid
	 */
	public static function genUuid() {
		$defaults = new \OC_Defaults();
		$hash = md5(\OCP\User::getDisplayName() . $defaults->getBaseUrl());
		$hash = substr($hash, 0, 8 ) .'-'.
			substr($hash, 8, 4) .'-3'.
			substr($hash, 13, 3) .'-9'.
			substr($hash, 17, 3) .'-'.
			substr($hash, 20);
		return $hash;
	}

	/**
	 * @brief log warning
	 * @param string message to write to log
	 */
	public static function logWarn($msg) {
		\OCP\Util::writeLog('files_opds', $msg, \OCP\Util::WARN);
	}
}
