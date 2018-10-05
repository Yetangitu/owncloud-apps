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

$l = \OC::$server->getL10N('files_opds');

\OC_JSON::checkLoggedIn();
\OC_JSON::callCheck();

Meta::rescan();
\OC_JSON::success(array( "data" => array( "message" => $l->t("Rescan scheduled")))); 
