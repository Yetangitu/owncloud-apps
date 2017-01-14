<?php
$l = \OC::$server->getL10N('files_opds');

\OCP\App::registerPersonal('files_opds', 'personal');
\OCP\App::registerAdmin('files_opds', 'admin');

/* register preview providers */
\OC::$server->getPreviewManager()->registerProvider('/application\/epub\+zip/', function() { return new OCA\Files_Opds\EpubPreview; });
\OC::$server->getPreviewManager()->registerProvider('/application\/x-fictionbook\+xml/', function() { return new OCA\Files_Opds\Fb2Preview; });
