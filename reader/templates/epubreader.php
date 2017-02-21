<?php
 /** @var array $_ */
  /** @var OCP\IURLGenerator $urlGenerator */
  $urlGenerator = $_['urlGenerator'];
  $downloadLink = $_['downloadLink'];
  $fileId = $_['fileId'];
  $scope = $_['scope'];
  $cursor = $_['cursor'];
  $defaults = $_['defaults'];
  $preferences = $_['preferences'];
  $metadata = $_['metadata'];
  $revision = '0021';
  $version = \OCP\App::getAppVersion('files_reader') . '.' . $revision;

  /* Owncloud currently does not implement CSPv3, remove this test when it does */
  $nonce = class_exists('\OC\Security\CSP\ContentSecurityPolicyNonceManager')
    ? \OC::$server->getContentSecurityPolicyNonceManager()->getNonce()
    : 'nonce_not_implemented';
?>

<html dir="ltr">
    <head class="session" data-nonce='<?php p($nonce);?>' data-downloadlink='<?php print_unescaped($downloadLink);?>' data-fileid='<?php print_unescaped($fileId);?>' data-version='<?php print_unescaped($version);?>' data-basepath='<?php p($urlGenerator->linkTo('files_reader',''));?>' data-scope='<?php print_unescaped($scope);?>' data-cursor='<?php print_unescaped($cursor);?>' data-defaults='<?php print_unescaped($defaults);?>' data-preferences='<?php print_unescaped($preferences);?>' data-metadata='<?php print_unescaped($metadata);?>'>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <base href="<?php p($urlGenerator->linkTo('files_reader',''));?>">
        <title>
            <?php p($_['title']);?>
        </title>
        <link rel="shortcut icon" href="img/book.png">
        <link rel="stylesheet" href="<?php p($urlGenerator->linkTo('files_reader', 'vendor/epubjs/css/main.css')) ?>?v=<?php p($version) ?>"> </script>
		<link rel="stylesheet" href="<?php p($urlGenerator->linkTo('files_reader', 'vendor/epubjs/css/sidebar.css')) ?>?v=<?php p($version) ?>"> </script>
		<link rel="stylesheet" href="<?php p($urlGenerator->linkTo('files_reader', 'vendor/epubjs/css/popup.css')) ?>?v=<?php p($version) ?>"> </script>
		<link rel="stylesheet" href="<?php p($urlGenerator->linkTo('files_reader', 'vendor/epubjs/css/tooltip.css')) ?>?v=<?php p($version) ?>"> </script>
		<script type="text/javascript" nonce="<?php p($nonce) ?>" src="<?php p($urlGenerator->linkTo('files_reader', 'js/lib/typedarray.min.js')) ?>?v=<?php p($version) ?>"> </script>
		<script type="text/javascript" nonce="<?php p($nonce) ?>" src="<?php p($urlGenerator->linkTo('files_reader', 'js/lib/Blob.js')) ?>?v=<?php p($version) ?>"> </script>
		<script type="text/javascript" nonce="<?php p($nonce) ?>" src="<?php p($urlGenerator->linkTo('files_reader', 'js/lib/wgxpath.install.js')) ?>?v=<?php p($version) ?>"> </script>
		<script type="text/javascript" nonce="<?php p($nonce) ?>" src="<?php p($urlGenerator->linkTo('files_reader', 'vendor/epubjs/libs/jquery.min.js')) ?>?v=<?php p($version) ?>"> </script>
		<script type="text/javascript" nonce="<?php p($nonce) ?>" src="<?php p($urlGenerator->linkTo('files_reader', 'vendor/bartaz/jquery.highlight.js')) ?>?v=<?php p($version) ?>"> </script>
		<script type="text/javascript" nonce="<?php p($nonce) ?>" src="<?php p($urlGenerator->linkTo('files_reader', 'vendor/sindresorhus/screenfull.js')) ?>?v=<?php p($version) ?>"> </script>
		<script type="text/javascript" nonce="<?php p($nonce) ?>" src="<?php p($urlGenerator->linkTo('files_reader', 'vendor/epubjs/libs/zip.min.js')) ?>?v=<?php p($version) ?>"> </script>
		<script type="text/javascript" nonce="<?php p($nonce) ?>" src="<?php p($urlGenerator->linkTo('files_reader', 'vendor/epubjs/epub.min.js')) ?>?v=<?php p($version) ?>"> </script>
		<script type="text/javascript" nonce="<?php p($nonce) ?>" src="<?php p($urlGenerator->linkTo('files_reader', 'vendor/epubjs/hooks.min.js')) ?>?v=<?php p($version) ?>"> </script>

		<script type="text/javascript" nonce="<?php p($nonce) ?>" src="<?php p($urlGenerator->linkTo('files_reader', 'vendor/epubjs/hooks/extensions/highlight.js')) ?>?v=<?php p($version) ?>"> </script>
		<script type="text/javascript" nonce="<?php p($nonce) ?>" src="<?php p($urlGenerator->linkTo('files_reader', 'vendor/epubjs/reader.min.js')) ?>?v=<?php p($version) ?>"> </script>
		<script type="text/javascript" nonce="<?php p($nonce) ?>" src="<?php p($urlGenerator->linkTo('files_reader', 'vendor/epubjs/plugins/search.js')) ?>?v=<?php p($version) ?>"> </script>

		<script type="text/javascript" nonce="<?php p($nonce) ?>" src="<?php p($urlGenerator->linkTo('files_reader', 'js/ready.js')) ?>?v=<?php p($version) ?>"> </script>
    </head>
    <body>
        <div id="outerContainer">

            <!-- sidebar -->

            <div id="sidebar" class="sidebar">
                <div id="panels" class="panels">
                    <div class="pull-left">
                        <button id="show-Toc" class="show_view icon-list-1 open" title="Table of Contents" data-view="Toc"></button>
                        <button id="show-Bookmarks" class="show_view icon-bookmark" title="Bookmarks" data-view="Bookmarks"></button>
                        <button id="show-Search" class="show_view icon-search" title="Search" data-view="Search"></button>
                        <button id="show-Notes" class="show_view icon-edit" title="Notes" data-view="Notes"></button>
                        <button id="show-Settings" class="show_view icon-cog" title="Settings" data-view="Settings"></button>
                    </div>
                    <div class="pull-right">
                        <button id="close-Sidebar" class="close_sidebar icon-right" title="Close sidebar"></button>
                    </div>
                </div>
                <div id="tocView" class="toc-view view open">
                </div>
                <div id="bookmarksView" class="bookmarks-view view">
                    <ul id="bookmarks">
                    </ul>
                </div>
                <div id="searchView" class="view search-view">
                    <div>
                        <div class="search-input">
                            <input id="searchBox" class="searchbox" placeholder="search..." type="input">
                            <span title="Clear">x</span>
                            </form>
                        </div>
                        <ul id="searchResults" class="search-results">
                        </ul>
                    </div>
                </div>
                <div id="notesView" class="notes-view view">
                    <div id="new-note" class="new-note">
                        <textarea id="note-text" class="note-text">
                        </textarea>
                        <button id="note-anchor" class="note-anchor">Anchor</button>
                    </div>
                    <ol id="notes" class="notes">
                    </ol>
                </div>
                <div id="settingsView" class="settings-view view">
                    <fieldset class="settings-container" name="font-settings">
                        <legend>font</legend>
                        <div class="control-group">
                            <input type="checkbox" id="ignore_css" name="ignore_css">
                            <label for="ignore_css">use custom font</label>
                            <div class="center-box">
                                <select id="font_family" disabled="">
                                    <option value="verdana, trebuchet, droid sans serif, sans, sans-serif"> sans </option>
                                    <option value="georgia, times new roman, droid serif, serif"> serif </option>
                                    <option value="monospace"> monospace </option>
                                </select>
                                at <input type="number" id="font_size" value="100" min="50" max="150" disabled=""> %
                            </div>
                            <div id="font_example" class="user font_example">
                                <div>
                                Et nos esse veri viri scire volemus
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset class="settings-container" name="colour-settings">
                        <legend>colors</legend>
                        <fieldset>
                            <legend>normal</legend>
                            <div class="control-group">
                                <input type="checkbox" id="use_custom_colors" name="use_custom_colors">
                                <label for="use_custom_colors">
                                    Use custom colors 
                                </label>
                                <div class="center-box">
                                <input type="color" id="day_color" value="#0a0a0a">
                                on
                                <input type="color" id="day_background" value="#f0f0f0">
                                </div>
                                <div class="day font_example">
                                    <div>
                                        Et nos esse veri viri scire volemus
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                        <fieldset>
                            <legend>night</legend>
                            <div class="control-group">
                                <div class="center-box">
                                <input type="color" id="night_color" value="#3a516b">
                                on
                                <input type="color" id="night_background" value="#000000">
                                </div>
                                <div class="night font_example">
                                    <div>
                                        Et nos esse veri viri scire volemus
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </fieldset>
                    <fieldset class="settings-container" name="display-settings">
                        <legend>display</legend>
                        <fieldset>
                            <legend>page width</legend>
                            <div class="control-group center-box">
                                maximum <input type="number" id="page_width" value="72" min="25" max="200"> characters
                            </div>
                        </fieldset>
                        <div class="control-group">
                            <input type="checkbox" id="sidebarReflow" name="sidebarReflow">
                            <label for="sidebarReflow">
                                <?php p($l->t("reflow text when sidebars are open.")); ?>
                            </label>
                        </div>
                        <div class="control-group">
                            <input type="checkbox" id="touch_nav" name="touch_nav">
                            <label for="touch_nav" class="tooltip">
                                <?php p($l->t("disable extra-wide page turn areas")); ?> 
                                <span>
                                    <?php p($l->t("the extra-wide page turn areas as used by default on touch-screen devices interfere with the ability to select links in ebooks. when this option is enabled, the page-turn area is always outside the ebook margins so links are reachable.")); ?>
                                </span>
                            </label>
                        </div>
                    </fieldset>
                </div>
            </div>

            <!-- /sidebar -->

            <!-- main -->

            <div id="main">

                <!-- titlebar -->

                <div id="titlebar">
                    <div id="opener" class="pull-left">
                        <a id="slider" class="icon-menu">
                            <?php p($l->t("menu")); ?>
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
                    <div id="title-controls" class="pull-right">
                        <a id="bookmark" class="icon-bookmark-empty">
                            <?php p($l->t("bookmark")); ?>
                        </a>
                        <a id="setting" class="icon-cog">
                            <?php p($l->t("settings")); ?>
                        </a>
                        <a id="fullscreen" class="icon-resize-full">
                            <?php p($l->t("fullscreen")); ?>
                        </a>
                        <a id="close" class="icon-cancel-circled2" style="display:none">
                            <?php p($l->t("close")); ?>
                        </a>
                    </div>
                </div>

                <!-- /titlebar -->

                <!-- divider -->

                <div id="divider">
                </div>

                <!-- /divider -->

                <!-- navigation + viewer -->

                <div id="prev" class="arrow noday nonight">
                    <div class="noday nonight">
                        ‹
                    </div>
                </div>
                <div id="viewer">
                </div>
                <div id="next" class="arrow noday nonight">
                    <div class="noday nonight">
                        ›
                    </div>
                </div>
                <div id="loader" class="noday nonight">
                    <img src="img/loading.gif">
                </div>

                <!-- /navigation + viewer -->

            </div>

            <!-- /main -->

            <div class="overlay noday nonight">
            </div>
        </div>
    </body>
</html>
