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
 * Feed class for OPDS
 */
class Feed
{
        /**
         * @brief get feed id
         *
         * @return string feed id
         */
        public static function getFeedId() {
                return Config::get('id', '');
        }

        /**
         * @brief offer single file for download
         * 
         * @param string $path full path to file
         * @param int $id file id
         */
        public static function serveFile($path, $id) {
                \OCP\User::checkLoggedIn();
                \OC::$server->getSession()->close();
                Bookshelf::add($id);
                $dirName = dirname($path);
                $fileName = basename($path);
                \OC_Files::get($dirName, array($fileName), $_SERVER['REQUEST_METHOD'] == 'HEAD');
        }

        /**
         * @brief serve opds feed for given directory
         *
         * @param string $dir full path to directory
         * @param int $id requested id
	 * @param string $type request type (root, bookshelf, directory)
         */
        public static function serveFeed($dir, $id, $type) {
                if (isset($_SERVER['HTTP_ACCEPT']) && stristr($_SERVER['HTTP_ACCEPT'], 'application/atom+xml')) {
                        header('Content-Type: application/atom+xml');
                } else {
                        header('Content-Type: text/xml; charset=UTF-8');
                }
                $sortAttribute = 'name';
                $sortDirection = false;
                $defaults = new \OC_Defaults();
                $tmpl = new \OCP\Template('files_opds', 'feed');
                $tmpl->assign('files', Files::formatFileInfos(Files::getFiles($dir, $sortAttribute, $sortDirection)));
                $tmpl->assign('bookshelf', Files::formatFileInfos(Bookshelf::get()));
                $tmpl->assign('bookshelf-count', Bookshelf::count());
                $tmpl->assign('feed_id', self::getFeedId());
                $tmpl->assign('id', $id);
                $tmpl->assign('type', $type);
                $tmpl->assign('dir', $dir);
                $tmpl->assign('user', \OCP\User::getDisplayName());
                $tmpl->assign('feed_title', Config::get('feed_title',\OCP\User::getDisplayName() . "'s Library"));
                $tmpl->assign('feed_subtitle', Config::getApp('feed_subtitle', $defaults->getName() . " OPDS catalog"));
                $tmpl->assign('feed_updated', time());
                $tmpl->printPage();
        }

        /**
         * @brief offer preview for download
	 *
	 * if no preview exists for this file, send icon instead
         * 
         * @param string $path full path to file
         * @param string type type of preview requested
         */
        public static function servePreview($path, $type) {
                \OCP\User::checkLoggedIn();
                \OC::$server->getSession()->close();
		$i = \OC\Files\Filesystem::getFileInfo($path,false);

		/* check for predefined cover, if found replace $path with that of cover file */
		$meta = Meta::get($i['fileid']);
		if($meta['cover']) {
			$path = pathinfo($path)['dirname'] .'/' . $meta['cover'];
			$i = \OC\Files\Filesystem::getFileInfo($path,false);
		}

		if (\OC::$server->getPreviewManager()->isMimeSupported($i['mimetype'])) {
			$preview = new \OC\Preview(\OC_User::getUser(), 'files');
			$preview->setFile($path);
			switch ($type) {
				case 'cover':
					$preview->setMaxX(Config::getApp('cover-x', '200'));
					$preview->setMaxY(Config::getApp('cover-y', '200'));
					break;
				case 'thumbnail':
					$preview->setMaxX(Config::getApp('thumb-x', '36'));
					$preview->setMaxY(Config::getApp('thumb-y', '36'));
					break;
			}
			$preview->showPreview();
		} else {
			// no preview, serve icon instead
			$scheme = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
				|| $_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http';
			header("Location: "
				. $scheme
				. "://"
				. $_SERVER['HTTP_HOST']
				. \OC::$server->getMimeTypeDetector()->mimeTypeIcon($i->getMimeType())
				);
			/* Note: relative URL should be enough (RFC7231) but some OPDS clients
			 * (especially those in dedicated book readers) might not support them
			 * 
			 * header("Location: " . \OC::$server->getMimeTypeDetector()->mimeTypeIcon($i->getMimeType()));
			 */
		}
        }
}
