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

use OC\Authentication\Exceptions\PasswordLoginForbiddenException;
use OC\User\LoginException;

/**
 * Utility class for OPDS
 */
class Util
{
	/**
	 * @brief Authenticate user by HTTP Basic Authentication with username and password or token
	 * 
	 * Supports login as well as app passwords (tokens).
	 * NC: only app passwords are accepted when 2FA is enforced for $user
	 *
	 * @throws OC\Authentication\Exceptions\PasswordLoginForbiddenException;
	 * @throws OC\User\LoginException;
	 */
	public static function authenticateUser() {
		$request = \OC::$server->getRequest();

		// force basic auth, enables access through browser
		if (!isset($request->server['PHP_AUTH_USER'])) {
			$defaults = new \OC_Defaults();
			$realm = $defaults->getName();
			header ("HTTP/1.0 401 Unauthorized");
			header ('WWW-Authenticate: Basic realm="' . $realm. '"');
                        exit();
                }

		$user = $request->server['PHP_AUTH_USER'];
		$pass = $request->server['PHP_AUTH_PW'];

		try {
			//if (!\OC::$server->getUserSession()->logClientIn($user, $pass, $request, $throttler)) {
			if (!self::logClientIn($user, $pass, $request)) {
				// unknown user and/or password
				self::changeHttpStatus(401);
				exit();
			}
		} catch (PasswordLoginForbiddenException $ex) {
			// 2FA active and enforced for user so only app passwords are allowed
			self::changeHttpStatus(401);
			exit();
		} catch (LoginException $ex) {
			// login cancelled or user forbidden
			self::changeHttpStatus(403);
			exit();
		}
        }

	/**
	 * @brief attempt to login using $user and $pass (password or token)
	 * 
	 * Login using username and password, supports both traditional passwords as well as
	 * token-based login ('app passwords').
	 *
	 * @param string $user
	 * @param string $pass
	 * @param IRequest $request
	 * @throws PasswordLoginForbiddenException
	 * @throws LoginException
	 * @return boolean
	 *
	 */
	public static function logClientIn($user, $pass, $request) {
		if (class_exists('OC\Security\Bruteforce\Throttler')) {
			$throttler = \OC::$server->getBruteForceThrottler();
			return \OC::$server->getUserSession()->logClientIn($user, $pass, $request, $throttler);
		} else {
			return \OC::$server->getUserSession()->logClientIn($user, $pass, $request);
		}
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
