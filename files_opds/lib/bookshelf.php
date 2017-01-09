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
 * Bookshelf class for publishing as OPDS
 *
 * This implements a 'personal bookshelf', listing books
 * which have been downloaded from the OPDS feed.
 */
class Bookshelf
{
	/**
	 * @brief add book to personal bookshelf
	 *
	 * @param int $id book to add to bookshelf
	 */
	public static function add($id) {
		$bookshelf = json_decode(Config::get('bookshelf', ''), true);
		if(!isset($bookshelf[$id])) {
			$bookshelf[$id]=time();
		}
		Config::set('bookshelf', json_encode($bookshelf));
	}

	/**
	 * @brief remove book from personal bookshelf
	 *
	 * @param int $id book to remove from bookshelf
	 */
	public static function remove($id) {
		$bookshelf = json_decode(Config::get('bookshelf', ''), true);
		if(isset($bookshelf[$id])) {
			unset($bookshelf[$id]);
			Config::set('bookshelf', json_encode($bookshelf));
		}
	}

	/**
	 * @brief clear personal bookshelf
	 */
	public static function clear() {
		Config::set('bookshelf', '');
	}

	/**
	 * @brief return number of books on personal bookshelf
	 * @return int number of books
	 */
	public static function count() {
		return substr_count(Config::get('bookshelf', ''), ':');
	}

	/**
	 * @brief list bookshelf contents
	 *
	 * @return array of FileInfo[], sorted by time added
	 */
	public static function get() {
		$files = array();
		if($bookshelf = json_decode(Config::get('bookshelf', ''), true)) {
			arsort($bookshelf);
			while (list($id, $time) = each($bookshelf)) {
				try {
					array_push($files, \OC\Files\Filesystem::getFileInfo(\OC\Files\Filesystem::normalizePath(\OC\Files\Filesystem::getPath($id))));
				} catch(\OCP\Files\NotFoundException $e) {
					self::remove($id);
					Meta::remove($id);
				}
			}
		}

		return $files;
	}
}
