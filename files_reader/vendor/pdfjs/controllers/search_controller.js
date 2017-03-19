PDFJS.reader.SearchController = function () {
    var reader = this,
        book = this.book,
        query = "";

    var $searchBox = $("#searchBox"),
        $clearBtn = $("#searchBox").next(),
        $clear_search = $("#clear_search"), 
        $searchResults = $("#searchResults"),
        $searchView = $("#searchView"),
        $body = $("#viewer iframe").contents().find('body'),
        $sidebar = $("#sidebar");

    var onShow = function() {
        $searchView.addClass("open");
        $searchBox.focus();
    };

    var onHide = function() {
        unhighlight();
        $searchView.removeClass("open");
    };

    var search = function(q) {
        if (q === undefined) {
            q = $searchBox.val();
        }

        if (q == '') {
            clear();
            return;
        }

        reader.SidebarController.changePanelTo("Search");

        $searchResults.empty();
        $searchResults.append("<li><p>Searching...</p></li>");

        reader.SearchController.query = q;

        runQuery(q, $searchResults[0]);

    };

    $searchBox.on("keydown", function(e) {
        // Show the clear button if text input value is not empty
        $clearBtn.css("visibility", (this.value.length) ? "visible" : "hidden");

        // run search when Enter is pressed
        if (e.keyCode === 13) {
            search();
        }

        e.stopPropagation();
    });

    $clearBtn.on("click", function() {
        $(this).css("visibility", "hidden");
        $searchBox.val("");
    });

    $clear_search.on("click", function () {
        unhighlight();
        $searchResults.empty();
    });

    var clear = function () {

        unhighlight();
        $searchResults.empty();

        if (reader.SidebarController.getActivePanel() == "Search") {
            reader.SidebarController.changePanelTo("Toc");
        }
    };

    var highlightQuery = function(e) {
        $("#viewer iframe").contents().find('body').highlight(reader.SearchController.query, { element: 'span' });
    };

    var unhighlight = function(e) {
        $body = $("#viewer iframe").contents().find('body');
        $body.unhighlight();
        book.off("renderer:chapterDisplayed", highlightQuery);
    };

    // perform search and build result list
    var runQuery = function(query, element) {

        return new Promise(function(resolve, reject) {

            var results = [];

            for (var i = 0; i < book.spine.length; i++) {
                var spineItem = book.spine[i];
                results.push(new Promise(function(resolve, reject) {
                    new Promise(function(resolve, reject) {
                        resolve(new PDFJS.Chapter(spineItem, book.store, book.credentials));
                    }).then(function(chapter) {
                        return new Promise(function(resolve, reject) {
                            chapter.load().then(function() {
                                resolve(chapter);
                            }).catch(reject);
                        });
                    }).then(function(chapter) {
                        return Promise.resolve(chapter.find(query));
                    }).then(function(result) {
                        resolve(result);
                    });
                }));
            }
            Promise.all(results).then(function(results) {
                return new Promise(function(resolve, reject) {
                    resolve(results);
                    var mergedResults = [].concat.apply([], results);
                    element.innerHTML = "";
                    for (var i = 0; i < mergedResults.length; i++) {
                        try {
                            var listitem = document.createElement("li");
                            var link = document.createElement("a");
                            listitem.classList.add("list_item");
                            listitem.id = "search-"+i;
                            link.href=mergedResults[i].cfi;
                            link.textContent = mergedResults[i].excerpt;
                            link.classList.add("toc_link");
                            link.addEventListener("click", function(e) {
                                e.preventDefault();
                                book.gotoCfi(this.getAttribute("href"));
                                $searchResults.find(".list_item")
                                    .removeClass("currentChapter");
                                $(this).parent("li").addClass("currentChapter");
                                $(this).data('query', query);
                                book.on("renderer:chapterDisplayed", highlightQuery);
                            });
                            listitem.appendChild(link);
                            element.appendChild(listitem);
                        } catch (e) {
                            console.warn(e);
                        }
                    }
                });
            });
        });
    };


    return {
        "show"  : onShow,
        "hide"  : onHide,
        "search": search,
        "query" : query,
        "clear" : clear,
        "unhighlight"   : unhighlight
    };
};
