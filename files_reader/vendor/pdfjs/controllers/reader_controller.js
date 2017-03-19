PDFJS.reader.ReaderController = function(book) {
    var $main = $("#main"),
        $divider = $("#divider"),
        $loader = $("#loader"),
        $next = $("#next"),
        $prev = $("#prev"),
        $sidebarReflow = $('#sidebarReflow'),
        $metainfo = $("#metainfo"),
        $use_custom_colors = $("#use_custom_colors"),
        $container = $("#container"),
        $fullscreen = $("#fullscreen"),
        $bookmark = $("#bookmark"),
        $note = $("#note");

    var reader = this,
        book = this.book,
        settings = reader.settings;

    var slideIn = function() {
        if (reader.viewerResized) {
            var currentPosition = book.getCurrentLocationCfi();
            reader.viewerResized = false;
            $main.removeClass('single');
            $main.one("transitionend", function(){
                book.gotoCfi(currentPosition);
            });
        }
    };

    var slideOut = function() {
        var currentPosition = book.getCurrentLocationCfi();
        reader.viewerResized = true;
        $main.addClass('single');
        $main.one("transitionend", function(){
            book.gotoCfi(currentPosition);
        });
    };

    var showLoader = function() {
        $loader.show();
        hideDivider();
    };

    var hideLoader = function() {
        $loader.hide();

        //-- If the book is using spreads, show the divider
        // if(book.settings.spreads) {
            // 	showDivider();
            // }
    };

    var showDivider = function() {
        $divider.addClass("show");
    };

    var hideDivider = function() {
        $divider.removeClass("show");
    };

    var keylock = false;

    var showActive = function (obj) {
        keylock = true;
        obj.addClass("active");	
        setTimeout(function () {
            keylock = false;
            obj.removeClass("active");
        }, 100);
    };

    var keyCommands = function(e) {

        var page_no = false;

        switch (settings.keyboard[e.keyCode]) {
            case 'previous':
                $prev.click();
                break;
            case 'next':
                $next.click();
                break;
            case 'first':
                page_no = 1;
                break;
            case 'last':
                // TODO
                break;
            case 'annotate':
                $note.click();
                break;
            case 'bookmark':
                $bookmark.click();
                break;
            case 'reflow':
                $sidebarReflow.click();
                break;
            case 'toggleSidebar':
                reader.SidebarController.toggle();
                break;
            case 'closeSidebar':
                reader.SidebarController.hide();
                break;
            case 'toggleFullscreen':
                $fullscreen.click();
                break;
            case 'toggleNight':
                $metainfo.click();
                break;
            case 'toggleDay':
                $use_custom_colors.click();
                break;
            default:
                console.log("unsupported keyCode: " + e.keyCode);
        }

        if (page_no) {

            // TODO
        }
    }

    document.addEventListener('keydown', keyCommands, false);

    $next.on("click", function(e){

        //if(book.metadata.direction === "rtl") {
        //    reader.prevPage();
        //} else {
            reader.nextPage();
        //}

        showActive($next);

        e.preventDefault();
    });

    $prev.on("click", function(e){

        //if(book.metadata.direction === "rtl") {
        //    reader.nextPage();
        //} else {
            reader.prevPage();
        //}

        showActive($prev);

        e.preventDefault();
    });

    /*
    book.on("renderer:spreads", function(bool){
        if(bool) {
            showDivider();
        } else {
            hideDivider();
        }
    });
    */

    // book.on("book:atStart", function(){
        // 	$prev.addClass("disabled");
        // });
    // 
    // book.on("book:atEnd", function(){
        // 	$next.addClass("disabled");	
        // });

    return {
        "slideOut" : slideOut,
        "slideIn"  : slideIn,
        "showLoader" : showLoader,
        "hideLoader" : hideLoader,
        "showDivider" : showDivider,
        "hideDivider" : hideDivider,
        "keyCommands" : keyCommands
    };
};
