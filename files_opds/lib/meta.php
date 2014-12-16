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
 * Meta (data) class for OPDS
 */
class Meta
{
	/**
	 * @brief create a new metadata array for a given id
	 * 
	 * @param int $id fileid
	 * @return array of metadata
	 */
	public static function create($id) {
		$meta = array();

		/* start with empty values, except for id. This way, files only get
		 * scanned once, even if they don't contain valid metadate.
		 */
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

		return $meta;
	}

	/**
	 * @brief load metadata from database
	 *
	 * @return array or false
	 */
	protected static function load($id) {
                $sql = 'SELECT * FROM `*PREFIX*opds_metadata` WHERE id = ?';
                $args = array($id);
                $query = \OCP\DB::prepare($sql);
                $result = $query->execute($args);

		return ($row = $result->fetchRow()) ? $row : false;
	}

	/**
	 * @brief write metadata to database
	 *
	 * @param array $meta metadata
	 * @return OC_DB_StatementWrapper
	 */
	protected static function save($meta) {
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

		return $query->execute($args);
	}

        /**
         * @brief get metadata for fileid
         * 
         * Long function, to be split later
         *
         * @param int $id fileid
         * @return array of metadata
         */
        public static function get($id) {
		if (!($meta = self::load($id))) {
			$meta = self::scan($id);
                }
		return $meta;
        }

	/**
	 * @brief scan files for metadata
	 * PLAN: use search_lucene to extract metadata? Does not seem to support PDF1.6? 
	 *       solution: first ask search_lucene, if no data then scan file?
	 *
	 * @param int $id fileid
	 * @return array $meta metadata
	 */
	public static function scan($id) {
		$meta = self::create($id);
		$path = \OC\Files\Filesystem::getLocalFile(\OC\Files\Filesystem::getPath($id));
		
		switch (strtolower(substr(strrchr($path, "."), 1))) {
			case 'epub':
				self::epub($path,$meta);
				break;
			case 'pdf':
				self::pdf($path,$meta);
				break;
		}

		/* if title is not set, assume metadata was invalid or not present
		 * use filename as title
		 */
		if (!($meta['title'])) {
			$info = pathinfo($path);
			$meta['title'] = basename($path,'.'.$info['extension']);
		}
		// self::save($meta);
		return $meta;
	}


	/**
	 * @brief check epub for metadata
	 *
	 * @param string $path path to epub
	 * @param arrayref $meta reference to array of metadata
	 * @return bool $success (true if metadata found)
	 */
	public static function epub($path,&$meta) {
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

		return true;
	}

	/**
	 * @brief check pdf for metadata
	 *
	 * @param string $path path to pdf
	 * @param arrayref $meta reference to array of metadata
	 * @return bool $success (true if metadata found)
	 */
	public static function pdf($path,&$meta) {

		return false;
	}
}
