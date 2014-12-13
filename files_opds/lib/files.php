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
                $entry['mtime'] = $i['mtime'] * 1000;
                $entry['name'] = $i->getName();
                $entry['type'] = $i['type'];
		if ($i['type'] === 'file') {
                	$entry['mimetype'] = $i['mimetype'];
                	$entry['preview'] = self::getPreview($i);
                	$entry['thumbnail'] = self::getThumbnail($i);
                	$entry['humansize'] = \OC_Helper::humanFileSize($i['size']);
			$entry['meta'] = Util::getMeta($i['fileid']);
		} else {
                	$entry['icon'] = self::determineIcon($i);
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

	/**
	 * @brief get preview for file
	 * @param \OCP\Files\FileInfo $i
	 * @return string preview URL
	 */
	public static function getPreview($i) {
		if (\OC::$server->getPreviewManager()->isMimeSupported($i['mimetype'])) {
			return \OC_Helper::linkToRoute( 'core_ajax_preview', array('x' => Config::getApp('cover-x', '200'), 'y' => Config::getApp('cover-y', '200'), 'file' => \OC\Files\Filesystem::normalizePath(\OC\Files\Filesystem::getPath($i['fileid']))));
		} else {
			return self::determineIcon($i);
		}
	}


	/**
	 * @brief get thumbnail for file
	 * @param \OCP\Files\FileInfo $i
	 * @return string preview URL
	 */
	public static function getThumbnail($i) {
		if (\OC::$server->getPreviewManager()->isMimeSupported($i['mimetype'])) {
			return \OC_Helper::linkToRoute( 'core_ajax_preview', array('x' => Config::getApp('thumb-x', '36'), 'y' => Config::getApp('thumb-y', '36'), 'file' => \OC\Files\Filesystem::normalizePath(\OC\Files\Filesystem::getPath($i['fileid']))));
		} else {
			return self::determineIcon($i);
		}
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
