<?php
/**
 * Kunena Component
 * @package Kunena
 *
 * @Copyright (C) 2008 - 2011 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined ( '_JEXEC' ) or die ();

$document = JFactory::getDocument();
$document->setTitle(JText::_('COM_KUNENA_ANN_ANNOUNCEMENTS') . ' - ' . $this->config->board_title);
JHTML::_('behavior.formvalidation');
$document->addScriptDeclaration('// <![CDATA[
	function myValidate(f) {
	if (document.formvalidator.isValid(f)) {
		return true;
	}
	return false;
}
// ]]>');
?>
<div class="forumlist">
	<div class="inner">
		<span class="corners-top"><span></span></span>
			<ul class="topiclist">
				<li class="header">
					<dl class="icon">
						<dt><?php echo JText::_('COM_KUNENA_ANN_ANNOUNCEMENTS'); ?>: <?php echo $this->announcement->id ? JText::_('COM_KUNENA_ANN_EDIT') : JText::_('COM_KUNENA_ANN_ADD'); ?></dt>
						<dd>&nbsp;</dd>
					</dl>
				</li>
			</ul>
			<div class="kdetailsbox kannouncement-details" id="kannouncement-detailsbox">
				<div class="kactions"><?php echo CKunenaLink::GetAnnouncementLink('show',NULL, JText::_('COM_KUNENA_ANN_CPANEL'), JText::_('COM_KUNENA_ANN_CPANEL')); ?></div>
				<form class="form-validate" action="<?php echo CKunenaLink::GetAnnouncementURL('doedit'); ?>" method="post" name="editform" onsubmit="return myValidate(this);">
					<?php echo JHTML::_( 'form.token' ); ?>
					<div>
						<label>
							<?php echo JText::_('COM_KUNENA_ANN_TITLE'); ?>:<br />
							<input class="klarge required" type="text" name="title" value="<?php echo $this->escape($this->announcement->title) ;?>"/>
						</label><br />
						<label>
							<?php echo JText::_('COM_KUNENA_ANN_SORTTEXT'); ?>:<br />
							<textarea class="ksmall required" rows="15" cols="80" name="sdescription"><?php echo $this->escape($this->announcement->sdescription); ?></textarea>
						</label>
					</div>
					<div>
						<label>
							<?php echo JText::_('COM_KUNENA_ANN_LONGTEXT'); ?>:<br />
							<textarea class="klarge" rows="30" cols="80" name="description"><?php echo $this->escape($this->announcement->description); ?></textarea>
						</label>
					</div>
					<div>
						<label>
							<?php echo JText::_('COM_KUNENA_ANN_DATE'); ?>:<br />
							<?php echo JHTML::_('calendar', $this->escape($this->announcement->created), 'created', 'addcreated');?>
						</label><br />
						<label>
							<?php echo JText::_('COM_KUNENA_ANN_SHOWDATE'); ?>:<br />
							<select name="showdate">
								<option value="1" <?php echo ($this->announcement->showdate == 1 ? 'selected="selected"' : ''); ?>><?php echo JText::_('COM_KUNENA_ANN_YES'); ?></option>
								<option value="0" <?php echo ($this->announcement->showdate == 0 ? 'selected="selected"' : ''); ?>><?php echo JText::_('COM_KUNENA_ANN_NO'); ?></option>
							</select>
						</label>
						<label>
							<?php echo JText::_('COM_KUNENA_ANN_PUBLISH'); ?>:
							<select name="published">
								<option value="1" <?php echo ($this->announcement->published == 1 ? 'selected="selected"' : ''); ?>><?php echo JText::_('COM_KUNENA_ANN_YES'); ?></option>
								<option value="0" <?php echo ($this->announcement->published == 0 ? 'selected="selected"' : ''); ?>><?php echo JText::_('COM_KUNENA_ANN_NO'); ?></option>
							</select>
						</label>
					</div>
					<input type='hidden' name="do" value="doedit"/>
					<input type='hidden' name="id" value="<?php echo intval($this->announcement->id) ;?>"/>
					<input name="submit" class="tk-submit-button" type="submit" value="<?php echo JText::_('COM_KUNENA_ANN_SAVE'); ?>"/>
				</form>
			</div>
		<span class="corners-bottom"><span></span></span>
	</div>
</div>