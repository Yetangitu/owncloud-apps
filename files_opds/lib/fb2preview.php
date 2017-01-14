<?php

/**
 * Nextcloud - Files_Opds App
 *
 * @author Frank de Lange
 * @copyright 2016 Frank de Lange
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 */

namespace OCA\Files_Opds;

use OCP\Preview\IProvider;

/**
 * FB2 preview - returns cover or null
 */
class Fb2Preview implements IProvider {

        public function getMimeType() {
                return '/application\/x-fictionbook\+xml/';
        }

	public function getThumbnail($path, $maxX, $maxY, $scalingup, $fileview) {
                //get fileinfo
                $fileInfo = $fileview->getFileInfo($path);
                if(!$fileInfo) {
                        return false;
                }

                $absPath = $fileview->toTmpFile($path);

		$fb2 = new \OCA\Files_Opds\FB2($absPath);

		$cover = $fb2->Cover();

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

