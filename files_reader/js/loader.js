var READER = function() {

    var isMobile = navigator.userAgent.match(/Mobi/i);
    var hasTouch = 'ontouchstart' in document.documentElement;

    var sharingToken = null;

    function hideReader() {
        FileList.setViewerMode(false);
        $("#controls").show();
        $('#app-content #controls').removeClass('hidden');
        $('iframe').remove();
    }

    function hideControls() {
        $('#app-content #controls').hide();
    }

    function showReader(dir, filename, share) {
        if (!showReader.shown) {
            if (share === 'undefined')
                share = '';
            var viewer = OC.linkTo('files_reader', 'viewer.php') + '?dir=' + encodeURIComponent(dir).replace(/%2F/g, '/') + '&file=' + encodeURIComponent(filename.replace('&', '%26')) + '&share=' + encodeURIComponent(share);
            if (isMobile || hasTouch)
                window.open(viewer, dir + '/' + filename);
            else {
                $iframe = '<iframe style="width:100%;height:100%;display:block;position:absolute;top:0;" src="' + viewer + '" allowfullscreen="true" webkitallowfullscreen="true" mozallowfullscreen="true"  sandbox="allow-scripts allow-same-origin"/>';
                if ($('#isPublic').val()) {
                    // force the preview to adjust its height
                    $('#preview').append($iframe).css({
                        height: '100%'
                    });
                    $('body').css({
                        height: '100%'
                    });
                    $('footer').addClass('hidden');
                    $('#imgframe').addClass('hidden');
                    $('.directLink').addClass('hidden');
                    $('.directDownload').addClass('hidden');
                    $('#controls').addClass('hidden');
                } else {
                    FileList.setViewerMode(true);
                    $('#app-content').append($iframe);
                }

                // replace the controls with our own
                hideControls();
            }
        }
    }

    function openReader(filename) {
        if ($('#isPublic').val()) {
            showReader(FileList.getCurrentDirectory(), filename, sharingToken);
        } else {
            showReader(FileList.getCurrentDirectory(), filename, '');
        }
    }

    $(document).ready(function() {
        if (!$.browser.msie) { //doesn't work on IE
            sharingToken = $('#sharingToken').val();

            // Logged view
            if ($('#filesApp').val() && typeof FileActions !== 'undefined') {
                OCA.Files.fileActions.register('application/epub+zip', 'Edit', OC.PERMISSION_READ, '', openReader);
                FileActions.setDefault('application/epub+zip', 'Edit');
            }

            // Publicly shared view
            if ($('#isPublic').val()) {
                if ($('#mimetype').val() === 'application/epub+zip') {
                    showReader('', '', sharingToken);
                }
            }
        }
    });

    return {
        hideReader: hideReader,
        hideControls: hideControls,
        showReader: showReader
    }

}();
