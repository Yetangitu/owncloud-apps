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

function checkBox($format) {
	foreach($format as $name => $enabled) {
		echo '<input type="checkbox" id="opds-preview-' . $name . '" name="opds-preview-' . $name . '" ' . ($enabled == 1 ? 'checked >' : '>');
		echo '<label for="opds-preview-' . $name . '">' . $name . '</label>';
	}
} 

?>

<div class="section" id="opds">
        <h2><?php p($l->t('OPDS')); ?></h2>
	<h3>Enable preview for:<span class="msg"></span></h3>
	<div class="indent">
		<?php foreach ($_['previewFormats'] as $format): ?>
		<div>
			<?php checkBox($format); ?>
		</div>
		<?php endforeach; ?>
	</div>
	<br>
        <div>
		<p>Cover size</p>
                <label for="opds-cover-x"><?php p($l->t('width')) ?></label>
                <input type="text" id="opds-cover-x" title="<?php p($l->t("Enter cover image width in pixels")); ?>" value="<?php p($_['cover-x']) ?>" />
                <label for="opds-cover-y"><?php p($l->t('height')) ?></label>
                <input type="text" id="opds-cover-y" title="<?php p($l->t("Enter cover image height in pixels")); ?>" value="<?php p($_['cover-y']) ?>" />
        </div>
        <div>
		<p>Cover thumbnail size</p>
                <label for="opds-thumb-x"><?php p($l->t('width')) ?></label>
                <input type="text" id="opds-thumb-x" title="<?php p($l->t("Enter thumbnail width in pixels")); ?>" value="<?php p($_['thumb-x']) ?>" />
                <label for="opds-thumb-y"><?php p($l->t('height')) ?></label>
                <input type="text" id="opds-thumb-y" title="<?php p($l->t("Enter thumbnail height in pixels")); ?>" value="<?php p($_['thumb-y']) ?>" />
        </div>
</div>

