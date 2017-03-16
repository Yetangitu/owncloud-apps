<?php
/**
 * @author Frank de Lange
 * @copyright 2015 Frank de Lange
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OCA\Files_Reader\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\Files\IRootFolder;
use OCP\Share\IManager;

use OCA\Files_Reader\Service\BookmarkService;
use OCA\Files_Reader\Service\MetadataService;
use OCA\Files_Reader\Service\PreferenceService;

class PageController extends Controller {

    /** @var IURLGenerator */
    private $urlGenerator;
    /** @var IRootFolder */
    private $rootFolder;
    private $shareManager;
    private $userId;
    private $bookmarkService;
    private $metadataService;
    private $preferenceService;

    /**
     * @param string $AppName
     * @param IRequest $request
     * @param IURLGenerator $urlGenerator
     * @param IRootFolder $rootFolder
     * @param IManager $shareManager
     * @param string $UserId
     * @param BookmarkService $bookmarkService
     * @param PreferenceService $preferenceService
     * @param MetadataService $metadataService
     */
    public function __construct(
            $AppName,
            IRequest $request,
            IURLGenerator $urlGenerator,
            IRootFolder $rootFolder,
            IManager $shareManager,
            $UserId,
            BookmarkService $bookmarkService,
            PreferenceService $preferenceService,
            MetadataService $metadataService) {
        parent::__construct($AppName, $request);
        $this->urlGenerator = $urlGenerator;
        $this->rootFolder = $rootFolder;
        $this->shareManager = $shareManager;
        $this->userId = $UserId;
        $this->bookmarkService = $bookmarkService;
        $this->metadataService = $metadataService;
        $this->preferenceService = $preferenceService;
    }

    /**
     * @PublicPage
     * @NoCSRFRequired
     *
     * @return TemplateResponse
     */
    public function showReader() {
        $templates= [
            'application/epub+zip' => 'epubreader',
            'application/x-cbr' => 'cbreader'
        ];

        /**
         *  $fileInfo = [
         *      fileId => null,
         *      fileName => null,
         *      fileType => null
         *  ];
         */
        $fileInfo = $this->getFileInfo($this->request->get['file']);
        $fileId = $fileInfo['fileId'];
        $type = $this->request->get["type"];
        $scope = $template = $templates[$type];

        $params = [
            'urlGenerator' => $this->urlGenerator,
            'downloadLink' => $this->request->get['file'],
            'scope' => $scope,
            'fileId' => $fileInfo['fileId'],
            'fileName' => $fileInfo['fileName'],
            'fileType' => $fileInfo['fileType'],
            'cursor' => $this->toJson($this->bookmarkService->getCursor($fileId)),
            'defaults' => $this->toJson($this->preferenceService->getDefault($scope)),
            'preferences' => $this->toJson($this->preferenceService->get($scope, $fileId)),
            'defaults' => $this->toJson($this->preferenceService->getDefault($scope)),
            'metadata' => $this->toJson($this->metadataService->get($fileId)),
            'annotations' => $this->toJson($this->bookmarkService->get($fileId))
        ];



        $response = new TemplateResponse($this->appName, $template, $params, 'blank');

        return $response;
    }

    /**
     * @brief sharing-aware file info retriever
     *
     * Work around the differences between normal and shared file access
     *
     * @param string $path path-fragment from url
     * @return array
     */ 
    private function getFileInfo($path) {
        $count = 0;
        $shareToken = preg_replace("/\/index\.php\/s\/([A-Za-z0-9]{15})\/download/", "$1", $path, 1,$count);
        if ($count === 1) {
            $node = $this->shareManager->getShareByToken($shareToken)->getNode();
            $filePath = $node->getPath();
            $fileId = $node->getId();
        } else {
            $filePath = $path;
            $fileId = $this->rootFolder->getUserFolder($this->userId)
                ->get(explode("/", rawurldecode($this->request->get['file']),4)[3])
                ->getFileInfo()
                ->getId();
        }

        return [
            fileName => pathInfo($filePath, PATHINFO_FILENAME),
            fileType => strtolower(pathInfo($filePath, PATHINFO_EXTENSION)),
            fileId => $fileId
        ];
    }

    private function toJson($value) {
        return htmlspecialchars(json_encode($value), ENT_QUOTES, 'UTF-8');
    }
}
