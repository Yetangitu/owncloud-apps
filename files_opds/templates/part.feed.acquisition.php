  <entry>
    <title><?php p($_['file']['meta']['title']); ?></title>
    <updated><?php p(date("Y-m-d\TH:i:sP",strtotime($_['file']['meta']['updated']))); ?></updated>
    <id>id:<?php p($_['file']['id']); ?></id>
    <dcterms:extent><?php p($_['file']['humansize']); ?></dcterms:extent>
    <?php $authors = json_decode($_['file']['meta']['author'],true); if(is_array($authors)): foreach ($authors as $author): ?>
    <author>
      <name><?php p($author); ?></name>
    </author>
    <?php endforeach; endif; ?>
    <?php if($_['file']['meta']['isbn']): ?>
    <dc:identifier>urn:isbn:<?php p($_['file']['meta']['isbn']); ?></dc:identifier>
    <?php endif; ?>
    <?php if($_['file']['meta']['publisher']): ?>
    <dc:publisher><?php p($_['file']['meta']['publisher']); ?></dc:publisher>
    <?php endif; ?>
    <?php if($_['file']['meta']['language']): ?>
    <dc:language><?php p($_['file']['meta']['language']); ?></dc:language>
    <?php endif; ?>
    <dc:issued><?php p(date("Y-m-d\TH:i:sP",strtotime($_['file']['meta']['date']))); ?></dc:issued>
    <link type="<?php p($_['file']['mimetype']); ?>"
        rel="alternate"
        href="?id=<?php p($_['file']['id']); ?>"/>
    <link type="<?php p($_['file']['mimetype']); ?>"
        rel="http://opds-spec.org/acquisition/open-access"
        href="?id=<?php p($_['file']['id']); ?>"/>
    <link href="?pid=<?php p($_['file']['id']); ?>"
        rel="http://opds-spec.org/image"
        type="image/jpeg" />
    <link href="?tid=<?php p($_['file']['id']); ?>"
        rel="http://opds-spec.org/image/thumbnail"
        type="image/jpeg" />
    <?php if ($_['file']['meta']['description']): ?>
    <content type="text"><?php p($_['file']['meta']['description']); p("\n\n"); ?><?php p(formatMetadata($_['file']['humansize'],$_['file']['mimetype'],$_['file']['name'])); ?></content>
    <?php else: ?>
    <summary type="text"><?php p(formatMetadata($_['file']['humansize'],$_['file']['mimetype'],$_['file']['name'])); ?></summary>
    <?php endif; ?>
  </entry>
