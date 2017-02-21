function disableStyles(doc, disable) {
	for ( var i=0; i<doc.styleSheets.length; i++) {
		void(doc.styleSheets.item(i).disabled=disable);
	}
}

function addStyleSheet() {
	var style = document.createElement("style");
	// WebKit hack :(
	style.appendChild(document.createTextNode(""));
	document.head.appendChild(style);
	return style.sheet;
}

function getCSSRule(sheet, selector, del) {
        lcSelector = selector.toLowerCase();
        for  (var i=0; i<sheet.cssRules.length; i++) {
		if (sheet.cssRules.item(i).selectorText.toLowerCase() == lcSelector) {
			if (del) {
				sheet.deleteRule(i);
				return null;
			} else {
				return sheet.cssRules.item(i);
			}
		}
	}
	return null;
}

function addCSSRule(sheet, selector, rules, index) {
    if (index === undefined) {
        index = 0;
    }

	if("insertRule" in sheet) {
		sheet.insertRule(selector + "{" + rules + "}", index);
	}
	else if("addRule" in sheet) {
		sheet.addRule(selector, rules, index);
	}
}

function delCSSRule(sheet, selector) {
	getCSSRule(sheet, selector, true);
}

function getUrlParameter(param){
	var pattern = new RegExp('[?&]'+param+'((=([^&]*))|(?=(&|$)))','i');
	var m = window.location.search.match(pattern);
	return m && ( typeof(m[3])==='undefined' ? '' : m[3] );
}

