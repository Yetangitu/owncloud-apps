EPUBJS.reader.plugins.SearchController = function () {
    var reader = this;
    var book = this.book;

    var $searchBox = $("#searchBox"),
		$clearBtn = $("#searchBox").next(),
        $searchResults = $("#searchResults"),
        $searchView = $("#searchView"),
        $body = $("#viewer iframe").contents().find('body');
        results = document.getElementById('searchResults');

    var onShow = function() {
        $searchView.addClass("open");
        //search();
    };

    var onHide = function() {
        highlight();
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

        runQuery(q, results);

    };

	$searchBox.on("keyup", function(e) {
		// Show the clear button if text input value is not empty
		$clearBtn.css("visibility", (this.value.length) ? "visible" : "hidden");

        // run search when Enter is pressed
        if (e.keyCode === 13) {
            e.preventDefault();
            search();
        }
	});

	$clearBtn.on("click", function() {
		$(this).css("visibility", "hidden");
		$searchBox.val("");
	});

    function clear () {

        $searchResults.empty();
        // book.off("renderer:chapterDisplayed");
        highlight();

        if (reader.SidebarController.getActivePanel() == "Search") {
            reader.SidebarController.changePanelTo("Toc");
        }
    };

    // perform search and build result list
    function runQuery(query, element) {

        return new Promise(function(resolve, reject) {

            var results = [];

            for (var i = 0; i < book.spine.length; i++) {
                var spineItem = book.spine[i];
                results.push(new Promise(function(resolve, reject) {
                    new Promise(function(resolve, reject) {
                        resolve(new EPUBJS.Chapter(spineItem, book.store, book.credentials));
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
                            // listitem.setAttribute("data-location", mergedResults[i].cfi);
                            listitem.id = "search-"+i;
                            link.href=mergedResults[i].cfi;
                            link.textContent = mergedResults[i].excerpt;
                            link.classList.add("toc_link");
                            link.addEventListener("click", function(e) {
                                e.preventDefault();
                                $searchResults.find(".list_item")
                                    .removeClass("currentChapter");
                                $(this).parent("li").addClass("currentChapter");

                                book.on("renderer:chapterDisplayed", function() {
                                    highlight(query);
                                });

                                book.gotoCfi(this.getAttribute("href"));
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

    highlight = function (query) {
        (query !== undefined) 
        ? $body.highlight(query, { element: 'span' })
        : $body.unhighlight();
    };

    return {
        "show": onShow,
        "hide": onHide,
        "search": search
    };
};
