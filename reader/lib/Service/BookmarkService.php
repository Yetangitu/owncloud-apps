<?php
/**
 * @author Frank de Lange
 * @copyright 2017 Frank de Lange
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OCA\Files_Reader\Service;


use OCA\Files_Reader\Db\BookmarkMapper;

class BookmarkService extends Service {

    // use 'CURSOR_$UserId' to identify cursor (current position in book)
    const CURSOR = 'CURSOR';

    private $bookmarkMapper;
    private $userId;

    public function __construct(BookmarkMapper $bookmarkMapper, $UserId) {
        parent::__construct($bookmarkMapper);
        $this->bookmarkMapper = $bookmarkMapper;
        $this->userId = $UserId;
    }

    /**
     * @brief get bookmark
     *
     * bookmark type is format-dependent, eg CFI for epub, page number for CBR/CBZ, etc
     *
     * @param int $fileId
     * @param string $name
     *
     * @return array
     */
    public function get($fileId, $name=null) {
        $result = $this->bookmarkMapper->get($fileId, $name);
        return array_map(
            function($entity) {
                return [
                    'name' => $entity->getName(),
                    'value' => $entity->getValue(),
                    'lastModified' => $entity->getLastModified()
                ];
            }, $result);
    }

    /**
     * @brief write bookmark
     *
     * position type is format-dependent, eg CFI for epub, page number for CBR/CBZ, etc
     *
     * @param int $fileId
     * @param string $name
     * @param string $value
     *
     * @return array
     */
    public function set($fileId, $name, $value) {
        return $this->bookmarkMapper->set($fileId, $name, $value);
    }

    /**
     * @brief get cursor (current position in book)
     *
     * @param int $fileId
     *
     * @return array
     */
    public function getCursor($fileId) {
        $cursor = self::CURSOR . '_' . $this->userId;
        if (!empty($value = $this->get($fileId, $cursor))) {
            return $value[0];
        }
    }

    /**
     * @brief set cursor (current position in book)
     *
     * @param int $fileId
     * @param string $value
     *
     * @return array
     */
    public function setCursor($fileId, $value) {
        $cursor = self::CURSOR . '_' . $this->userId;
        return $this->bookmarkMapper->set($fileId, $cursor, $value);
    }
}
 
