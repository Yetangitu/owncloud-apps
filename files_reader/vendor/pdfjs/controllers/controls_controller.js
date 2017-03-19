PDFJS.reader.ControlsController = function(book) {
    var reader = this;

    var $store = $("#store"),
        $fullscreen = $("#fullscreen"),
        $fullscreenicon = $("#fullscreenicon"),
        $cancelfullscreenicon = $("#cancelfullscreenicon"),
        $slider = $("#slider"),
        $main = $("#main"),
        $sidebar = $("#sidebar"),
        $settings = $("#setting"),
        $bookmark = $("#bookmark"),
        $note = $("#note");

    var goOnline = function() {
        reader.offline = false;
        // $store.attr("src", $icon.data("save"));
    };

    var goOffline = function() {
        reader.offline = true;
        // $store.attr("src", $icon.data("saved"));
    };

    var fullscreen = false;

    $slider.on("click", function () {
        if(reader.sidebarOpen) {
            reader.SidebarController.hide();
            //$slider.addClass("icon-menu");
            //$slider.removeClass("icon-right2");
        } else {
            reader.SidebarController.show();
            //$slider.addClass("icon-right2");
            //$slider.removeClass("icon-menu");
        }
    });

    if(typeof screenfull !== 'undefined') {
        $fullscreen.on("click", function() {
            screenfull.toggle($('#container')[0]);
        });
        if(screenfull.raw) {
            document.addEventListener(screenfull.raw.fullscreenchange, function() {
                fullscreen = screenfull.isFullscreen;
                if(fullscreen) {
                    $fullscreen
                        .addClass("icon-fullscreen_exit")
                        .removeClass("icon-fullscreen");
                } else {
                    $fullscreen
                        .addClass("icon-fullscreen")
                        .removeClass("icon-fullscreen_exit");
                }
            });
        }
    }

    $settings.on("click", function() {
        reader.SettingsController.show();
    });

    $note.on("click", function() {
        reader.SidebarController.changePanelTo("Notes");
    });

    $bookmark.on("click", function() {
        var cfi = reader.book.getCurrentLocationCfi();

        if(!(reader.isBookmarked(cfi))) { //-- Add bookmark
            reader.addBookmark(cfi);
            $bookmark
                .addClass("icon-turned_in")
                .removeClass("icon-turned_in_not");
        } else { //-- Remove Bookmark
            reader.removeBookmark(cfi);
            $bookmark
                .removeClass("icon-turned_in")
                .addClass("icon-turned_in_not");
        }

    });

    /*
    book.on('renderer:locationChanged', function(cfi){
        var cfiFragment = "#" + cfi;
        // save current position (cursor)
        reader.settings.session.setCursor(cfi);
        //-- Check if bookmarked
        if(!(reader.isBookmarked(cfi))) { //-- Not bookmarked
            $bookmark
                .removeClass("icon-turned_in")
                .addClass("icon-turned_in_not");
        } else { //-- Bookmarked
            $bookmark
                .addClass("icon-turned_in")
                .removeClass("icon-turned_in_not");
        }

        reader.currentLocationCfi = cfi;

        // Update the History Location
        if(reader.settings.history &&
            window.location.hash != cfiFragment) {
                // Add CFI fragment to the history
                history.pushState({}, '', cfiFragment);
            }
    });

    book.on('book:pageChanged', function(location){
        console.log("page", location.page, location.percentage)
    });
    */

    return {

    };
};
