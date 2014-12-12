<?php

$l = OC_L10N::get('files_opds');

require 'files_opds/lib/epub-preview.php';

\OCP\App::registerPersonal('files_opds', 'personal');
\OCP\App::registerAdmin('files_opds', 'admin');

/* enable preview providers... */
// \OCA\Files_Opds\Config::setPreview('OC\Preview\Epub',true);

/* ..and register preview provider */
\OC\Preview::registerProvider('OC\Preview\Epub');

