<entry>
    <title><?php p($_['xfile']['name']); ?></title>
    <updated><?php p(date("Y-m-d\TH:i:sP", $_['file']['mtime'])); ?></updated>
    <id>id:<?php p($_['xfile']['id']); ?></id>
    <link type="application/atom+xml;profile=opds-catalog;kind=navigation"
        rel="alternate"
        href="?id=<?php p($_['xfile']['id']); ?>"/>
    <link type="application/atom+xml;profile=opds-catalog;kind=navigation"
        rel="subsection"
        href="?id=<?php p($_['xfile']['id']); ?>"/>
    <content type="text"></content>
</entry>
