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

$l = new \OC_L10N('files_opds');

function checkBox($format) {
	foreach($format as $name => $enabled) {
		echo '<input type="checkbox" id="opds-preview-' . $name . '" name="opds-preview-' . $name . '" ' . ($enabled == 1 ? 'checked >' : '>');
		echo '<label for="opds-preview-' . $name . '">' . $name . '</label>';
	}
} 

?>

<div class="section" id="opds-admin">
        <h2><?php p($l->t('OPDS')); ?><span class="msg"></span></h2>
        <div>
                <label for="opds-feed-subtitle"><?php p($l->t('Feed subtitle:')) ?></label>
                <input type="text" id="opds-feed-subtitle" title="<?php p($l->t("Enter subtitle for OPDS catalog.")); ?>" value="<?php p($_['feedSubtitle']) ?>" />
        </div>
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

