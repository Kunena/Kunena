<?php
/**
* Kunena Component
* @package Kunena.Administrator.Template
* @subpackage Categories
*
* @copyright (C) 2008 - 2012 Kunena Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @link http://www.kunena.org
**/

// no direct access
defined('_JEXEC') or die;
?>
<div class="modal hide fade" id="collapseModal">
	<div class="modal-header">
		<button type="button" role="presentation" class="close" data-dismiss="modal">x</button>
		<h3><?php echo JText::_('COM_KUNENA_BATCH_OPTIONS');?></h3>
	</div>
	<div class="modal-body">
		<p><?php echo JText::_('COM_KUNENA_BATCH_TIP'); ?></p>
		<div class="control-group">
			<div class="controls">
				<label id="batch-choose-action-lbl" for="batch-category-id">
				<?php echo JText::_('COM_KUNENA_BATCH_CATEGORY_LABEL'); ?>
				</label>
				<fieldset id="batch-choose-action" class="combo">
					<?php echo $this->batch_categories; ?>
					<div id="batch-move-copy" class="control-group radio">
						<div class="controls">
							<input type="radio" name="move_copy" value="copy" />
							<label><?php echo JText::_('COM_KUNENA_BATCH_CATEGORY_COPY') ?></label>
							<input type="radio" name="move_copy" value="move" />
							<label>
								<?php echo JText::_('COM_KUNENA_BATCH_CATEGORY_MOVE') ?>
							</label>
						</div>
					</div>
				</fieldset>
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<button class="btn" type="button" onclick="document.id('batch-category-id').value='';document.id('batch-access').value='';document.id('batch-language-id').value=''" data-dismiss="modal">
			<?php echo JText::_('JCANCEL'); ?>
		</button>
		<button class="btn btn-primary" type="submit" onclick="Joomla.submitbutton('batch_categories');">
			<?php echo JText::_('COM_KUNENA_BATCH_PROCESS'); ?>
		</button>
	</div>
</div>