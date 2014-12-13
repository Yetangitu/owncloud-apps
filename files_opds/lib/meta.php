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
		if ($meta = self::load($id)) {
                        return $meta;
                } else {
                        $meta = self::create($id);
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
			self::save($meta);
                        return $meta;
                }
        }
}
