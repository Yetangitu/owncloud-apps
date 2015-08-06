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

use OCP\Preview\IProvider;

/**
 * Epub preview - returns cover or null
 */
class EpubPreview implements IProvider {

        public function getMimeType() {
                return '/application\/epub\+zip/';
        }

	public function getThumbnail($path, $maxX, $maxY, $scalingup, $fileview) {
                //get fileinfo
                $fileInfo = $fileview->getFileInfo($path);
                if(!$fileInfo) {
                        return false;
                }

                $absPath = $fileview->toTmpFile($path);

		$epub = new \OCA\Files_Opds\Epub($absPath);

		$cover = $epub->Cover();

		if ($cover) {
			$image = new \OC_Image();

			$image->loadFromData($cover['data']);
		}

		return (($cover !== null) && $image->valid()) ? $image : false;
        }

	public function isAvailable(\OCP\Files\FileInfo $file) {
		return $file->getSize() > 0;
	}
}

