<entry>
    <title><?php p($_['file']['name']); ?></title>
    <updated><?php p(date("Y-m-d\TH:i:sP", $_['file']['mtime'])); ?></updated>
    <id>id:<?php p($_['file']['id']); ?></id>
    <link type="application/atom+xml;profile=opds-catalog;kind=navigation"
        rel="alternate"
        href="?id=<?php p($_['file']['id']); ?>"/>
    <link type="application/atom+xml;profile=opds-catalog;kind=navigation"
        rel="subsection"
        href="?id=<?php p($_['file']['id']); ?>"/>
    <content type="text"></content>
</entry>
