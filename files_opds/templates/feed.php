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
  <title><?php p($l->t("%s's library", array($_['user']))); ?></title>
  <subtitle><?php p($l->t("%s OPDS Catalog", array($_['ocname']))); ?></subtitle>
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
<?php if ($_['id'] == 'root'): ?>
  <entry>
    <title><?php p($l->t("Browse catalog")); ?></title>
    <updated><?php p(date("Y-m-d\TH:i:sP", $_['feed_updated'])); ?></updated>
    <content type="text"><?php p($l->t("Browse the catalog in alphabetical order")); ?></content>
    <link type="application/atom+xml;profile=opds-catalog;kind=navigation"
        href="?id=directory"/>
    <id>id:by_directory</id>
  </entry>
  <entry>
    <title><?php p($l->t("%s's bookshelf", array($_['user']))); ?></title>
    <updated><?php p(date("Y-m-d\TH:i:sP", $_['feed_updated'])); ?></updated>
    <content type="text"><?php p($l->t("This bookshelf contains %s books", array($_['bookshelf-count']))); ?></content>
    <link type="application/atom+xml;profile=opds-catalog;kind=navigation"
        href="?id=bookshelf"/>
    <id>id:by_bookshelf</id>
  </entry>
<?php elseif ($_['id'] == 'bookshelf'): ?>

<?php foreach ($_['bookshelf'] as $file): ?>
  <entry>
    <title><?php p($file['meta']['title']); ?></title>
    <updated><?php p(date("Y-m-d\TH:i:sP",strtotime($file['meta']['updated']))); ?></updated>
    <id>id:<?php p($file['id']); ?></id>
    <dcterms:extent><?php p($file['humansize']); ?></dcterms:extent>
    <dc:language><?php p($file['meta']['language']); ?></dc:language>
    <?php foreach (json_decode($file['meta']['author'],true) as $author): ?>
    <author>
      <name><?php p($author); ?></name>
    </author>
    <dc:identifier>urn:isbn:<?php p($file['meta']['isbn']); ?></dc:identifier>
    <dc:publisher><?php p($file['meta']['publisher']); ?></dc:publisher>
    <dc:issued><?php p($file['meta']['date']); ?></dc:issued>
    <?php endforeach; ?>
    <link type="<?php p($file['mimetype']); ?>"
        rel="alternate"
        href="?id=<?php p($file['id']); ?>"/>
    <link type="<?php p($file['mimetype']); ?>"
        rel="http://opds-spec.org/acquisition/open-access"
        href="?id=<?php p($file['id']); ?>"/>
    <link href="<?php p($file['preview']); ?>"
        rel="http://opds-spec.org/image"
        type="image/jpeg" />
    <link href="<?php p($file['thumbnail']); ?>"
        rel="http://opds-spec.org/image/thumbnail"
        type="image/jpeg" />
    <summary type="text"><?php p(formatMetadata($file['humansize'],$file['mimetype'],$file['name'])); ?></summary>
    <content type="text"><?php p("Size: " . $file['humansize'] . "\n\n"); ?><?php p($file['meta']['description']); ?></content>
  </entry>
<?php endforeach; ?>
<?php else: ?>
<?php foreach ($_['files'] as $file): ?>
<?php if ($file['type'] == 'dir'): ?>
  <entry>
    <title><?php p($file['name']); ?></title>
    <updated><?php p(date("Y-m-d\TH:i:sP", $file['mtime'])); ?></updated>
    <id>id:<?php p($file['id']); ?></id>
    <link type="application/atom+xml;profile=opds-catalog;kind=navigation"
        rel="alternate"
        href="?id=<?php p($file['id']); ?>"/>
    <link type="application/atom+xml;profile=opds-catalog;kind=navigation"
        rel="subsection"
        href="?id=<?php p($file['id']); ?>"/>
    <content type="text"></content>
  </entry>
<?php else: ?>
  <entry>
    <title><?php p($file['meta']['title']); ?></title>
    <updated><?php p(date("Y-m-d\TH:i:sP",strtotime($file['meta']['updated']))); ?></updated>
    <id>id:<?php p($file['id']); ?></id>
    <dcterms:extent><?php p($file['humansize']); ?></dcterms:extent>
    <dc:language><?php p($file['meta']['language']); ?></dc:language>
    <?php foreach (json_decode($file['meta']['author'],true) as $author): ?>
    <author>
      <name><?php p($author); ?></name>
    </author>
    <dc:identifier>urn:isbn:<?php p($file['meta']['isbn']); ?></dc:identifier>
    <dc:publisher><?php p($file['meta']['publisher']); ?></dc:publisher>
    <dc:issued><?php p($file['meta']['date']); ?></dc:issued>
    <?php endforeach; ?>
    <link type="<?php p($file['mimetype']); ?>"
        rel="alternate"
        href="?id=<?php p($file['id']); ?>"/>
    <link type="<?php p($file['mimetype']); ?>"
        rel="http://opds-spec.org/acquisition/open-access"
        href="?id=<?php p($file['id']); ?>"/>
    <link href="<?php p($file['preview']); ?>"
        rel="http://opds-spec.org/image"
        type="image/jpeg" />
    <link href="<?php p($file['thumbnail']); ?>"
        rel="http://opds-spec.org/image/thumbnail"
        type="image/jpeg" />
    <summary type="text"><?php p(formatMetadata($file['humansize'],$file['mimetype'],$file['name'])); ?></summary>
    <content type="text"><?php p("Size: " . $file['humansize'] . "\n\n"); ?><?php p($file['meta']['description']); ?></content>
  </entry>
<?php endif; ?>
<?php endforeach; ?>
<?php endif; ?>
</feed>