function renderEpub(file) {


    // some defaults
    EPUBJS.session = {};
    EPUBJS.session.version = $('.session').data('version');
    /*
    EPUBJS.session.filename = filename;
    EPUBJS.session.title = filename.replace(/\.[^/.]+$/, '');
    EPUBJS.session.format = filename.toLowerCase().match(re_file_ext)[1];
    */
    EPUBJS.session.metadata = $('.session').data('metadata') || {};
    EPUBJS.session.fileId = $('.session').data('fileid') || "";
    EPUBJS.session.scope = $('.session').data('scope') || "";
    EPUBJS.session.cursor = $('.session').data('cursor') || {};
    EPUBJS.session.defaults = $('.session').data('defaults') || {};
    EPUBJS.session.preferences = $('.session').data('preferences') || {};
    EPUBJS.session.defaults = $('.session').data('defaults') || {};
    EPUBJS.basePath = $('.session').data('basepath');
    EPUBJS.downloadLink = $('.session').data('downloadlink');


	// some parameters... 
	EPUBJS.filePath = "vendor/epubjs/";
	EPUBJS.cssPath = "css/";

    // user-configurable options
    EPUBJS.options = {};

    // user-configurable styles
    EPUBJS.styles = {};

	var reader = ePubReader(file, { contained: true });

	// touch-enabled devices...
	$('#touch_nav').prop('checked', !('ontouchstart' in document.documentElement));
	if (!($('#touch_nav').prop('checked'))) {
		$("#next").addClass("touch_nav");
		$("#prev").addClass("touch_nav");
	}

	// idevices...
	if (navigator.userAgent.match(/(iPad|iPhone|iPod)/g)) {
		$('head').append($('<script type="text/javascript" />')
            .attr('src', document.getElementsByTagName("base").item(0).href + 'vendor/bgrins/spectrum.js' + "?v=" + $('.session').data('version'))
            .attr('nonce', $('.session').data('nonce')));
		$('head').append($('<link rel="stylesheet" type="text/css" />')
            .attr('href', document.getElementsByTagName("base").item(0).href + 'vendor/bgrins/spectrum.css' + "?v=" + $('.session').data('version')));
	}

	// IE < 11
	if (navigator.userAgent.indexOf("MSIE") != -1) {
		EPUBJS.Hooks.register("beforeChapterDisplay").wgxpath = function(callback, renderer){
			wgxpath.install(renderer.render.window);
			if(callback)
				callback();
		};
		wgxpath.install(window);
	}


    /* user style settings */

	EPUBJS.userSheet = addStyleSheet();

    // construct a custom mode

	function modeConfig(mode) {


        var rule = "",
            undo_rule = "",
            selector = "." + mode.classname + " *",
            annulator = ".no" + mode.classname + " *";

		delCSSRule(EPUBJS.userSheet, selector);

        for (var clause in mode.rules) {
            rule += clause + ": " + mode.rules[clause] + " !important;";
            undo_rule += clause + ": initial !important;";
        }

        addCSSRule(EPUBJS.userSheet, selector, rule, 0);
        addCSSRule(EPUBJS.userSheet, annulator, undo_rule, 0);

        if (mode.extra) {
            addCSSRule(EPUBJS.userSheet, mode.extra[0], mode.extra[1], 0);
        }
	};


	// just switching in the "day" classname works on some browsers but not on others, hence the trickery with
	// setStyle/removeStyle...
    //
    // call this with 'style' set to enable a custom style (or change to a different one),
    // call this without parameters to disable custom styles

    function toggleCustom (style) {

        if (style) {
            if (EPUBJS.styles.active) {
                toggleCustom();
            }

            EPUBJS.styles.active = style;

            $("#outerContainer").addClass(EPUBJS.styles.active.classname);
            // and, just in case...
            $("#outerContainer").removeClass("night");
            EPUBJS.nightMode = false;
            for (var clause in EPUBJS.styles.active.rules) {
                reader.book.setStyle(clause, EPUBJS.styles.active.rules[clause]);
            }
        } else {
            if (EPUBJS.styles.active) {
                $("#outerContainer").removeClass(EPUBJS.styles.active.classname);
                for (var clause in EPUBJS.styles.active.rules) {
                    reader.book.removeStyle(clause);
                }

                delete EPUBJS.styles.active;
            }
        }
    };

    // night mode is not a normal custom style. It can be
    // applied at the same time as custom styles (which it overrules).
    // Custom styles will be restored when night mode is disabled
    function toggleNight () {

        if (EPUBJS.nightMode) {

            EPUBJS.nightMode = false;

            EPUBJS.styles.active = EPUBJS.styles.nightMode;
            toggleCustom();

            if (EPUBJS.styles.inactive) {
                toggleCustom (EPUBJS.styles.inactive);
                delete EPUBJS.styles.inactive;
            }
        } else {

            EPUBJS.nightMode = true;

            if (EPUBJS.styles.active) {
                EPUBJS.styles.inactive = EPUBJS.styles.active;
                toggleCustom();
            }
            for (var clause in EPUBJS.styles.nightMode.rules) {
                reader.book.setStyle(clause, EPUBJS.styles.nightMode.rules[clause]);
            }

            $("#outerContainer").addClass(EPUBJS.styles.nightMode.classname);
        }
    };

    // dayMode (custom colours)

    EPUBJS.styles.dayMode = {};
    EPUBJS.styles.dayMode.rules = {};
    EPUBJS.styles.dayMode.classname = "day";
    EPUBJS.styles.dayMode.rules.color = $('#day_color').val();
    EPUBJS.styles.dayMode.rules.background = $('#day_background').val();

    // nightMode

    EPUBJS.styles.nightMode = {};
    EPUBJS.styles.nightMode.rules = {};
    EPUBJS.styles.nightMode.classname = "night";
    EPUBJS.styles.nightMode.rules.color = $('#night_color').val();
    EPUBJS.styles.nightMode.rules.background = $('#night_background').val();

    modeConfig(EPUBJS.styles.dayMode);
    modeConfig(EPUBJS.styles.nightMode);

	$('#day_background').on('change', function() {
		EPUBJS.styles.dayMode.rules.background = $('#day_background').val();
		modeConfig(EPUBJS.styles.dayMode);
	});

	$('#day_color').on('change', function() {
		EPUBJS.styles.dayMode.rules.color = $('#day_color').val();
		modeConfig(EPUBJS.styles.dayMode);
	});

	// nightMode

	EPUBJS.nightMode = false;

	$('#night_background').on('change', function() {
		EPUBJS.styles.nightMode.rules.background = $('#night_background').val();
		modeConfig(EPUBJS.styles.nightMode);
	});

	$('#night_color').on('change', function() {
		EPUBJS.styles.nightMode.rules.color = $('#night_color').val();
		modeConfig(EPUBJS.styles.nightMode);
	});

	// enable day mode switch
	$('#use_custom_colors').on('change', function () {
        console.log("click!");
        if ($(this).prop('checked')) {
            toggleCustom(EPUBJS.styles.dayMode);
        } else {
            toggleCustom();
        }
    });

    // enable night mode switch

	$('#metainfo').on('click', toggleNight);

	// extra-wide page turn area?

	$('#touch_nav').on('change', function() {
		if ($('#touch_nav').prop('checked')) {
			$("#prev").removeClass("touch_nav");
			$("#next").removeClass("touch_nav");
		} else {
			$("#prev").addClass("touch_nav");
			$("#next").addClass("touch_nav");
		}
	});

    // page width
    $("#page_width").on("change", function () {
        EPUBJS.options.page_width = $(this).val();
        $("#viewer").css("max-width", EPUBJS.options.page_width + "em");
    });
		
	// user-defined font
	EPUBJS.ignore_css = false;
	EPUBJS.bookFrame = null;
	EPUBJS.options.font_family = $('#font_family').val();
	EPUBJS.options.font_size = $('#font_size').val() + '%';

    $('#font_example').css('font-size', EPUBJS.options.font_size);
    $('#font_example').css('font-family', EPUBJS.options.font_family);

	$('#ignore_css').on('click', function() {
		EPUBJS.bookFrame = document.getElementsByTagName('iframe')[0].contentDocument;
		if ($('#ignore_css').prop('checked')) {
			$('#font_family').prop('disabled',false);
			$('#font_size').prop('disabled',false);
			EPUBJS.ignore_css = true;
			reader.book.setStyle('font-size', EPUBJS.options.font_size);
			reader.book.setStyle('font-family', EPUBJS.options.font_family);
		} else {
			$('#font_family').prop('disabled',true);
			$('#font_size').prop('disabled',true);
			EPUBJS.ignore_css = false;
			reader.book.removeStyle('font-size');
			reader.book.removeStyle('font-family');
		}
		disableStyles(EPUBJS.bookFrame, EPUBJS.ignore_css);
;
	});


	$('#font_size').on('change', function() {
		EPUBJS.options.font_size = $(this).val() + '%';
		$('#font_example').css('font-size', EPUBJS.options.font_size);
		reader.book.setStyle('font-size', EPUBJS.options.font_size);
	});
	$('#font_family').on('change', function() {
		EPUBJS.options.font_family = $(this).val();
		$('#font_example').css('font-family', EPUBJS.options.font_family);
		reader.book.setStyle('font-family', EPUBJS.options.font_family);
	});

    // only enable close button when launched in an iframe
	if (parent !== window) {
		$('#close').show();
		$('#close').on('click', function() { reader.book.destroy(); parent.OCA.Files_Reader.Plugin.hide(); });
	}

    // connect event handlers
    //
    //reader.book.ready.all.then(function () {
    //    reader.book.on('renderer:locationChanged', function(location){
    //        console.log(location);
    //    });
    //});
}

function renderCbr(file) {
	CBRJS.filePath = "vendor/cbrjs/";

	var reader = cbReader(file, { contained: true });

	if (parent !== window) {
		$('.close').removeClass('hide');
		$('.close').on('click', function() { parent.OCA.Files_Reader.Plugin.hide(); });
	}

}

document.onreadystatechange = function () {  
	if (document.readyState == "complete") {
		var type=decodeURIComponent(getUrlParameter('type'));
		var file=decodeURIComponent(getUrlParameter('file'));

		switch (type) {
			case 'application/epub+zip':
				renderEpub(file);
				break;
			case 'application/x-cbr':
				renderCbr(file);
				break;
			default:
				console.log(type + ' is not supported by Reader');
		}
	}
};

