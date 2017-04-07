<?php
/**
 * @author Frank de Lange
 * @copyright 2017 Frank de Lange
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OCA\Files_Reader;

use OCP\IDBConnection;
use OCP\Files\Node;
use OCP\IUser;
use \OC\User\User as User;

class Hooks {

    public static function register() {
        \OC::$server->getRootFolder()->listen('\OC\Files', 'preDelete', function (Node $node) {
            $fileId = $node->getId();
            $connection = \OC::$server->getDatabaseConnection();
            self::deleteFile($connection, $fileId);
        });
        \OC::$server->getUserManager()->listen('\OC\User', 'preDelete', function (User $user) {
            $userId = $user->getUID();
            $connection = \OC::$server->getDatabaseConnection();
            self::deleteUser($connection, $userId);
        });
    }

    protected static function deleteFile(IDBConnection $connection, $fileId) {
        $queryBuilder = $connection->getQueryBuilder();
        $queryBuilder->delete('reader_bookmarks')->where('file_id = :file_id')->setParameter(':file_id', $fileId);
        $queryBuilder->execute();

        $queryBuilder = $connection->getQueryBuilder();
        $queryBuilder->delete('reader_preferences')->where('file_id = :file_id')->setParameter(':file_id', $fileId); 
        $queryBuilder->execute();
    }

    protected static function deleteUser(IDBConnection $connection, $userId) {
        $queryBuilder = $connection->getQueryBuilder();
        $queryBuilder->delete('reader_bookmarks')->where('user_id = :user_id')->setParameter(':user_id', $userId);
        $queryBuilder->execute();

        $queryBuilder = $connection->getQueryBuilder();
        $queryBuilder->delete('reader_preferences')->where('user_id = :user_id')->setParameter(':user_id', $userId); 
        $queryBuilder->execute();
    }

}
