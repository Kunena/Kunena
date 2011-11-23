<?php
/**
 * Kunena Component
 * @package Kunena.Template.Default20
 * @subpackage Common
 *
 * @copyright (C) 2008 - 2011 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined ( '_JEXEC' ) or die ();
?>
		<?php echo $this->getModulePosition( 'kunena_bottom' ); ?>
		<div id="kcredit">
			<p><?php echo $this->getTeamCreditsLink( JText::_('COM_KUNENA_POWEREDBY')) ?> <a href="http://www.kunena.org" title="<?php echo JText::_('COM_KUNENA_VIEW_COMMON_FOOTER_POWERED_BY_TITLE') ?>">Kunena</a></p
			<?php if ( $this->config->time_to_create_page ) : ?><p><?php echo JText::sprintf('COM_KUNENA_VIEW_COMMON_FOOTER_TIME', $this->getTime()) ?></p><?php endif; ?>
		</div>
