  <entry>
    <title><?php p($_['file']['meta']['title']); ?></title>
    <updated><?php p(date("Y-m-d\TH:i:sP",strtotime($_['file']['meta']['updated']))); ?></updated>
    <id>id:<?php p($_['file']['id']); ?></id>
    <dcterms:extent><?php p($_['file']['humansize']); ?></dcterms:extent>
    <dc:language><?php p($_['file']['meta']['language']); ?></dc:language>
    <?php foreach (json_decode($_['file']['meta']['author'],true) as $author): ?>
    <author>
      <name><?php p($author); ?></name>
    </author>
    <dc:identifier>urn:isbn:<?php p($_['file']['meta']['isbn']); ?></dc:identifier>
    <dc:publisher><?php p($_['file']['meta']['publisher']); ?></dc:publisher>
    <dc:issued><?php p($_['file']['meta']['date']); ?></dc:issued>
    <?php endforeach; ?>
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
