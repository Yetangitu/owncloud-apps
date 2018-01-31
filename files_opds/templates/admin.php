<?php

/**
 * ownCloud - Files_Opds App
 *
 * @author Frank de Lange
 * @copyright 2014 Frank de Lange
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 */

$l = \OC::$server->getL10N('files_opds');

function checkBox($format) {
	foreach($format as $name => $enabled) {
		echo '<input type="checkbox" class="checkbox" id="opds-preview-' . $name . '" name="opds-preview-' . $name . '" ' . ($enabled == 1 ? 'checked >' : '>');
		echo '<label for="opds-preview-' . $name . '">' . $name . '</label>';
	}
} 

?>

<div class="section" id="opds-admin">
	<table>
	<tr>
                <td><h2><?php p($l->t('OPDS')); ?></h2></td><td>&nbsp;<span class="msg"></span></td>
	</tr>
	</table>
	<table>
        <tr>
                <td><label for="opds-feed-subtitle"><?php p($l->t('Feed subtitle:')) ?></label></td>
                <td><input type="text" id="opds-feed-subtitle" title="<?php p($l->t("Enter subtitle for OPDS catalog.")); ?>" value="<?php p($_['feedSubtitle']) ?>" /></td>
        </tr>
        <tr>
                <td><label for="opds-isbndb-key"><?php p($l->t('ISBNdb key:')) ?></label></td>
                <td><input type="text" id="opds-isbndb-key" title="<?php p($l->t("Enter ISBNdb key to use for metadata lookup. Leave blank to disable ISBNdb lookup.")); ?>" value="<?php p($_['isbndbKey']) ?>" /></td>
        </tr>
        <tr>
                <td><label for="opds-google-key"><?php p($l->t('Google Books API key:')) ?></label></td>
                <td><input type="text" id="opds-google-key" title="<?php p($l->t("Enter Google Books API key to use for metadata lookup. Even though metadata lookup will work without an API key, the rate limit is higher when a key is used.")); ?>" value="<?php p($_['googleKey']) ?>" /></td>
        </tr>
	</table>
	<br>
	<p><?php p($l->t('Enable preview for:')); ?></p>
	<div class="indent">
		<?php foreach ($_['previewFormats'] as $format): ?>
		<div>
			<?php checkBox($format); ?>
		</div>
		<?php endforeach; ?>
	</div>
	<br>
        <div>
		<p><?php p($l->t('Cover size')); ?></p>
                <label for="opds-cover-x"><?php p($l->t('width')) ?></label>
                <input type="text" id="opds-cover-x" title="<?php p($l->t("Enter cover image width in pixels")); ?>" value="<?php p($_['cover-x']) ?>" />
                <label for="opds-cover-y"><?php p($l->t('height')) ?></label>
                <input type="text" id="opds-cover-y" title="<?php p($l->t("Enter cover image height in pixels")); ?>" value="<?php p($_['cover-y']) ?>" />
        </div>
        <div>
		<p><?php p($l->t('Cover thumbnail size')); ?></p>
                <label for="opds-thumb-x"><?php p($l->t('width')) ?></label>
                <input type="text" id="opds-thumb-x" title="<?php p($l->t("Enter thumbnail width in pixels")); ?>" value="<?php p($_['thumb-x']) ?>" />
                <label for="opds-thumb-y"><?php p($l->t('height')) ?></label>
                <input type="text" id="opds-thumb-y" title="<?php p($l->t("Enter thumbnail height in pixels")); ?>" value="<?php p($_['thumb-y']) ?>" />
        </div>
</div>

