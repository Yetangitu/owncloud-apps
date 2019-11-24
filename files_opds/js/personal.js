$(document).ready(function(){
	// clear bookshelf
	$('#opds-clear-bookshelf').on("click", function() {
		$('#opds-really-clear-bookshelf,#opds-dont-clear-bookshelf').show();
	});
	$('#opds-dont-clear-bookshelf').on("click", function() {
		$('#opds-really-clear-bookshelf,#opds-dont-clear-bookshelf').hide();
	});
	$('#opds-really-clear-bookshelf').on("click", function() {
                $.post(OC.filePath('files_opds','ajax','clear_bookshelf.php'), {},
                        function(result){
                                if(result) {
                                        OC.msg.finishedSaving('#opds-personal .clr', result);
					$('#opds-book-count').hide();
                                }
                        });
		$('#opds-really-clear-bookshelf,#opds-dont-clear-bookshelf').hide();
        });

	// schedule rescan
	$('#opds-rescan').on("click", function() {
		$('#opds-really-rescan,#opds-dont-rescan').show();
	});
	$('#opds-dont-rescan').on("click", function() {
		$('#opds-really-rescan,#opds-dont-rescan').hide();
	});
	$('#opds-really-rescan').on("click", function() {
                $.post(OC.filePath('files_opds','ajax','schedule_rescan.php'), {},
                        function(result){
                                if(result) {
                                        OC.msg.finishedSaving('#opds-personal .scn', result);
                                }
                        });
		$('#opds-really-rescan,#opds-dont-rescan').hide();
        });


	// save settings
        var opdsSettings = {
                save : function() {
			var opdsEnable = document.getElementById('opds-enable').checked ? 'true' : 'false';
			var opdsoldMime = document.getElementById('opds-oldmime').checked ? 'true' : 'false';
                        var data = {
                                opdsEnable : opdsEnable,
                                opdsoldMime : opdsoldMime,
                                rootPath : $('#opds-root-path').val(),
                                fileTypes : $('#opds-file-types').val(),
                                skipList : $('#opds-skip-list').val(),
                                feedTitle : $('#opds-feed-title').val()
                        };
                        OC.msg.startSaving('#opds-personal .msg');
                        $.post(OC.filePath('files_opds', 'ajax', 'personal.php'), data, opdsSettings.afterSave);
                },
                afterSave : function(data){
                        OC.msg.finishedSaving('#opds-personal .msg', data);
                }
        };
        $('#opds-root-path,#opds-file-types,#opds-feed-title,#opds-skip-list').blur(opdsSettings.save);
        $('#opds-root-path,#opds-file-types,#opds-feed-title,#opds-skip-list').keypress(function( event ) {
                                                if (event.which == 13) {
                                                  event.preventDefault();
                                                  opdsSettings.save();
                                                }
        });
        $('#opds-enable').on("change", opdsSettings.save);
        $('#opds-oldmime').on("change", opdsSettings.save);
});

