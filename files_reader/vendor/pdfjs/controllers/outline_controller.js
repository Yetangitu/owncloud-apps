PDFJS.reader.OutlineController = function(_outline) {

	var reader = this,
        book = this.book,
        outline = _outline || [];

	var outlineView = document.getElementById("outlineView"),
        baseUrl = location.href.split('#')[0],
        lastToggleIsShow;

    var DEFAULT_TITLE = '\u2013';

    var bindLink = function (element, item) {

		var destination = item.dest;

		if (item.url) {

			PDFJS.addLinkAttributes (element, {
				url: item.url,
				target: (item.newWindow
					? PDFJS.LinkTarget.BLANK
					: undefined),
			});

			return;
		} else {

			element.href = getDestinationHash(destination);
			element.onclick = function () {
				if (destination) {
					reader.navigateTo(destination);
				}
				
				return false;
			};
		}
	};

	var setStyles = function (element, item) {
	
        var styleStr = "";

        if (item.bold) {
            styleStr += 'font-weight: bold;';
        }

        if (item.italic) {
            styleStr += 'font-style: italic;';
        }

        if (styleStr) {
            element.setAttribute('style', styleStr);
        }
    };

	var getDestinationHash = function (destination) {

		var url = baseUrl || "",
			str;

		if (typeof destination === 'string') {

			url += "#"
				+ (parseInt(destination) === destination)
					? "nameddest="
					: ""
				+ escape(destination);

		} else if (destination instanceof Array) {

			url += "#"
				+ escape(JSON.stringify(destination));
		}

		return url;
	};

    var generateOutlineItems = function (outline, level) {

        var container = document.createElement("ul");

        if(!level) level = 1;

        outline.forEach(function (chapter) {
            var listitem = document.createElement("li"),
                link = document.createElement("a"),
                toggle = document.createElement("a"),
                subitems;

            listitem.id = "outline-"+chapter.dest;
            listitem.classList.add('list_item');

            link.textContent = PDFJS.removeNullCharacters(chapter.title) || DEFAULT_TITLE;
            bindLink(link, chapter);
            setStyles(link, chapter);
            link.classList.add('outline_link');

            listitem.appendChild(link);

            if(chapter.items.length > 0) {
                level++;
                subitems = generateOutlineItems(chapter.items, level);
                toggle.classList.add('outline_toggle');
                listitem.insertBefore(toggle, link);
                listitem.appendChild(subitems);
            }

            container.appendChild(listitem);
        });

        return container;
    }

	var onShow = function() {
        outlineView.classList.add('open');
	};

	var onHide = function() {
        outlineView.classList.remove('open');
	};

	outlineView.appendChild(generateOutlineItems(outline));

	$(outlineView).find(".outline_toggle").on("click", function(event){
			var $el = $(this).parent('li'),
					open = $el.hasClass("openChapter");

			event.preventDefault();
			if(open){
				$el.removeClass("openChapter");
			} else {
				$el.addClass("openChapter");
			}
	});

	return {
		"show" : onShow,
		"hide" : onHide
	};
};
