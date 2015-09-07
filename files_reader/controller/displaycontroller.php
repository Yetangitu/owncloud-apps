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

class DisplayController extends Controller {

	/** @var IURLGenerator */
	private $urlGenerator;

	/**
	 * @param string $AppName
	 * @param IRequest $request
	 * @param IURLGenerator $urlGenerator
	 */
	public function __construct($AppName, IRequest $request, IURLGenerator $urlGenerator) {
		parent::__construct($AppName, $request);
		$this->urlGenerator = $urlGenerator;
	}

	/**
	 * @PublicPage
	 * @NoCSRFRequired
	 *
	 * @return TemplateResponse
	 */
	public function showReader() {
		$params = [
			'urlGenerator' => $this->urlGenerator
		];

		$response = new TemplateResponse($this->appName, 'reader', $params, 'blank');

		$csp = new ContentSecurityPolicy();
		$csp->addAllowedChildSrcDomain('\'self\'');
		$csp->addAllowedFrameDomain('\'self\'');
		$csp->addAllowedStyleDomain('blob:');
		$csp->addAllowedImageDomain('blob:');
		$response->setContentSecurityPolicy($csp);

		return $response;
	}

}
