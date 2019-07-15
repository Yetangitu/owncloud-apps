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
use OCP\ILogger;

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
		$meta['updated'] = date("Y-m-d H:i:s");
		$meta['date'] = '';
		$meta['author'] = '';
		$meta['title'] = '';
		$meta['language'] = '';
		$meta['publisher'] = '';
		$meta['isbn'] = '';
		$meta['copyright'] = '';
		$meta['description'] = '';
		$meta['subjects'] = '';
		$meta['cover'] = null;
		$meta['rescan'] = null;

		return $meta;
	}

	/** 
	 * @brief check whether metadata is valid (ie. title, author and language are defined)
	 *
	 * @param array $meta metadata
	 * @return bool true if valid
	 */
	public static function isValid($meta) {
		return (!(empty($meta['title']) || empty($meta['author']) || empty($meta['language'])));
	}

	/**
	 * @brief load metadata from database
	 *
	 * @return array or false
	 */
	protected static function xload($id) {
                $sql = 'SELECT * FROM `*PREFIX*opds_metadata` WHERE id = ?';
                $args = array($id);
                $query = \OC_DB::prepare($sql);
                $result = $query->execute($args);

		return ($row = $result->fetch()) ? $row : false;
	}

	/**
	 * @brief write metadata to database
	 *
	 * @param array $meta metadata
	 * @return OC_DB_StatementWrapper
	 */
	protected static function save($meta) {
		$sql = "SELECT `id` FROM *PREFIX*opds_metadata WHERE `id`=?";
		$args = array($meta['id']);
		$query = \OC_DB::prepare($sql);
		$result = $query->execute($args);
		$data = $result->fetch();
		if (isset($data['id'])) {
			$sql = "UPDATE *PREFIX*opds_metadata SET `updated`=?, `date`=?, `author`=?, `title`=?, `language`=?, `publisher`=?, `isbn`=?, `copyright`=?, `description`=?, `subjects`=?, `cover`=?, `rescan`=? WHERE id=?";
			$args = array(
				$meta['updated'],
				$meta['date'],
				$meta['author'],
				$meta['title'],
				$meta['language'],
				$meta['publisher'],
				preg_replace('/[^0-9xX]/','',$meta['isbn']),
				mb_strimwidth($meta['copyright'],0,252,'...'),
				mb_strimwidth($meta['description'],0,2044,'...'),
				$meta['subjects'],
				$meta['cover'],
				$meta['rescan'],
				$meta['id']
				);
		} else {
			$sql = "INSERT INTO *PREFIX*opds_metadata (`id`, `updated`, `date`, `author`, `title`, `language`, `publisher`, `isbn`, `copyright`, `description`, `subjects`, `cover`, `rescan`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";
			$args = array(
				$meta['id'],
				$meta['updated'],
				$meta['date'],
				$meta['author'],
				$meta['title'],
				$meta['language'],
				$meta['publisher'],
				preg_replace('/[^0-9xX]/','',$meta['isbn']),
				mb_strimwidth($meta['copyright'],0,252,'...'),
				mb_strimwidth($meta['description'],0,2044,'...'),
				$meta['subjects'],
				$meta['cover'],
				$meta['rescan']
				);

		}
		$query = \OC_DB::prepare($sql);

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
		if (!($meta = self::xload($id)) || (isset($meta['rescan']) && time() > strtotime($meta['rescan']))) {
			if(isset($meta['rescan'])) {
				$meta['rescan'] = null;
			}
			$meta = self::scan($id);
                }
		return $meta;
        }

	/**
	 * @brief remove metadata for fileid
	 *
	 * @param int $id fileif
	 * @return OC_DB_StatementWrapper
	 */
	public static function remove($id) {
		$sql = "DELETE FROM *PREFIX*opds_metadata WHERE `id`=?";
		$args = array($id);
		$query = \OC_DB::prepare($sql);

		return $query->execute($args);
	}

	/**
	 * @brief schedule rescan of metadata
	 * 
	 */
	public static function rescan() {
                $sql = "UPDATE *PREFIX*opds_metadata SET `rescan`=?";
                $args = array(date("Y-m-d H:i:s"));
                $query = \OC_DB::prepare($sql);
                $result = $query->execute($args);
	}

	/**
	 * @brief scan files for metadata
	 *
	 * @param int $id fileid
	 * @return array $meta metadata
	 */
	public static function scan($id) {
		$meta = self::create($id);
		$path = \OC\Files\Filesystem::getLocalFile(\OC\Files\Filesystem::getPath($id));

		/* scan for Calibre-generated metadata.opf first. If found, use it as metadata source,
		 * if not found continue file/isbn/etc scan
		 */
		if(!(Calibre::get($path,$meta))) {
			/* try to call function named 'type' with signature type($path,$meta)
			 * eg, pdf(), epub(), etc
			 */
			$type = strtolower(substr(strrchr($path, "."), 1));
			if(is_callable(array(__CLASS__, $type))) {
				try {
					self::$type($path,$meta);
				} catch (Exception $e) {
					Util::logWarn("no metadata scanner for format " . $type);
				}
			}
		}
		
		/* if title is not set, assume metadata was invalid or not present
		 * use filename as title
		 */
		if (!($meta['title'])) {
			$info = pathinfo($path);
			$meta['title'] = basename($path,'.'.$info['extension']);
		}

		self::save($meta);
		return $meta;
	}

	/**
	 * @brief check epub for metadata
	 *
	 * @param string $path path to epub
	 * @param arrayref $meta reference to array of metadata
	 */
	public static function epub($path,&$meta) {
		$success = false;
		try {
			$epub = new Epub($path);
			/* first try ISBN */
			if(!(($isbn = $epub->ISBN()) && (Isbn::get($isbn, $meta)))) {
				/* use EPUB internal metadata instead */
				$meta['author'] = json_encode($epub->Authors());
				$meta['title'] = $epub->Title();
				$meta['date'] = $epub->Date();
				$meta['publisher'] = $epub->Publisher();
				$meta['copyright'] = $epub->Copyright();
				$meta['language'] = $epub->Language();
				$meta['description'] = strip_tags($epub->Description());
				$meta['isbn'] = $epub->ISBN();
				$meta['subjects'] = json_encode($epub->Subjects());
			}
		} catch (\Exception $e) {
			\OCP\Util::writeLog(get_class(), $e->getMessage(), ILogger::ERROR);
		}
	}

	/**
	 * @brief check fb2 for metadata
	 *
	 * @param string $path path to fb2
	 * @param arrayref $meta reference to array of metadata
	 */
	public static function fb2($path,&$meta) {
		$success = false;
		try {
			$fb2 = new FB2($path);
			/* first try ISBN */
			if(!(($isbn = $fb2->ISBN()) && (Isbn::get($isbn, $meta)))) {
				/* use FB2 internal metadata instead */
				$meta['author'] = json_encode($fb2->Authors());
				$meta['title'] = $fb2->Title();
				$meta['date'] = $fb2->Date();
				$meta['publisher'] = $fb2->Publisher();
				$meta['copyright'] = $fb2->Copyright();
				$meta['language'] = $fb2->Language();
				$meta['description'] = strip_tags($fb2->Description());
				$meta['isbn'] = $fb2->ISBN();
				$meta['subjects'] = json_encode($fb2->Subjects());
			}
		} catch (\Exception $e) {
			\OCP\Util::writeLog(get_class(), $e->getMessage(), ILogger::ERROR);
		}
	}

	/**
	 * @brief check pdf for metadata
	 *
	 * @param string $path path to pdf
	 * @param arrayref $meta reference to array of metadata
	 */
	public static function pdf($path,&$meta) {
		/* first, try to get metadata through ISBN */
		$command = ['pdftotext -l 10 "','" -'];
		$output=array();
		exec($command[0] . $path . $command[1], $output);
		if (!(($output) && ($isbn = Isbn::scan($output)) && (Isbn::get($isbn,$meta)))) {
			/* No ISBN, try PDF metadata */
			$output=array();
			$command = ["pdfinfo '","'|grep -we '^\(Title\|Author\|Subject\|Keywords\|CreationDate\|ModDate\)'"];
			exec($command[0] . $path . $command[1], $output);
			foreach($output as $data) {
				list($key, $value) = explode(':',$data,2);
				$value = trim($value);
			}

			if (isset($value) && !($value == '')) {
				switch ($key) {
					case 'Title':
						$meta['title'] = $value;
						break;
					case 'Author':
						$meta['author'] = $value;
						break;
					case 'Subject':
					case 'Keywords':
						$meta['subjects'] .= $value;
						break;
					case 'CreationDate':
					case 'ModDate':
						$meta['date'] = strtotime($value); 
						break;
				}
			}
		}
	}
}
