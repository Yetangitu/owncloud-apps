var FindStates = {
    FIND_FOUND: 0,
    FIND_NOTFOUND: 1,
    FIND_WRAPPED: 2,
    FIND_PENDING: 3
};

var FIND_SCROLL_OFFSET_TOP = -50;
var FIND_SCROLL_OFFSET_LEFT = -400;

var CHARACTERS_TO_NORMALIZE = {
    '\u2018': '\'', // Left single quotation mark
    '\u2019': '\'', // Right single quotation mark
    '\u201A': '\'', // Single low-9 quotation mark
    '\u201B': '\'', // Single high-reversed-9 quotation mark
    '\u201C': '"', // Left double quotation mark
    '\u201D': '"', // Right double quotation mark
    '\u201E': '"', // Double low-9 quotation mark
    '\u201F': '"', // Double high-reversed-9 quotation mark
    '\u00BC': '1/4', // Vulgar fraction one quarter
    '\u00BD': '1/2', // Vulgar fraction one half
    '\u00BE': '3/4', // Vulgar fraction three quarters
};

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

    this.onUpdateResultsCount = null;
    this.onUpdateState = null;

    // Compile the regular expression for text normalization once.
    var replace = Object.keys(CHARACTERS_TO_NORMALIZE).join('');
    this.normalizationRegex = new RegExp('[' + replace + ']', 'g');

    var reset = function () {
        this.startedTextExtraction = false;
        this.extractTextPromises = [];
        this.pendingFindMatches = Object.create(null);
        this.active = false; // If active, find results will be highlighted.
        this.pageContents = []; // Stores the text for each page.
        this.pageMatches = [];
        this.pageMatchesLength = null;
        this.matchCount = 0;
        this.selected = { // Currently selected match.
            pageIdx: -1,
            matchIdx: -1
        };
        this.offset = { // Where the find algorithm currently is in the document.
            pageIdx: null,
            matchIdx: null
        };
        this.pagesToSearch = null;
        this.resumePageIdx = null;
        this.state = null;
        this.dirtyMatch = false;
        this.findTimeout = null;
    };

    reset();


    var normalize = function (text) {
        return text.replace(this.normalizationRegex, function (ch) {
            return CHARACTERS_TO_NORMALIZE[ch];
        });
    };

    // Helper for multiple search - fills matchesWithLength array
    // and takes into account cases when one search term
    // include another search term (for example, "tamed tame" or "this is").
    // Looking for intersecting terms in the 'matches' and
    // leave elements with a longer match-length.

    var _prepareMatches = function (
        matchesWithLength, matches, matchesLength) {

            function isSubTerm(matchesWithLength, currentIndex) {
                var currentElem, prevElem, nextElem;
                currentElem = matchesWithLength[currentIndex];
                nextElem = matchesWithLength[currentIndex + 1];
                // checking for cases like "TAMEd TAME"
                if (currentIndex < matchesWithLength.length - 1 &&
                    currentElem.match === nextElem.match) {
                        currentElem.skipped = true;
                        return true;
                    }
                // checking for cases like "thIS IS"
                for (var i = currentIndex - 1; i >= 0; i--) {
                    prevElem = matchesWithLength[i];
                    if (prevElem.skipped) {
                        continue;
                    }
                    if (prevElem.match + prevElem.matchLength < currentElem.match) {
                        break;
                    }
                    if (prevElem.match + prevElem.matchLength >=
                        currentElem.match + currentElem.matchLength) {
                            currentElem.skipped = true;
                            return true;
                        }
                }
                return false;
            }

            var i, len;
            // Sorting array of objects { match: <match>, matchLength: <matchLength> }
            // in increasing index first and then the lengths.
            matchesWithLength.sort(function(a, b) {
                return a.match === b.match ?
                    a.matchLength - b.matchLength : a.match - b.match;
            });
            for (i = 0, len = matchesWithLength.length; i < len; i++) {
                if (isSubTerm(matchesWithLength, i)) {
                    continue;
                }
                matches.push(matchesWithLength[i].match);
                matchesLength.push(matchesWithLength[i].matchLength);
            }
        };

    var calcFindPhraseMatch = function (
        query, pageIndex, pageContent) {
            var matches = [];
            var queryLen = query.length;
            var matchIdx = -queryLen;
            while (true) {
                matchIdx = pageContent.indexOf(query, matchIdx + queryLen);
                if (matchIdx === -1) {
                    break;
                }
                matches.push(matchIdx);
            }
            this.pageMatches[pageIndex] = matches;

        };

    var calcFindWordMatch = function (
        query, pageIndex, pageContent) {
            var matchesWithLength = [];
            // Divide the query into pieces and search for text on each piece.
            var queryArray = query.match(/\S+/g);
            var subquery, subqueryLen, matchIdx;
            for (var i = 0, len = queryArray.length; i < len; i++) {
                subquery = queryArray[i];
                subqueryLen = subquery.length;
                matchIdx = -subqueryLen;
                while (true) {
                    matchIdx = pageContent.indexOf(subquery, matchIdx + subqueryLen);
                    if (matchIdx === -1) {
                        break;
                    }
                    // Other searches do not, so we store the length.
                    matchesWithLength.push({
                        match: matchIdx,
                        matchLength: subqueryLen,
                        skipped: false
                    });
                }
            }
            // Prepare arrays for store the matches.
            if (!this.pageMatchesLength) {
                this.pageMatchesLength = [];
            }
            this.pageMatchesLength[pageIndex] = [];
            this.pageMatches[pageIndex] = [];
            // Sort matchesWithLength, clean up intersecting terms
            // and put the result into the two arrays.
            _prepareMatches(matchesWithLength, this.pageMatches[pageIndex],
                this.pageMatchesLength[pageIndex]);
        };

    var getSnippet = function (pageIndex, position) {

        var ellipse = '…',
            match_length = this.state.query.length,
            span = '<span class="search_match">',
            span_close = '</span>',
            limit = 160 + span.length + span_close.length,
            leader,
            trailer,
            context;

        leader = this.pageContents[pageIndex].substring(position - limit/2, position);
        leader = leader.slice(leader.indexOf(" "));
        trailer = this.pageContents[pageIndex].substring(position + match_length, position + limit/2 + match_length);
        query = this.pageContents[pageIndex].substring(position, position + match_length);

        context = ellipse + leader + span + query + span_close + trailer;

        return reader.ellipsize(context, context.length - 10);
    };

    var createItem = function (pageIndex, position) {

        var listitem = document.createElement("li"),
            link = document.createElement("a"),
            id = parseInt(pageIndex + 1) + ":" + position,
            item = {
                url: null,
                dest: null,
                bold: null,
                italic: null
            };

        // for now only the pageIndex is used
        item.dest = [pageIndex,position];

        //link.textContent = getSnippet(pageIndex, position);
        link.innerHTML = getSnippet(pageIndex, position);
        listitem.classList.add("list_item");
        listitem.id = "search-"+id;
        listitem.dataset.position = position;
        reader.bindLink(link, item);
        link.classList.add("search_link");
        listitem.appendChild(link);

        return listitem;
    };


    var calcFindMatch = function (pageIndex) {
        var pageContent = normalize(this.pageContents[pageIndex]);
        var query = normalize(this.state.query);
        var caseSensitive = this.state.caseSensitive;
        var phraseSearch = this.state.phraseSearch;
        var queryLen = query.length;

        if (queryLen === 0) {
            // Do nothing: the matches should be wiped out already.
            return;
        }

        if (!caseSensitive) {
            pageContent = pageContent.toLowerCase();
            query = query.toLowerCase();
        }

        if (phraseSearch) {
            calcFindPhraseMatch(query, pageIndex, pageContent);
        } else {
            calcFindWordMatch(query, pageIndex, pageContent);
        }
    };

    var extractText = function () {

        if (this.startedTextExtraction) {
            return;
        }
        this.startedTextExtraction = true;

        this.pageContents = [];
        var extractTextPromisesResolves = [];
        var numPages = reader.settings.numPages;
        for (var i = 0; i < numPages; i++) {
            this.extractTextPromises.push(new Promise(function (resolve) {
                extractTextPromisesResolves.push(resolve);
            }));
        }

        var self = this;
        function extractPageText(pageIndex) {
            reader.getPageTextContent(pageIndex).then(
                function textContentResolved(textContent) {
                    var textItems = textContent.items;
                    var str = [];

                    for (var i = 0, len = textItems.length; i < len; i++) {
                        str.push(textItems[i].str);
                    }

                    // Store the pageContent as a string.
                    self.pageContents.push(str.join(' ').replace(/\s\s+/g, ' '));

                    extractTextPromisesResolves[pageIndex](pageIndex);
                    if ((pageIndex + 1) < reader.settings.numPages) {
                        extractPageText(pageIndex + 1);
                    } 
                }
            );
        }
        extractPageText(0);
    };

    var executeCommand = function (cmd, state) {
        if (this.state === null || cmd !== 'findagain') {
            this.dirtyMatch = true;
        }
        this.state = state;
        updateUIState(FindStates.FIND_PENDING);

		console.log("execute command ", cmd, " with state ", state);

        reader.firstPagePromise.then(function() {
            extractText();

            clearTimeout(this.findTimeout);
            if (cmd === 'find') {
                // Only trigger the find action after 250ms of silence.
                //this.findTimeout = setTimeout(nextMatch.bind(this), 250);
                generateMatchList();
            } else {
                nextMatch();
            }
        }.bind(this));
    };

    var updatePage = function (index) {

        if (this.selected.pageIdx === index) {
            // If the page is selected, scroll the page into view, which triggers
            // rendering the page, which adds the textLayer. Once the textLayer is
            // build, it will scroll onto the selected match.
            reader.settings.currentPage = index + 1;
        }

        //var page = this.pdfViewer.getPageView(index);
        //if (page.textLayer) {
            //    page.textLayer.updateMatches();
            //}
    };

    var generateMatchList = function () {

        var container = document.getElementById("searchResults"),
            numPages = reader.settings.numPages,
            self = this;

        for (var i = 0; i < numPages; i++) {
            //var placeholder = document.createElement("li");
            //placeholder.style.display = "none";
            //container.appendChild(placeholder);
            if (!(i in this.pendingFindMatches)) {
                this.pendingFindMatches[i] = true;
                this.extractTextPromises[i].then(function(pageIdx) {
                    delete self.pendingFindMatches[pageIdx];
                    calcFindMatch(pageIdx);
                    if (self.pageMatches[pageIdx].length > 0) {
                        reader.pageMatches[pageIdx] = self.pageMatches[pageIdx];
                        var fragment = document.createDocumentFragment();
                        var listitem = document.createElement("li");
                        listitem.textContent="page " + parseInt(pageIdx + 1);
                        listitem.classList.add("search_page_header");
                        fragment.appendChild(listitem);
                        self.pageMatches[pageIdx].forEach(function (match) {
                            fragment.appendChild(createItem(pageIdx, match));
                        });

                        container.appendChild(fragment);
                    }
                });
            }
        }
    };

    var nextMatch = function () {

        var previous = this.state.findPrevious;
        var currentPageIndex = reader.settings.currentPage - 1;
        var numPages = reader.settings.numPages;

        this.active = true;

        if (this.dirtyMatch) {
            // Need to recalculate the matches, reset everything.
            this.dirtyMatch = false;
            this.selected.pageIdx = this.selected.matchIdx = -1;
            this.offset.pageIdx = currentPageIndex;
            this.offset.matchIdx = null;
            this.hadMatch = false;
            this.resumePageIdx = null;
            this.pageMatches = [];
            this.matchCount = 0;
            this.pageMatchesLength = null;
            var self = this;

            for (var i = 0; i < numPages; i++) {
                // Wipe out any previous highlighted matches.
                updatePage(i);

                // As soon as the text is extracted start finding the matches.
                if (!(i in this.pendingFindMatches)) {
                    this.pendingFindMatches[i] = true;
                    this.extractTextPromises[i].then(function(pageIdx) {
                        delete self.pendingFindMatches[pageIdx];
                        calcFindMatch(pageIdx);
                    });
                }
            }
        }

        // If there's no query there's no point in searching.
        if (this.state.query === '') {
            updateUIState(FindStates.FIND_FOUND);
            return;
        }

        // If we're waiting on a page, we return since we can't do anything else.
        if (this.resumePageIdx) {
            return;
        }

        var offset = this.offset;
        // Keep track of how many pages we should maximally iterate through.
        this.pagesToSearch = numPages;
        // If there's already a matchIdx that means we are iterating through a
        // page's matches.
        if (offset.matchIdx !== null) {
            var numPageMatches = this.pageMatches[offset.pageIdx].length;
            if ((!previous && offset.matchIdx + 1 < numPageMatches) ||
                (previous && offset.matchIdx > 0)) {
                    // The simple case; we just have advance the matchIdx to select
                    // the next match on the page.
                    this.hadMatch = true;
                    offset.matchIdx = (previous ? offset.matchIdx - 1 :
                        offset.matchIdx + 1);
                    updateMatch(true);
                    return;
                }
            // We went beyond the current page's matches, so we advance to
            // the next page.
            advanceOffsetPage(previous);
        }
        // Start searching through the page.
        nextPageMatch();
    };

    var matchesReady = function (matches) {
        var offset = this.offset;
        var numMatches = matches.length;
        var previous = this.state.findPrevious;

        if (numMatches) {
            // There were matches for the page, so initialize the matchIdx.
            this.hadMatch = true;
            offset.matchIdx = (previous ? numMatches - 1 : 0);
            updateMatch(true);
            return true;
        }
        // No matches, so attempt to search the next page.
        advanceOffsetPage(previous);
        if (offset.wrapped) {
            offset.matchIdx = null;
            if (this.pagesToSearch < 0) {
                // No point in wrapping again, there were no matches.
                updateMatch(false);
                // while matches were not found, searching for a page
                // with matches should nevertheless halt.
                return true;
            }
        }
        // Matches were not found (and searching is not done).
        return false;
    };

    /**
        * The method is called back from the text layer when match presentation
        * is updated.
        * @param {number} pageIndex - page index.
        * @param {number} index - match index.
        * @param {Array} elements - text layer div elements array.
        * @param {number} beginIdx - start index of the div array for the match.
        */
        var updateMatchPosition = function (
            pageIndex, index, elements, beginIdx) {
                if (this.selected.matchIdx === index &&
                    this.selected.pageIdx === pageIndex) {
                        //var spot = {
                            //    top: FIND_SCROLL_OFFSET_TOP,
                            //    left: FIND_SCROLL_OFFSET_LEFT
                            //};
                        //scrollIntoView(elements[beginIdx], spot,
                            //        /* skipOverflowHiddenElements = */ true);
                    }
                console.log("would scroll into view here except for the fact that Reader is a non-scrolling reader...");
            };

    var nextPageMatch = function () {
        if (this.resumePageIdx !== null) {
            console.error('There can only be one pending page.');
        }
        do {
            var pageIdx = this.offset.pageIdx;
            var matches = this.pageMatches[pageIdx];
            if (!matches) {
                // The matches don't exist yet for processing by "matchesReady",
                // so set a resume point for when they do exist.
                this.resumePageIdx = pageIdx;
                break;
            }
        } while (!matchesReady(matches));
    };

    var advanceOffsetPage = function (previous) {
        var offset = this.offset;
        var numPages = this.extractTextPromises.length;
        offset.pageIdx = (previous ? offset.pageIdx - 1 : offset.pageIdx + 1);
        offset.matchIdx = null;

        this.pagesToSearch--;

        if (offset.pageIdx >= numPages || offset.pageIdx < 0) {
            offset.pageIdx = (previous ? numPages - 1 : 0);
            offset.wrapped = true;
        }
    };

    var updateMatch = function (found) {
        var state = FindStates.FIND_NOTFOUND;
        var wrapped = this.offset.wrapped;
        this.offset.wrapped = false;

        if (found) {
            var previousPage = this.selected.pageIdx;
            this.selected.pageIdx = this.offset.pageIdx;
            this.selected.matchIdx = this.offset.matchIdx;
            state = (wrapped ? FindStates.FIND_WRAPPED : FindStates.FIND_FOUND);
            // Update the currently selected page to wipe out any selected matches.
            if (previousPage !== -1 && previousPage !== this.selected.pageIdx) {
                updatePage(previousPage);
            }
        }

        updateUIState(state, this.state.findPrevious);
        if (this.selected.pageIdx !== -1) {
            updatePage(this.selected.pageIdx);
        }
    };

    var updateUIResultsCount = function () {
        if (this.onUpdateResultsCount) {
            onUpdateResultsCount(this.matchCount);
        }
    };

    var updateUIState = function (state, previous) {
        if (this.onUpdateState) {
            onUpdateState(state, previous, this.matchCount);
        }
    };


    var search = function(q) {
        if (q === undefined) {
            q = $searchBox.val();
        }

        if (q === '') {
            clear();
            return;
        }

        reader.SidebarController.changePanelTo("Search");

        reset();
        $searchResults.empty();

        this.query = q;

		executeCommand('find', {query: q});
        highlightQuery();
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
        reset();
        unhighlight();
        $searchResults.empty();
    });

    var clear = function () {

        reset();
        unhighlight();
        $searchResults.empty();

        if (reader.SidebarController.getActivePanel() == "Search") {
            reader.SidebarController.changePanelTo("Toc");
        }
    };

    var highlightQuery = function(e) {
        $("#text_left").contents().highlight(this.state.query, { element: 'span' });
        $("#text_right").contents().highlight(this.state.query, { element: 'span' });
    };

    var unhighlight = function(e) {
        $("#text_left").unhighlight();
        $("#text_right").unhighlight();
    };


    return {
        "show": onShow,
        "hide": onHide,
        "search": search,
        "executeCommand": executeCommand,
        "highlightQuery": highlightQuery,
        "unhighlight": unhighlight
    };
};
