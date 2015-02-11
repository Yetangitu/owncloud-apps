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

?>

<div class="section" id="opds-personal">
        <table>
        <tr>
                <td><h2><?php p($l->t('OPDS')); ?></h2></td><td>&nbsp;<span class="msg"></span></td>
        </tr>
        </table>
        <div>
		<input id="opds-enable" name="opds-enable" value="<?php p($_['opdsEnable-value']) ?>" <?php p($_['opdsEnable-checked']) ?> type="checkbox">
                <label for="opds-enable"><?php p($l->t('enable OPDS catalog')) ?></label>
	</div>
	<br>
	<table>
	<tr>
                <td><label for="opds-feed-title"><?php p($l->t('Feed title:')) ?></label></td>
                <td><input type="text" id="opds-feed-title" title="<?php p($l->t("Enter title for OPDS catalog.")); ?>" value="<?php p($_['feedTitle']) ?>" /></td>
        </tr>
	<tr>
                <td><label for="opds-root-path"><?php p($l->t('Root directory:')) ?></label></td>
                <td><input type="text" id="opds-root-path" title="<?php p($l->t("Enter root directory for OPDS catalog.")); ?>" value="<?php p($_['rootPath']) ?>" /></td>
        </tr>
	<tr>
                <td><label for="opds-file-types"><?php p($l->t('Supported extensions:')) ?></label></td>
                <td><input type="text" id="opds-file-types" title="<?php p($l->t("Enter list of comma-separated extensions (eg. pdf,epub,doc,txt). Leave blank to publish all file types.")); ?>" value="<?php p($_['fileTypes']) ?>" /></td>
        </tr>
	<tr>
                <td><label for="opds-skip-list"><?php p($l->t('Skip these filenames:')) ?></label></td>
                <td><input type="text" id="opds-skip-list" title="<?php p($l->t("Enter list of comma-separated file names which should be skipped. Leave blank to use the default skip list.")); ?>" value="<?php p($_['skipList']) ?>" /></td>
        </tr>
	</table>
	<br>
	<div>
                <input type="button" id="opds-rescan" value="<?php p($l -> t('Schedule rescan')); ?>" />
                <input type="button" id="opds-really-rescan" title="<?php p($l->t("Schedule a rescan of all metadata.")); ?>" value="<?php p($l -> t('Yes, I really want to schedule a rescan of all metadata')); ?>" hidden />
                <input type="button" id="opds-dont-rescan" value="<?php p($l -> t('No, I do not want to schedule a rescan of all metadata')); ?>" hidden />
		<span class="scn"></span>
        </div>
	<br>
	<div>
                <input type="button" id="opds-clear-bookshelf" value="<?php p($l -> t('Clear Bookshelf')); ?>" />
                <input type="button" id="opds-really-clear-bookshelf" title="<?php p($l->t("Clear list of downloaded books from bookshelf. This only clears the list, it does not delete any books.")); ?>" value="<?php p($l -> t('Yes, I really want to clear my personal bookshelf')); ?>" hidden />
                <input type="button" id="opds-dont-clear-bookshelf" value="<?php p($l -> t('No, I do not want to clear my bookshelf')); ?>" hidden />
		<span class="clr"></span>
		<div>
			<span id="opds-book-count"><?php p($l->t('There are %s books on your personal bookshelf', array($_['bookshelf-count']))); ?></span>&nbsp;
		</div>
        </div>
	<br>
	<div>
		<span><?php p($l->t("OPDS URL")); ?>:</span>
		<code><?php p($_['feedUrl']); ?></code>
		<div><?php p($l->t("Use your Owncloud username and password.")); ?></div>
	</div>
</div>

