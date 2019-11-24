<?php
namespace OCA\Files_Opds;

if ( Config::get('old_mime', 'false') !== 'false' && strpos($_['file_mimetype'],'comicbook') !== false){
	$mime = 'application/x-cbr';
} else {
	$mime = $_['file_mimetype'];
}
?>
<entry>
    <title><?php p($_['file_meta']['title']); ?></title>
    <updated><?php p(date("Y-m-d\TH:i:sP",strtotime($_['file_meta']['updated']))); ?></updated>
    <id>id:<?php p($_['file_id']); ?></id>
    <dcterms:extent><?php p($_['file_humansize']); ?></dcterms:extent>
    <?php $authors = json_decode($_['file_meta']['author'],true); if(is_array($authors)): foreach ($authors as $author): ?>
    <author>
      <name><?php p($author); ?></name>
    </author>
    <?php endforeach; endif; ?>
    <?php if($_['file_meta']['isbn']): ?>
    <dc:identifier>urn:isbn:<?php p($_['file_meta']['isbn']); ?></dc:identifier>
    <?php endif; ?>
    <?php if($_['file_meta']['publisher']): ?>
    <dc:publisher><?php p($_['file_meta']['publisher']); ?></dc:publisher>
    <?php endif; ?>
    <?php if($_['file_meta']['language']): ?>
    <dc:language><?php p($_['file_meta']['language']); ?></dc:language>
    <?php endif; ?>
    <dc:issued><?php p(date("Y-m-d\TH:i:sP",strtotime($_['file_meta']['date']))); ?></dc:issued>
    <link type="<?php p($mime); ?>"
        rel="alternate"
        href="?id=<?php p($_['file_id']); ?>"/>
    <link type="<?php p($mime); ?>"
        rel="http://opds-spec.org/acquisition/open-access"
        href="?id=<?php p($_['file_id']); ?>"/>
    <link href="?pid=<?php p($_['file_id']); ?>"
        rel="http://opds-spec.org/image"
        type="image/jpeg" />
    <link href="?tid=<?php p($_['file_id']); ?>"
        rel="http://opds-spec.org/image/thumbnail"
        type="image/jpeg" />
    <?php if ($_['file_meta']['description']): ?>
    <content type="text"><?php p($_['file_meta']['description']); p("\n\n"); ?><?php p(formatMetadata($_['file_humansize'],$mime,$_['file_name'])); ?></content>
    <?php else: ?>
    <summary type="text"><?php p(formatMetadata($_['file_humansize'],$mime,$_['file_name'])); ?></summary>
    <?php endif; ?>
  </entry>
