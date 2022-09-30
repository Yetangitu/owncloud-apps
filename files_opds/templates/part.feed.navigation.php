<entry>
    <title><?php p($_['file_name']); ?></title>
    <updated><?php p(date("Y-m-d\TH:i:sP", $_['file_mtime'])); ?></updated>
    <id>id:<?php p($_['file_id']); ?></id>
    <link type="application/atom+xml;profile=opds-catalog;kind=navigation"
        rel="alternate"
        href="?id=<?php p($_['file_id']); ?>"/>
    <link type="application/atom+xml;profile=opds-catalog;kind=navigation"
        rel="subsection"
        href="?id=<?php p($_['file_id']); ?>"/>
    <content type="text"></content>
</entry>
