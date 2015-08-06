/*
 * Copyright (c) 2015 Frank de Lange
 * Copyright (c) 2013-2014 Lukas Reschke <lukas@owncloud.com>
 *
 * This file is licensed under the Affero General Public License version 3
 * or later.
 *
 * See the COPYING-README file.
 *
 */

(function(OCA) {

	OCA.FilesReader = OCA.FilesReader || {};

	var isMobile = navigator.userAgent.match(/Mobi/i);
	var hasTouch = 'ontouchstart' in document.documentElement;

	/**
	 * @namespace OCA.FilesReader.Plugin
	 */
	OCA.FilesReader.Plugin = {

		/**
		 * @param fileList
		 */
		attach: function(fileList) {
			this._extendFileActions(fileList.fileActions);
		},

		hideControls: function() {
			$('#app-content #controls').hide();
		},

		hide: function() {
			if ($('#fileList').length) {
				FileList.setViewerMode(false);
			}
			$("#controls").show();
			$('#app-content #controls').removeClass('hidden');
			if ($('#isPublic').val()) {
				$('#imgframe').show();
				$('footer').show();
				$('.directLink').show();
				$('.directDownload').show();
			}
			$('iframe').remove();
		},

		/**
		 * @param downloadUrl
		 * @param isFileList
		 */
		show: function(downloadUrl, isFileList) {
			var self = this;
			var $iframe;
            		var viewer = OC.generateUrl('/apps/files_reader/?file={file}', {file: downloadUrl});
			// launch in new window on mobile and touch devices...
			if (isMobile || hasTouch) {
				window.open(viewer, downloadUrl);
            		} else {
				$iframe = '<iframe style="width:100%;height:100%;display:block;position:absolute;top:0;" src="' + viewer + '" allowfullscreen="true" webkitallowfullscreen="true" mozallowfullscreen="true"  sandbox="allow-scripts allow-same-origin"/>';
				if (isFileList === true) {
					FileList.setViewerMode(true);
				}
				if ($('#isPublic').val()) {
					// force the preview to adjust its height
					$('#preview').append($iframe).css({ height: '100%' });
					$('body').css({ height: '100%' });
					$('footer').addClass('hidden');
					$('#imgframe').addClass('hidden');
					$('.directLink').addClass('hidden');
					$('.directDownload').addClass('hidden');
					$('#controls').addClass('hidden');
				} else {
					$('#app-content').append($iframe);
					self.hideControls();
				}
			}
		},

		/**
		 * @param fileActions
		 * @private
		 */
		_extendFileActions: function(fileActions) {
			var self = this;
			fileActions.registerAction({
				name: 'view',
				displayName: 'Favorite',
				mime: 'application/epub+zip',
				permissions: OC.PERMISSION_READ,
				actionHandler: function(fileName, context) {
					var downloadUrl = '';
					if($('#isPublic').val()) {
						var sharingToken = $('#sharingToken').val();
						downloadUrl = OC.generateUrl('/s/{token}/download?files={files}&path={path}', {
							token: sharingToken,
							files: fileName,
							path:  context.dir
						});
					} else {
						downloadUrl = Files.getDownloadUrl(fileName, context.dir);
					}
					self.show(downloadUrl, true);
				}
			});
			fileActions.setDefault('application/epub+zip', 'view');
		}
	};

})(OCA);

OC.Plugins.register('OCA.Files.FileList', OCA.FilesReader.Plugin);

// FIXME: Hack for single public file view since it is not attached to the fileslist
$(document).ready(function(){
	if ($('#isPublic').val() && $('#mimetype').val() === 'application/epub+zip') {
		var sharingToken = $('#sharingToken').val();
		var downloadUrl = OC.generateUrl('/s/{token}/download', {token: sharingToken});
		var viewer = OCA.FilesReader.Plugin;
		viewer.show(downloadUrl, false);
	}
});
