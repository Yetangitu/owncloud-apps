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
 * Files class, extendes \OCA\Files, tailored for OPDS
 */
class Files extends \OCA\Files\Helper
{
        /**
         * Formats the file info to be returned as OPDS to the client
         *
         * @param \OCP\Files\FileInfo $i
         * @return array formatted file info
         */
        public static function formatFileInfo($i) {
                $entry = array();

                $entry['id'] = $i['fileid'];
                $entry['mtime'] = $i['mtime'];
                $entry['name'] = $i->getName();
                $entry['type'] = $i['type'];
		if ($i['type'] === 'file') {
                	$entry['mimetype'] = $i['mimetype'];
                	$entry['humansize'] = \OC_Helper::humanFileSize($i['size']);
			$entry['meta'] = Meta::get($i['fileid']);
		} 
                return $entry;
        }

        /**
         * Format file info for OPDS feed
         * @param \OCP\Files\FileInfo[] $fileInfos file infos
         */
        public static function formatFileInfos($fileInfos) {
                $files = array();
		/* if set, add only files with given extensions */
		$fileTypes = array_filter(explode(',', strtolower(Config::get('file_types', ''))));
                foreach ($fileInfos as $i) {
			if((!empty($fileTypes)) && (!in_array(strtolower(substr(strrchr($i->getName(), "."), 1)), $fileTypes))) {
				continue;
			}
                        $files[] = self::formatFileInfo($i);
                }

                return $files;
        }

	/*
	 * @brief check if $child is a subdirectory of $parent
	 *
	 * @param string $parent a directory
	 * @param string $child a directory
	 * @return bool true if $child is a subdirectory of $parent
	 */
	public static function isChild($parent, $child) {
		return strpos($child, $parent . '/') === 0;
	}
}
