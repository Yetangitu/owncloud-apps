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
	 * @brief offer single file for download
	 * 
	 * @param string $path full path to file
	 * @param int $id file id
	 */
	public static function serveFile($path, $id) {
		\OCP\User::checkLoggedIn();
		\OC::$session->close();
		Bookshelf::add($id);
		$dirName = dirname($path);
		$fileName = basename($path);
		\OC_Files::get($dirName, array($fileName), $_SERVER['REQUEST_METHOD'] == 'HEAD');
	}

	/**
	 * @brief serve opds feed for given directory
	 *
	 * @param string $dir full path to directory
	 * @param int $id requested id
	 */
	public static function serveFeed($dir, $id) {
		if (isset($_SERVER['HTTP_ACCEPT']) && stristr($_SERVER['HTTP_ACCEPT'], 'application/atom+xml')) {
                        header('Content-Type: application/atom+xml');
                } else {
                        header('Content-Type: text/xml; charset=UTF-8');
                }
                $sortAttribute = 'name';
                $sortDirection = false;
                $defaults = new \OC_Defaults();
                $tmpl = new \OCP\Template('files_opds', 'feed');
                $tmpl->assign('files', Files::formatFileInfos(Files::getFiles($dir, $sortAttribute, $sortDirection)));
		$tmpl->assign('bookshelf', Files::formatFileInfos(Bookshelf::get()));
		$tmpl->assign('bookshelf-count', Bookshelf::count());
                $tmpl->assign('feed_id', self::getFeedId());
                $tmpl->assign('id', $id);
                $tmpl->assign('dir', $dir);
                $tmpl->assign('user', \OCP\User::getDisplayName());
                $tmpl->assign('ocname', $defaults->getName());
                $tmpl->printPage();
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
	 * @brief get feed id
	 *
	 * @return string feed id
	 */
	public static function getFeedId() {
		return Config::get('id', '');
	}

	/**
	 * @brief log warning
	 * @param string message to write to log
	 */
	public static function logWarn($msg) {
		\OCP\Util::writeLog('files_opds', $msg, \OCP\Util::WARN);
	}

	/**
	 * @brief get metadata for fileid
	 * 
	 * Long function, to be split later
	 *
	 * @param int $id fileid
	 * @return array of metadata
	 */
	public static function getMeta($id) {
		$sql = 'SELECT * FROM `*PREFIX*opds_metadata` WHERE id = ?';
		$args = array($id);
		$query = \OCP\DB::prepare($sql);
		$result = $query->execute($args);
		if ($row = $result->fetchRow()) {
			return $row;
		} else {
			/* start with empty values, except for id. This way, files only get
			 * scanned once, even if they don't contain valid metadate.
			 */
			$meta = array();
			$meta['id'] = $id;
			$meta['updated'] = date("Y-m-d\TH:i:sP");
			$meta['date'] = '';
			$meta['author'] = '';
			$meta['title'] = '';
			$meta['language'] = '';
			$meta['publisher'] = '';
			$meta['isbn'] = '';
			$meta['copyright'] = '';
			$meta['description'] = '';
			$meta['subjects'] = '';
			
			$path = \OC\Files\Filesystem::getLocalFile(\OC\Files\Filesystem::getPath($id));
			switch (strtolower(substr(strrchr($path, "."), 1))) {
				case 'epub':
					$epub = new Epub($path);
					$meta['author'] = json_encode($epub->Authors());
					$meta['title'] = $epub->Title();
					$meta['date'] = $epub->Date();
					$meta['publisher'] = $epub->Publisher();
					$meta['copyright'] = $epub->Copyright();
					$meta['language'] = $epub->Language();
					$meta['description'] = strip_tags($epub->Description());
					$meta['isbn'] = $epub->ISBN();
					$meta['subjects'] = $epub->Subjects();
					break;
				default:
					// set title to filename minus extension
					$info = pathinfo($path);
					$meta['title'] = basename($path,'.'.$info['extension']);
					break;
			}
			$sql = "INSERT INTO *PREFIX*opds_metadata (`id`, `updated`, `date`, `author`, `title`, `language`, `publisher`, `isbn`, `copyright`, `description`, `subjects`) VALUES (?,?,?,?,?,?,?,?,?,?,?)";
			$args = array(
				$meta['id'],
				$meta['updated'],
				$meta['date'],
				$meta['author'],
				$meta['title'],
				$meta['language'],
				$meta['publisher'],
				$meta['isbn'],
				$meta['copyright'],
				$meta['description'],
				$meta['subjects']
				);
			$query = \OCP\DB::prepare($sql);
			$result = $query->execute($args);
			return $meta;
		}
	}
}
