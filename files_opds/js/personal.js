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

	// save settings
        var opdsSettings = {
                save : function() {
			var opdsEnable = document.getElementById('opds-enable').checked ? 'true' : 'false';
                        var data = {
                                opdsEnable : opdsEnable,
                                rootPath : $('#opds-root-path').val(),
                                fileTypes : $('#opds-file-types').val()
                        };
                        OC.msg.startSaving('#opds-personal .msg');
                        $.post(OC.filePath('files_opds', 'ajax', 'personal.php'), data, opdsSettings.afterSave);
                },
                afterSave : function(data){
                        OC.msg.finishedSaving('#opds-personal .msg', data);
                }
        };
        $('#opds-root-path,#opds-file-types').blur(opdsSettings.save);
        $('#opds-root-path,#opds-file-types').keypress(function( event ) {
                                                if (event.which == 13) {
                                                  event.preventDefault();
                                                  opdsSettings.save();
                                                }
        });
        $('#opds-enable').on("change", opdsSettings.save);
});

