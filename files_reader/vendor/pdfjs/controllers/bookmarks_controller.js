PDFJS.reader.BookmarksController = function() {

	var reader = this,
        eventBus = this.eventBus,
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
        reader.settings.session.setBookmark(bookmark.id, bookmark.anchor, bookmark.type, bookmark);
    };

    var addBookmark = function (pageNum) {
        var bookmark = new reader.Annotation(
            "bookmark",
            pageNum,
            null,
            pageToId(pageNum)
        );

        addBookmarkItem(bookmark);
    };

    var removeBookmark = function (pageNum) {
        var id = pageToId(pageNum);
        console.log("ID", id);

        if (isBookmarked(id)) {
            delete reader.settings.annotations[id];
            reader.settings.session.deleteBookmark(id);
            if (id === pageToId(reader.settings.currentPage)) {
                $bookmark
                    .removeClass("icon-turned_in")
                    .addClass("icon-turned_in_not");
            }
        }
    };

    eventBus.on('bookmarkremoved', function removeBookmark1(e) {
        var id = e.id,
            $item = $("#"+id);

        $item.remove();

        if (id === pageToId(reader.settings.currentPage)) {
            $bookmark
                .removeClass("icon-turned_in")
                .addClass("icon-turned_in_not");
        }
    });

    var pageToId = function (pageNum) {
        return "page_" + pageNum;
    };

    var isBookmarked = function (pageNum) {
        return (reader.settings.annotations[pageToId(pageNum)] !== undefined);
    };

    for (var bookmark in annotations) {
        if (annotations.hasOwnProperty(bookmark) && (annotations[bookmark].type === "bookmark"))
            addBookmarkItem(annotations[bookmark]);
	};

	return {
		"show" : show,
		"hide" : hide,
        "addItem" : addBookmarkItem,
        "addBookmark" : addBookmark,
        "removeBookmark" : removeBookmark,
        "pageToId" : pageToId,
        "isBookmarked" : isBookmarked
	};
};
