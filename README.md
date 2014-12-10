owncloud-apps
=============

This repo contains a few apps I made for Owncloud, mostly related to reading/publishing books and other digital publications.

Install
-------

There are two ways to install apps from this repo:

 * pull the repo and link/copy the desired apps to your Owncloud apps directory
 * get tarballs (from dist/name_of_app-version.tar.gz) and extract them in your Owncloud apps directory

The first method gives you the latest, greatest and potentially buggiest versions. The second is therefore preferred for those who care about the stability of their Owncloud instance.

files_opds
----------

The OPDS app enables Owncloud users to publish a subtree of their personal filesystem as an OPDS feed. Since Owncloud currently has limited to no support for metadata, the OPDS entries are rather sparse: only title (as in 'filename'), modification time and content links are provided. It uses Owncloud icons as cover art, this should make it possible to serve thumbnails as well (not tested as Owncloud currently can not create thumbnails for epub files, the which I happen to use for my personal library).

The root feed links to a hierarchical navigation feed mirroring the directory structure as well as a 'personal bookshelf' containing all downloaded books in order (most recent download first). This 'personal bookshelf' will be empty (0 books) at first. Use the 'Browse catalog' link to download a book and it'll appear on the 'personal bookshelf'. Download another, and it will appear above the one you previously downloaded. This makes it possible to get at books you are in the process of reading from different devices, or to easily re-visit a book you downloaded earlier.

Once Owncloud starts supporting metadata in a more flexible way, this can be extended to include more faceted feeds (by tag, by author, by whatever...). For now, it works, albeit in a rather spartan fashion.

In the personal settings page there are options to enable/disable the feed (it is disabled by default), set the feed root directory, enter a list of extensions to which the feed should be limited (by default it publishes all files descending from the feed root) and clear the personal bookshelf.

I tested the OPDS feed using FBReader and CoolReader with positive results. The feed can be accessed through Gecko-based browsers as well.

As it stands now, this is all the app does. It does not offer any specific way to manage publications, other than designating a root directory to serve them from. Once Owncloud starts supporting more extensive metadata I might reconsider the feature set. For now, I'd rather keep it simple as I don't fancy putting a lot of work (or CPU cycles) into managing my rather extensive collection of documents based on an ephemeral data store.

The OPDS feed is disabled when the app is installed, enable it in the personal settings page under the header 'OPDS'.

To connect to the OPDS feed, point your OPDS client at the app URL:

     https://example.com/path/to/owncloud/index.php/apps/files_opds/

If all goes well, the client should ask for a username and password - enter your Owncloud credentials here (and make sure you use HTTPS!).

files_reader
------------

Reader is an ebook reader based on a pure javascript epub renderer. It only works for books formatted according to the epub standard.

Using the futurepress epub.js renderer it provides near-native looks, especially when used full-screen. Turn pages by pressing the left/right hand side of the screen/window or using the cursor keys (if you have those), use the sidebar to browse through chapters or bookmarks and add annotations.

Reader has a night mode (toggled by clicking or pressing the book title/author on top of the viewer) to read in the dark without waking up the neighbours. This is obviously most effective when used full-screen. The colours used for night mode are configurable in the Settings dialog.

Also in Settings you'll find the option to use ignore any internal formatting in the book by forcing a given font style and size.

Reader is available at the App repo: https://apps.owncloud.com/content/show.php/Reader+(ebook+reader)
