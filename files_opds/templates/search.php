<?php

/**
 * next/ownCloud - Files_Opds App
 *
 * @author Frank de Lange
 * @copyright 2017 Frank de Lange
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 */

 /** @var OCP\IURLGenerator $urlGenerator */
$urlGenerator = $_['urlGenerator'];

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>

<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/">
   <ShortName>OPDS Catalog Search</ShortName>
   <Description>Search <?php p($_['feed_title']); ?></Description>
   <Tags><?php p($_['feed_subtitle']); ?></Tags>
   <Contact><?php p($_['contact']); ?></Contact>
   <Url type="application/atom+xml"
        template="<?php p($urlGenerator->linkTo('files_opds','?query={searchTerms}&amp;author={atom:author}&amp;contributor={atom:contributor}&amp;title={atom:title}&amp;pw={startPage?}&amp;format=atom"/>
   <Url type="application/rss+xml"
        template="http://example.com/?q={searchTerms}&amp;pw={startPage?}&amp;format=rss"/>
   <Url type="text/html" 
        template="http://example.com/?q={searchTerms}&amp;pw={startPage?}"/>
   <LongName>Search <?php p($_['feed_title']); ?> - <?php p($_['feed_subtitle']); ?></LongName>
   <Image height="64" width="64" type="image/svg"><?php p($urlGenerator->linkTo('files_opds', 'img/app.svg')) ?></Image>
   <Query role="example" searchTerms="epub" />
   <Developer>Example.com Development Team</Developer>
   <Language>en-us</Language>
   <OutputEncoding>UTF-8</OutputEncoding>
   <InputEncoding>UTF-8</InputEncoding>
</OpenSearchDescription>
