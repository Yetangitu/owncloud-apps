PDFJS.reader.BookmarksController = function() {

	var reader = this,
	    book = this.book,
        annotations = reader.settings.annotations;

	var $bookmarks = $("#bookmarksView"),
        $list = $bookmarks.find("#bookmarks"),
        $bookmark = $("#bookmark");
	
	var show = function() {
        $bookmarks.addClass('open');
	};

	var hide = function() {
        $bookmarks.removeClass('open');
	};

    var addBookmarkItem = function (bookmark) {
        $list.append(reader.NotesController.createItem(bookmark));
    };

    for (var bookmark in annotations) {
        if (annotations.hasOwnProperty(bookmark) && (annotations[bookmark].type === "bookmark"))
            addBookmarkItem(annotations[bookmark]);
	};
	
	this.on("reader:bookmarkcreated", function (bookmark) {
        addBookmarkItem(bookmark);
	});
	
	this.on("reader:bookmarkremoved", function (id) {
		var $item = $("#"+id),
            cfi = reader.book.getCurrentLocationCfi(),
            cfi_id = reader.cfiToId(cfi);

		$item.remove();

        if(cfi_id === id) {
            $bookmark
                .removeClass("icon-turned_in")
                .addClass("icon-turned_in_not");
        }
	});

    this.on("reader:gotobookmark", function (bookmark) {
        if (bookmark && bookmark.value)
            book.gotoCfi(bookmark.value);
    });

	return {
		"show" : show,
		"hide" : hide
	};
};
