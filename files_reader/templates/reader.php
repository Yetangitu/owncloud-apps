<?php
  /** @var array $_ */
  /** @var OCP\IURLGenerator $urlGenerator */
  $urlGenerator = $_['urlGenerator'];
  $version = \OCP\App::getAppVersion('files_reader');
  $dllink = isset($_GET['file']) ? $_GET['file'] : '';
  $title = htmlentities(basename($dllink));

  /* Owncloud currently does not implement CSPv3, remove this test when it does */
  $nonce = class_exists('\OC\Security\CSP\ContentSecurityPolicyNonceManager')
    ? \OC::$server->getContentSecurityPolicyNonceManager()->getNonce()
    : 'nonce_not_implemented';
?>

<html dir="ltr">
   <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
      <meta name="apple-mobile-web-app-capable" content="yes">
      <base href="<?php p($urlGenerator->linkTo('files_reader',''));?>">
      <title>
         <?php p($title);?>
      </title>
      <link rel="shortcut icon" href="img/book.png">
      <link rel="stylesheet" href="css/normalize.css">
      <link rel="stylesheet" href="css/main.css">
      <link rel="stylesheet" href="css/popup.css">
      <link rel="stylesheet" href="css/tooltip.css">
      <script type="text/javascript" nonce="<?php p($nonce) ?>" src="<?php p($urlGenerator->linkTo('files_reader', 'js/lib/typedarray.min.js')) ?>?v=<?php p($version) ?>"> </script>
      <script type="text/javascript" nonce="<?php p($nonce) ?>" src="<?php p($urlGenerator->linkTo('files_reader', 'js/lib/Blob.js')) ?>?v=<?php p($version) ?>"> </script>
      <script type="text/javascript" nonce="<?php p($nonce) ?>" src="<?php p($urlGenerator->linkTo('files_reader', 'js/lib/wgxpath.install.js')) ?>?v=<?php p($version) ?>"> </script>
      <script type="text/javascript" nonce="<?php p($nonce) ?>" src="<?php p($urlGenerator->linkTo('files_reader', 'vendor/epubjs/libs/jquery.min.js')) ?>?v=<?php p($version) ?>"> </script>
      <script type="text/javascript" nonce="<?php p($nonce) ?>" src="<?php p($urlGenerator->linkTo('files_reader', 'vendor/epubjs/libs/screenfull.min.js')) ?>?v=<?php p($version) ?>"> </script>
      <script type="text/javascript" nonce="<?php p($nonce) ?>" src="<?php p($urlGenerator->linkTo('files_reader', 'vendor/epubjs/libs/zip.min.js')) ?>?v=<?php p($version) ?>"> </script>
      <script type="text/javascript" nonce="<?php p($nonce) ?>" src="<?php p($urlGenerator->linkTo('files_reader', 'vendor/epubjs/epub.min.js')) ?>?v=<?php p($version) ?>"> </script>
      <script type="text/javascript" nonce="<?php p($nonce) ?>" src="<?php p($urlGenerator->linkTo('files_reader', 'vendor/epubjs/hooks.min.js')) ?>?v=<?php p($version) ?>"> </script>
      <script type="text/javascript" nonce="<?php p($nonce) ?>" src="<?php p($urlGenerator->linkTo('files_reader', 'vendor/epubjs/hooks/extensions/highlight.js')) ?>?v=<?php p($version) ?>"> </script>
      <script type="text/javascript" nonce="<?php p($nonce) ?>" src="<?php p($urlGenerator->linkTo('files_reader', 'vendor/epubjs/reader.min.js')) ?>?v=<?php p($version) ?>"> </script>
     
      <script type="text/javascript" nonce="<?php p($nonce) ?>" src="<?php p($urlGenerator->linkTo('files_reader', 'js/ready.js')) ?>?v=<?php p($version) ?>"> </script>
   </head>
   <body>
      <input type="hidden" id="dllink" value="<?php print_unescaped($dllink);?>">
      <div id="outerContainer">
         <div id="sidebar">
            <div id="panels">
               <input id="searchBox" placeholder="not implemented yet" type="search" disabled="">
               <a id="show-Search" class="show_view icon-search" data-view="Search">
                  <?php p($l->t("Search")); ?>
               </a>
               <a id="show-Toc" class="show_view icon-list-1 active" data-view="Toc">
                  <?php p($l->t("TOC")); ?>
               </a>
               <a id="show-Bookmarks" class="show_view icon-bookmark" data-view="Bookmarks">
                  <?php p($l->t("Bookmarks")); ?>
               </a>
               <a id="show-Notes" class="show_view icon-edit" data-view="Notes">
                  <?php p($l->t("Notes")); ?>
               </a>
            </div>
            <div id="tocView" class="view">
            </div>
            <div id="searchView" class="view">
               <ul id="searchResults">
               </ul>
            </div>
            <div id="bookmarksView" class="view">
               <ul id="bookmarks">
               </ul>
            </div>
            <div id="notesView" class="view">
               <div id="new-note">
                  <textarea id="note-text">
                  </textarea>
                  <button id="note-anchor">
                     <?php p($l->t("Anchor")); ?>
                  </button>
               </div>
               <ol id="notes">
               </ol>
            </div>
         </div>
         <div id="main">
            <div id="titlebar">
               <div id="opener">
                  <a id="slider" class="icon-menu">
                     <?php p($l->t("Menu")); ?>
                  </a>
               </div>
               <div id="metainfo">
                  <span id="book-title">
                  </span>
                  <span id="title-seperator">
                       –  
                  </span>
                  <span id="chapter-title">
                  </span>
               </div>
               <div id="title-controls">
                  <a id="bookmark" class="icon-bookmark-empty">
                     <?php p($l->t("Bookmark")); ?>
                  </a>
                  <a id="setting" class="icon-cog">
                     <?php p($l->t("Settings")); ?>
                  </a>
                  <a id="fullscreen" class="icon-resize-full">
                     <?php p($l->t("Fullscreen")); ?>
                  </a>
                  <a id="close" class="icon-cancel-circled2" style="display:none">
                     <?php p($l->t("Close")); ?>
                  </a>
               </div>
            </div>
            <div id="divider">
            </div>
            <div id="prev" class="arrow nonight">
               <div class="nonight">
                  ‹
               </div>
            </div>
            <div id="viewer">
            </div>
            <div id="next" class="arrow nonight">
               <div class="nonight">
                  ›
               </div>
            </div>
            <div id="loader" class="nonight">
               <img src="img/loading.gif">
            </div>
         </div>
         <div class="modal md-effect-1" id="settings-modal">
            <div class="md-content">
               <h3>
                  <?php p($l->t("Settings")); ?>
               </h3>
               <div>
                  <p>
                     <input type="checkbox" id="ignore_css" name="ignore_css">
                     <label for="ignore_css">
                        <?php p($l->t("Always use")); ?>
                     </label>
                     <select id="fontFamily" disabled="">
                        <option value="verdana, trebuchet, droid sans serif, sans, sans-serif">
                           Sans
                        </option>
                        <option value="georgia, times new roman, droid serif, serif">
                           Serif
                        </option>
                        <option value="monospace">
                           Monospace
                        </option>
                     </select>
                     <?php p($l->t("font scaled to")); ?>
                     <input type="number" id="fontSize" value="100" min="50" max="150" disabled="">
                     %
                  </p>
                  <div id="font_example" class="user">
                     <?php p($l->t("Et nos esse veri viri scire volemus")); ?>
                  </div>
                  <p>
                     <input type="checkbox" id="sidebarReflow" name="sidebarReflow">
                     <label for="sidebarReflow">
                        <?php p($l->t("Reflow text when sidebars are open.")); ?>
                     </label>
                  </p>
                  <p>
                     <?php p($l->t("Night mode background")); ?>
                     <input type="color" id="nightModeBackground" value="#000000">
                     <?php p($l->t("and text")); ?>
                     <input type="color" id="nightModeColor" value="#3A516B">
                     <?php p($l->t("colour")); ?>
                  </p>
                  <div id="nightModeExample" class="night">
                     <div>
                        Et nos esse veri viri scire volemus
                     </div>
                  </div>
                  <p>
                     <input type="checkbox" id="touch_nav" name="touch_nav">
                     <label for="touch_nav" class="tooltip">
                        <?php p($l->t("Disable extra-wide page turn areas")); ?> 
								<span>
									<?php p($l->t("The extra-wide page turn areas as used by default on touch-screen devices interfere with the ability to select links in ebooks. When this option is enabled, the page-turn area is always outside the ebook margins so links are reachable.")); ?>
								</span>
                     </label>
                 </p>
               </div>
               <div class="closer icon-cancel-circled nonight">
               </div>
            </div>
         </div>
         <div class="overlay nonight">
         </div>
      </div>
   </body>
</html>
