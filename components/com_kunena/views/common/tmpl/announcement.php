<?php
/**
 * Kunena Component
 * @package Kunena.Template.Default
 * @subpackage Common
 *
 * @copyright (C) 2008 - 2011 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined ( '_JEXEC' ) or die ();
?>
<!-- ANNOUNCEMENTS BOX -->
<div class="kblock kannouncement">
	<div class="kheader">
		<span class="ktoggler"><a class="ktoggler close" title="<?php echo JText::_('COM_KUNENA_TOGGLER_COLLAPSE') ?>" rel="kannouncement"></a></span>
		<h2><?php echo CKunenaLink::GetAnnouncementLink( 'read', $this->announcement->id, $this->annTitle, JText::_('COM_KUNENA_ANN_READMORE'),'follow'); ?></h2>
	</div>
	<div class="kcontainer" id="kannouncement">
		<?php if ($this->canEdit) : ?>
		<div class="kactions">
			<?php echo CKunenaLink::GetAnnouncementLink( 'edit', $this->announcement->id, JText::_('COM_KUNENA_ANN_EDIT'), JText::_('COM_KUNENA_ANN_EDIT')); ?> |
			<?php echo CKunenaLink::GetAnnouncementLink( 'delete', $this->announcement->id, JText::_('COM_KUNENA_ANN_DELETE'), JText::_('COM_KUNENA_ANN_DELETE')); ?> |
			<?php echo CKunenaLink::GetAnnouncementLink( 'add',NULL, JText::_('COM_KUNENA_ANN_ADD'), JText::_('COM_KUNENA_ANN_ADD')); ?> |
			<?php echo CKunenaLink::GetAnnouncementLink( 'show', NULL, JText::_('COM_KUNENA_ANN_CPANEL'), JText::_('COM_KUNENA_ANN_CPANEL')); ?>
		</div>
		<?php endif; ?>
		<div class="kbody">
			<div class="kanndesc">
				<?php if ($this->announcement->showdate) : ?>
				<div class="anncreated"><?php echo $this->annDate->toKunena('date_today') ?></div>
				<?php endif; ?>
				<div class="anndesc">
					<?php echo $this->annDescription; ?>
					<?php if (!empty($this->announcement->description)) : ?>
					...<br /><?php echo CKunenaLink::GetAnnouncementLink( 'read', $this->announcement->id, JText::_('COM_KUNENA_ANN_READMORE'), JText::_('COM_KUNENA_ANN_READMORE'),'follow'); ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php echo $this->getModulePosition ( 'kunena_announcement' ) ?>
<!-- / ANNOUNCEMENTS BOX -->