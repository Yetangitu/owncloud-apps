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
