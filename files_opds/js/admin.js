$(document).ready(function(){
	// save settings
        var opdsAdminSettings = {
                save : function() {
			var epub = document.getElementById('opds-preview-epub').checked ? 'true' : 'false';
			var pdf = document.getElementById('opds-preview-pdf').checked ? 'true' : 'false';
			var opendocument = document.getElementById('opds-preview-opendocument').checked ? 'true' : 'false';
			var msoffice = document.getElementById('opds-preview-msoffice').checked ? 'true' : 'false';
                        var data = {
                                opdsPreviewEpub : epub,
                                opdsPreviewPdf : pdf,
                                opdsPreviewOpenDocument : opendocument,
                                opdsPreviewMsOffice : msoffice
                        };
                        OC.msg.startSaving('#opds-admin .msg');
                        $.post(OC.filePath('files_opds', 'ajax', 'admin.php'), data, opdsAdminSettings.afterSave);
                },
                afterSave : function(data){
                        OC.msg.finishedSaving('#opds-admin .msg', data);
                }
        };

        var opdsAdminCoverSettings = {
                save : function() {
                        var data = {
                                opdsCoverX : $('#opds-cover-x').val(),
                                opdsCoverY : $('#opds-cover-y').val(),
                                opdsThumbX : $('#opds-thumb-x').val(),
                                opdsThumbY : $('#opds-thumb-y').val(),
				opdsFeedSubtitle : $('#opds-feed-subtitle').val()
                        };
                        OC.msg.startSaving('#opds-admin .msg');
                        $.post(OC.filePath('files_opds', 'ajax', 'admin.php'), data, opdsAdminCoverSettings.afterSave);
                },
                afterSave : function(data){
                        OC.msg.finishedSaving('#opds-admin .msg', data);
                }
        };

        $('#opds-preview-epub').on("change", opdsAdminSettings.save);
        $('#opds-preview-pdf').on("change", opdsAdminSettings.save);
        $('#opds-preview-opendocument').on("change", opdsAdminSettings.save);
        $('#opds-preview-msoffice').on("change", opdsAdminSettings.save);

        $('#opds-cover-x,#opds-cover-y,#opds-thumb-x,#opds-thumb-y,#opds-feed-subtitle').blur(opdsAdminCoverSettings.save);
        $('#opds-cover-x,#opds-cover-y,#opds-thumb-x,#opds-thumb-y,#opds-feed-subtitle').keypress(function( event ) {
                                                if (event.which == 13) {
                                                  event.preventDefault();
                                                  opdsAdminCoverSettings.save();
                                                }
        });

});

