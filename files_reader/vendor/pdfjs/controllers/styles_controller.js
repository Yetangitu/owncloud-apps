PDFJS.reader.StylesController = function (renderer) {

    var reader = this,
        book = this.book,
		settings = reader.settings,
        customStyles = reader.settings.customStyles,
        activeStyles = reader.settings.activeStyles,
        $viewer = $("#viewer"),
        $day_example = $('#day_example'),
        $night_example = $('#night_example'),
        $font_example = $('#font_example'),
        $page_width = $("#page_width"),
        $day_background = $('#day_background'),
        $day_color = $('#day_color'),
        $night_background = $('#night_background'),
        $night_color = $('#night_color'),
        $use_custom_colors = $('#use_custom_colors'),
        $nightshift = $('.nightshift'),
        $custom_font_family = $('#custom_font_family'),
        $font_family = $('#font_family'),
        $custom_font_size = $('#custom_font_size'),
        $font_size = $("#font_size"),
        $custom_font_weight = $('#custom_font_weight'),
        $font_weight = $("#font_weight"),
        $maximize_page = $('#maximize_page');

    // register hook to refresh styles on chapter change    
    renderer.registerHook("beforeChapterDisplay", this.refreshStyles.bind(this), true);

    this.addStyle("dayMode", "*", {
        color: $day_color.val(),
        background: $day_background.val()
    });
    
    this.addStyle("nightMode", "*", {
        color: $night_color.val(),
        background: $night_background.val()
    });
    
    this.addStyle("fontFamily", "*", {
        "font-family": $font_family.val()
    });
    
    this.addStyle("fontSize", "*", {
        "font-size": $font_size.val() + '%'
    });

    this.addStyle("fontWeight", "*", {
        "font-weight": $font_weight.val()
    });

    this.addStyle("pageWidth", "#viewer", {
        "max-width": $page_width.val() + 'em'
    });

    this.addStyle("maximizePage", "#viewer", {
        "margin": "auto",
        "width": "100%",
        "height": "95%",
        "top": "5%"
    });

    this.addStyle("appleBugs", "document, html, body, p, span, div", {
        "cursor": "pointer"
    });

    $day_example.css({
        'background': customStyles.dayMode.rules.background,
        'color': customStyles.dayMode.rules.color
    });

    $night_example.css({
        'background': customStyles.nightMode.rules.background,
        'color': customStyles.nightMode.rules.color
    });

    $font_example.css({
        'font-size': customStyles.fontSize.rules["font-size"],
        'font-family': customStyles.fontFamily.rules["font-family"],
        'font-weight': customStyles.fontWeight.rules["font-weight"]
    });

    $font_family.val(customStyles.fontFamily.rules["font-family"]);
    $font_size.val(parseInt(customStyles.fontSize.rules["font-size"]));
    $font_weight.val(customStyles.fontWeight.rules["font-weight"]);
    $page_width.val(parseInt(0 + parseInt(customStyles.pageWidth.rules["max-width"])));

    // fix click-bug in apple products
    if (navigator.userAgent.match(/(iPad|iPhone|iPod)/g))
        activeStyles['appleBugs'] = true;

    for (var style in activeStyles) {
        if (!activeStyles.hasOwnProperty(style)) continue;

        switch (style) {
            case "dayMode":
                $use_custom_colors.prop("checked", true);
                break;
            case "fontFamily":
                $custom_font_family.prop("checked", true);
                $font_family.prop('disabled',false);
                break;
            case "fontSize":
                $custom_font_size.prop("checked", true);
                $font_size.prop('disabled',false);
                break;
            case "maximizePage":
                $maximize_page.prop("checked", true);
                break;
            case "appleBugs":
                console.log("Apple mobile bugs detected, applying workarounds...");
                break;
        }

        reader.enableStyle(customStyles[style]);
    }

    $day_background.off('change').on('change', function() {
        customStyles.dayMode.rules.background = $day_background.val();
        $day_example.css('background', customStyles.dayMode.rules.background);
        reader.updateStyle(customStyles.dayMode);
    });

    $day_color.off('change').on('change', function() {
        customStyles.dayMode.rules.color = $day_color.val();
        $day_example.css('color', customStyles.dayMode.rules.color);
        reader.updateStyle(customStyles.dayMode);
    });

    $night_background.off('change').on('change', function() {
        customStyles.nightMode.rules.background = $night_background.val();
        $night_example.css('background', customStyles.nightMode.rules.background);
        reader.updateStyle(customStyles.nightMode);
    });

    $night_color.off('change').on('change', function() {
        customStyles.nightMode.rules.color = $night_color.val();
        $night_example.css('color', customStyles.nightMode.rules.color);
        reader.updateStyle(customStyles.nightMode);
    });

    $use_custom_colors.off('change').on('change', function () {
        if ($(this).prop('checked')) {
            reader.enableStyle(customStyles.dayMode);
        } else {
            reader.disableStyle(customStyles.dayMode);
        }
    });

    $nightshift.off('click').on('click', function () {
        if (settings.nightMode) {
            reader.disableStyle(customStyles.nightMode);
            settings.nightMode = false;
        } else {
            reader.enableStyle(customStyles.nightMode);
            settings.nightMode = true;
        }
    });

    $page_width.off('change').on("change", function () {
        customStyles.pageWidth.rules["page-width"] = $(this).val() + "em";
		reader.updateStyle(customStyles.pageWidth);
        $viewer.css("max-width", customStyles.pageWidth.rules["page-width"]);
    });

    $custom_font_family.off('click').on('click', function() {
        if ($(this).prop('checked')) {
            $font_family.prop('disabled',false);
            reader.enableStyle(customStyles.fontFamily);
        } else {
            $font_family.prop('disabled',true);
            reader.disableStyle(customStyles.fontFamily);
        }
    });

    $custom_font_size.off('click').on('click', function() {
        if ($(this).prop('checked')) {
            $font_size.prop('disabled',false);
            reader.enableStyle(customStyles.fontSize);
        } else {
            $font_size.prop('disabled',true);
            reader.disableStyle(customStyles.fontSize);
        }
    });

    $custom_font_weight.off('click').on('click', function() {
        if ($(this).prop('checked')) {
            $font_weight.prop('disabled',false);
            reader.enableStyle(customStyles.fontWeight);
        } else {
            $font_weight.prop('disabled',true);
            reader.disableStyle(customStyles.fontWeight);
        }
   });

   $maximize_page.off('click').on('click', function() {
        if ($(this).prop('checked')) {
            reader.enableStyle(customStyles.maximizePage);
        } else {
            reader.disableStyle(customStyles.maximizePage);
        }
    });

    $font_size.off('change').on('change', function() {
        $font_example.css('font-size', $(this).val() + '%');
        customStyles.fontSize.rules["font-size"] = $(this).val() + '%';
        reader.updateStyle(customStyles.fontSize);
    });

    $font_weight.off('change').on('change', function() {
        customStyles.fontWeight.rules["font-weight"] = $(this).val();
        $font_example.css('font-weight', $(this).val());
        reader.updateStyle(customStyles.fontWeight);
    });

    $font_family.off('change').on('change', function() {
        customStyles.fontFamily.rules["font-family"] = $(this).val();
        $font_example.css('font-family', $(this).val());
        reader.updateStyle(customStyles.fontFamily);
    });

    $page_width.off('change').on("change", function () {
        customStyles.pageWidth.rules["page-width"] = $(this).val() + "em";
		reader.updateStyle(customStyles.pageWidth);
        $viewer.css("max-width", customStyles.pageWidth.rules["page-width"]);
    });

    return {
    };
};
