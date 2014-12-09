<html dir="ltr">
   <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
      <meta name="apple-mobile-web-app-capable" content="yes">
      <base href="<?php print_unescaped($_['base']);?>">
      <title>
         <?php p($_['title']);?>
      </title>
      <link rel="shortcut icon" href="img/book.png">
      <link rel="stylesheet" href="css/normalize.css">
      <link rel="stylesheet" href="css/main.css">
      <link rel="stylesheet" href="css/popup.css">
      <link rel="stylesheet" href="css/tooltip.css">
      <script type="text/javascript" src="js/libs/jquery-2.1.0.min.js"> </script>
      <script type="text/javascript" src="js/libs/jquery.highlight.js"> </script>
      <script type="text/javascript" src="js/libs/screenfull.min.js"> </script>
      <script type="text/javascript" src="js/libs/typedarray.min.js"> </script>
      <script type="text/javascript" src="js/libs/blob.js"> </script>
      <script type="text/javascript" src="js/libs/zip.min.js"> </script>
      <script type="text/javascript" src="js/ready.js"> </script>
      <script type="text/javascript" src="js/epub.min.js"> </script>
      <script type="text/javascript" src="js/hooks.min.js"> </script>
      <script type="text/javascript" src="js/hooks/extensions/highlight.js"> </script>
      <script type="text/javascript" src="js/reader.js"> </script>
   </head>
   <body>
      <input type="hidden" id="dllink" value="<?php print_unescaped($_['dllink']);?>">
      <div id="outerContainer">
         <div id="sidebar">
            <div id="panels">
               <input id="searchBox" placeholder="not implemented yet" type="search" disabled="">
               <a id="show-Search" class="show_view icon-search" data-view="Search">
                  Search
               </a>
               <a id="show-Toc" class="show_view icon-list-1 active" data-view="Toc">
                  TOC
               </a>
               <a id="show-Bookmarks" class="show_view icon-bookmark" data-view="Bookmarks">
                  Bookmarks
               </a>
               <a id="show-Notes" class="show_view icon-edit" data-view="Notes">
                  Notes
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
                     Anchor
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
                     Menu
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
                     Bookmark
                  </a>
                  <a id="setting" class="icon-cog">
                     Settings
                  </a>
                  <a id="fullscreen" class="icon-resize-full">
                     Fullscreen
                  </a>
                  <a id="close" class="icon-cancel-circled2">
                     Close
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
                  Settings
               </h3>
               <div>
                  <p>
                     <input type="checkbox" id="ignore_css" name="ignore_css">
                     <label for="ignore_css">
                        Always use
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
                     font scaled to
                     <input type="number" id="fontSize" value="100" min="50" max="150" disabled="">
                     %
                  </p>
                  <div id="font_example" class="user">
                     Et nos esse veri viri scire volemus
                  </div>
                  <p>
                     <input type="checkbox" id="sidebarReflow" name="sidebarReflow">
                     <label for="sidebarReflow">
                        Reflow text when sidebars are open.
                     </label>
                  </p>
                  <p>
                     Night mode background
                     <input type="color" id="nightModeBackground" value="#000000">
                     and text
                     <input type="color" id="nightModeColor" value="#3A516B">
                     colour
                  </p>
                  <div id="nightModeExample" class="night">
                     <div>
                        Et nos esse veri viri scire volemus
                     </div>
                  </div>
                  <p>
                     <input type="checkbox" id="touch_nav" name="touch_nav">
                     <label for="touch_nav" class="tooltip">
                        Disable extra-wide page turn areas 
								<span>
									The extra-wide page turn areas as used by default on touch-screen devices interfere with the ability to select links in ebooks. When this option is enabled, the page-turn area is always outside the ebook margins so links are reachable.
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
