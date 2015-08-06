<?php

$l = OC_L10N::get('files_opds');

\OCP\App::registerPersonal('files_opds', 'personal');
\OCP\App::registerAdmin('files_opds', 'admin');

/* register preview provider */
\OC::$server->getPreviewManager()->registerProvider('/application\/epub\+zip/', function() { return new OCA\Files_Opds\EpubPreview; });
