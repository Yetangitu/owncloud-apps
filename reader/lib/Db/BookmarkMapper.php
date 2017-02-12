<?php
/**
 * @author Frank de Lange
 * @copyright 2017 Frank de Lange
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OCA\Files_Reader\Db;


use OCP\IDBConnection;

use OCA\Files_Reader\Utility\Time;

error_log("in bookmarkmapper");

class BookmarkMapper extends ReaderMapper {

    private $userId;

    /**
     * @param IDbConnection $db
     * @param $UserId
     * @param Time $time
     */
    public function __construct(IDBConnection $db, $UserId, Time $time) {
        parent::__construct($db, 'reader_bookmarks', Bookmark::class, $time);
        /** @var int $UserId */
        $this->userId = $UserId;
    }

    /**
     * @brief get bookmarks for $fileId+$userId(+$name)
     * @param $fileId
     * @param string $name
     * @return array
     */
    public function get($fileId, $name=null) {
        if (!empty($name)) {
            $sql = "SELECT * FROM `*PREFIX*reader_bookmarks` WHERE file_id=? AND `user_id`=? AND `name`=?";
            $args = [
                $fileId,
                $this->userId,
                $name];
        } else {
        $sql = "SELECT * FROM `*PREFIX*reader_bookmarks` WHERE file_id=? AND `user_id`=?";
        $args = [
            $fileId,
            $this->userId];
        }

        return $this->findEntities($sql, $args);
    }

    /**
     * @brief write bookmark to database
     *
     * @param int $fileId
     * @param string $name
     * @param string $value
     *
     * @return Bookmark the newly created or updated bookmark
     */
    public function set($fileId, $name, $value) {

        $result = $this->get($fileId, $name);

        if(empty($result)) {

            // anonymous bookmarks are named after their contents
            if(empty($name)) {
                $name = $value;
            }

            $bookmark = new Bookmark();
            $bookmark->setFileId($fileId);
            $bookmark->setUserId($this->userId);
            $bookmark->setName($name);
            $bookmark->setValue($value);

            $this->insert($bookmark);
        } else {
            $bookmark = $result[0];
            $bookmark->setValue($value);

            $this->update($bookmark);
        }

        return $bookmark;
    }
}
 
