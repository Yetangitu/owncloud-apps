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


function formatMetadata($humansize,$mimetype,$name) {
	return    "Size: " . $humansize . "\n"
		. "Type: " . $mimetype . "\n"
		. "Filename: " . $name;
}

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>

<feed xmlns="http://www.w3.org/2005/Atom"
      xmlns:dc="http://purl.org/dc/terms/"
      xmlns:dcterms="http://purl.org/dc/terms/"
      xmlns:opds="http://opds-spec.org/2010/catalog">
  <id>id:<?php p($_['feed_id']); ?></id>
  <title><?php p($_['feed_title']); ?></title>
  <subtitle><?php p($_['feed_subtitle']); ?></subtitle>
  <updated><?php p(date("Y-m-d\TH:i:sP", $_['feed_updated'])); ?></updated>
  <author>
    <name><?php p($_['user']); ?></name>
  </author>

  <link rel="start"   
        href="?id=root"
        type="application/atom+xml;profile=opds-catalog;kind=navigation"/>
  <link rel="self"    
        href="?id=<?php p($_['id']); ?>"
        type="application/atom+xml;profile=opds-catalog;kind=navigation"/>


<?php
switch ($_['type']) {
	case 'root':
		print_unescaped($this->inc('part.feed.root'));
		break;

	case 'bookshelf':
		foreach ($_['bookshelf'] as $file) {
			print_unescaped($this->inc('part.feed.acquisition', [ 'file' => $file ]));
		}
        break;


	/* intentional fall-through */
    case 'author':
    case 'title':
    case 'genre':
	case 'directory':
	default:
		foreach ($_['files'] as $file) {
			if ($file['type'] == 'dir') {
				print_unescaped($this->inc('part.feed.navigation', [ 'file' => $file ]));
			} else {
				print_unescaped($this->inc('part.feed.acquisition', [ 'file' => $file ]));
			}
		}
		break;
}
?>
</feed>

