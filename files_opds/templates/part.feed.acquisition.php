  <entry>
    <title><?php p($_['xfile']['meta']['title']); ?></title>
    <updated><?php p(date("Y-m-d\TH:i:sP",strtotime($_['file']['meta']['updated']))); ?></updated>
    <id>id:<?php p($_['xfile']['id']); ?></id>
    <dcterms:extent><?php p($_['xfile']['humansize']); ?></dcterms:extent>
    <?php $authors = json_decode($_['xfile']['meta']['author'],true); if(is_array($authors)): foreach ($authors as $author): ?>
    <author>
      <name><?php p($author); ?></name>
    </author>
    <?php endforeach; endif; ?>
    <?php if($_['xfile']['meta']['isbn']): ?>
    <dc:identifier>urn:isbn:<?php p($_['xfile']['meta']['isbn']); ?></dc:identifier>
    <?php endif; ?>
    <?php if($_['xfile']['meta']['publisher']): ?>
    <dc:publisher><?php p($_['xfile']['meta']['publisher']); ?></dc:publisher>
    <?php endif; ?>
    <?php if($_['xfile']['meta']['language']): ?>
    <dc:language><?php p($_['xfile']['meta']['language']); ?></dc:language>
    <?php endif; ?>
    <dc:issued><?php p(date("Y-m-d\TH:i:sP",strtotime($_['xfile']['meta']['date']))); ?></dc:issued>
    <link type="<?php p($_['xfile']['mimetype']); ?>"
        rel="alternate"
        href="?id=<?php p($_['xfile']['id']); ?>"/>
    <link type="<?php p($_['xfile']['mimetype']); ?>"
        rel="http://opds-spec.org/acquisition/open-access"
        href="?id=<?php p($_['xfile']['id']); ?>"/>
    <link href="?pid=<?php p($_['xfile']['id']); ?>"
        rel="http://opds-spec.org/image"
        type="image/jpeg" />
    <link href="?tid=<?php p($_['xfile']['id']); ?>"
        rel="http://opds-spec.org/image/thumbnail"
        type="image/jpeg" />
    <?php if ($_['xfile']['meta']['description']): ?>
    <content type="text"><?php p($_['xfile']['meta']['description']); p("\n\n"); ?><?php p(formatMetadata($_['xfile']['humansize'],$_['xfile']['mimetype'],$_['xfile']['name'])); ?></content>
    <?php else: ?>
    <summary type="text"><?php p(formatMetadata($_['xfile']['humansize'],$_['xfile']['mimetype'],$_['xfile']['name'])); ?></summary>
    <?php endif; ?>
  </entry>
