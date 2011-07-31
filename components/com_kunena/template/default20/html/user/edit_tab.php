<?php
/**
 * Kunena Component
 * @package Kunena.Template.Default20
 * @subpackage User
 *
 * @copyright (C) 2008 - 2011 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined ( '_JEXEC' ) or die ();
?>
			<form action="<?php echo KunenaRoute::_('index.php?option=com_kunena') ?>" method="post" name="kuserform" class="form-validate" enctype="multipart/form-data">
				<input type="hidden" name="view" value="user" />
				<input type="hidden" name="task" value="save" />
				<?php echo JHTML::_( 'form.token' ); ?>
				<div id="kprofile-edit">
					<dl class="tabs">
						<dt class="open"><?php echo JText::_('COM_KUNENA_PROFILE_EDIT_USER') ?></dt>
						<dd style="display: none;">
							<?php $this->displayEditUser() ?>
						</dd>
						<dt class="closed"><?php echo JText::_('COM_KUNENA_PROFILE_EDIT_PROFILE') ?></dt>
						<dd style="display: none;">
							<?php $this->displayEditProfile() ?>
						</dd>
						<?php if ($this->editavatar) : ?>
						<dt class="closed"><?php echo JText::_('COM_KUNENA_PROFILE_EDIT_AVATAR') ?></dt>
						<dd style="display: none;">
							<?php $this->displayEditAvatar() ?>
						</dd>
						<?php endif ?>
						<dt class="closed"><?php echo JText::_('COM_KUNENA_PROFILE_EDIT_SETTINGS') ?></dt>
						<dd style="display: none;">
							<?php $this->displayEditSettings() ?>
						</dd>
					</dl>
					<div class="kpost-buttons">
						<button title="<?php echo JText::_('COM_KUNENA_EDITOR_HELPLINE_SAVE') ?>" type="submit" class="kbutton"><?php echo JText::_('COM_KUNENA_GEN_SAVE') ?></button>
						<button onclick="javascript:window.history.back();" title="<?php echo JText::_('COM_KUNENA_EDITOR_HELPLINE_CANCEL') ?>" type="button" class="kbutton"><?php echo (JText::_('COM_KUNENA_GEN_CANCEL')) ?></button>
					</div>
				</div>
			</form>