<?php
/**
 * Kunena Component
 * @package Kunena.Template.Mirage
 * @subpackage Common
 *
 * @copyright (C) 2008 - 2012 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined ( '_JEXEC' ) or die ();
?>
<ul class="list-unstyled login login-profile dropdown-menu">
	<li class="login-avatar link-dropdown">
		<a class="link" href="<?php echo KunenaRoute::_('index.php?option=com_kunena&view=user') ?>">
			<span class="login-avatar fl"><?php echo $this->me->getAvatarImage('', 'welcome') ?></span>
			<span class="login-view_profile"><?php echo JText::_('COM_KUNENA_VIEW_COMMON_LOGOUT_OWN_LINK_TITLE') ?></span>
			<span class="login-welcome"><?php echo JText::sprintf('COM_KUNENA_VIEW_COMMON_LOGOUT_WELCOME', $this->me->getName()) ?></span>
		</a>
	</li>
	<li class="divider"></li>
	<?php if (!empty($this->privateMessagesLink)) echo '<li class="pm link-dropdown">'.$this->privateMessagesLink.'</li>' ?>
	<?php if (!empty($this->editProfileLink)) echo '<li class="editprofile link-dropdown">'.$this->editProfileLink.'</li>' ?>
	<?php if (!empty($this->announcementsLink)) echo '<li class="announcements link-dropdown">'.$this->announcementsLink.'</li>' ?>
	<li class="divider"></li>
	<?php if ($this->logout) : ?>
		<li class="login-form">
			<!--<form action="<?php //echo KunenaRoute::_('index.php?option=com_kunena&view=user') ?>" method="post" class="form logout">
				<input type="hidden" name="task" value="logout" />
				<?php //echo $this->displayFormToken() ?>

				<fieldset>
					<legend class="klegend klogout"><?php //echo JText::_('COM_KUNENA_VIEW_COMMON_LOGOUT_LONG') ?></legend>
					<button class="kbutton button-type-standard" type="submit"><span><?php //echo JText::_('COM_KUNENA_PROFILEBOX_LOGOUT') ?></span></button>
				</fieldset>
			</form>-->
			<a class="link" href="<?php echo KunenaRoute::_('index.php?option=com_kunena&view=user&task=logout') ?>">
				<?php echo JText::_('COM_KUNENA_PROFILEBOX_LOGOUT') ?>
			</a>
		</li>
	<?php endif ?>

	<?php if ($this->moduleHtml) : ?>
	<li class = "login-modules">
		<?php echo $this->moduleHtml ?>
	</li>
	<?php endif; ?>
</ul>
