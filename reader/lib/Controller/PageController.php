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

use OCA\Files_Reader\Service\BookmarkService;
use OCA\Files_Reader\Service\MetadataService;
use OCA\Files_Reader\Service\PreferenceService;

class PageController extends Controller {

	/** @var IURLGenerator */
    private $urlGenerator;
    /** @var IRootFolder */
    private $rootFolder;
    private $userId;
    private $bookmarkService;
    private $metadataService;
    private $preferenceService;

	/**
	 * @param string $AppName
	 * @param IRequest $request
	 * @param IURLGenerator $urlGenerator
	 */
    public function __construct(
                            $AppName,
                            IRequest $request,
                            IURLGenerator $urlGenerator,
                            IRootFolder $rootFolder,
                            $UserId,
                            BookmarkService $bookmarkService,
                            PreferenceService $preferenceService,
                            MetadataService $metadataService) {
		parent::__construct($AppName, $request);
        $this->urlGenerator = $urlGenerator;
        $this->rootFolder = $rootFolder;
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

        $fileId = $this->getFileIdFromDownloadPath($this->request->get['file']);
		$type = $this->request->get["type"];
        $scope = $template = $templates[$type];

		$params = [
            'urlGenerator' => $this->urlGenerator,
            'downloadLink' => $this->request->get['file'],
            'scope' => $scope,
            'fileId' => $fileId,
            'cursor' => $this->toJson($this->bookmarkService->getCursor($fileId)),
            'defaults' => $this->toJson($this->preferenceService->getDefault($scope)),
            'preferences' => $this->toJson($this->preferenceService->get($scope, $fileId)),
            'defaults' => $this->toJson($this->preferenceService->getDefault($scope)),
            'metadata' => $this->toJson($this->metadataService->get($fileId))
		];


		
		$response = new TemplateResponse($this->appName, $template, $params, 'blank');
		/*
		$csp = new ContentSecurityPolicy();
		$csp->addAllowedStyleDomain('\'self\'');
		$csp->addAllowedStyleDomain('blob:');
		$csp->addAllowedScriptDomain('\'self\'');
		$csp->addAllowedFrameDomain('\'self\'');
		$csp->addAllowedChildSrcDomain('\'self\'');
		$csp->addAllowedFontDomain('\'self\'');
		$csp->addAllowedImageDomain('blob:');
		$response->setContentSecurityPolicy($csp);
		*/

		return $response;
    }

    private function getFileIdFromDownloadPath($path) {
        return $this->rootFolder->getUserFolder($this->userId)
            ->get(explode("/", rawurldecode($this->request->get['file']),4)[3])
            ->getFileInfo()
            ->getId();
    }

    private function toJson($value) {
        return htmlspecialchars(json_encode($value), ENT_QUOTES, 'UTF-8');
    }
}
