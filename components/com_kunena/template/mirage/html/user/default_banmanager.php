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

JHTML::_('behavior.tooltip');
$i=1;
$j=0;
?>
<div class="box-module">
	<div class="block-wrapper box-color box-border box-border_radius">
		<div class="block">
			<div class="headerbox-wrapper box-full">
				<div class="header">
					<h2 class="header"><a rel="kbanmanager-detailsbox"><?php echo JText::_('COM_KUNENA_BAN_BANMANAGER'); ?></a></h2>
				</div>
			</div>
			<div class="detailsbox-wrapper">
				<div class="banmanager banmanager-detailsbox detailsbox box-full box-border box-border_radius box-shadow">
					<ul class="list-unstyled banmanager-list">
						<li class="header box-hover_header-row clear">
							<dl class="list-unstyled">
								<dd class="banned-id"><span class="bold">#</span></dd>
								<dd class="banned-user"><span class="bold"><?php echo JText::_('COM_KUNENA_BAN_BANNEDUSER'); ?></span></dd>
								<dd class="banned-from"><span class="bold"><?php echo JText::_('COM_KUNENA_BAN_BANNEDFROM'); ?></span></dd>
								<dd class="banned-start"><span class="bold"><?php echo JText::_('COM_KUNENA_BAN_STARTTIME'); ?></span></dd>
								<dd class="banned-expire"><span class="bold"><?php echo JText::_('COM_KUNENA_BAN_EXPIRETIME'); ?></span></dd>
							</dl>
						</li>
					</ul>
					<ul class="list-unstyled banmanger-list">
						<?php
						if ( $this->bannedusers ) :
							foreach ($this->bannedusers as $userban) :
								$bantext = $userban->blocked ? JText::_('COM_KUNENA_BAN_UNBLOCK_USER') : JText::_('COM_KUNENA_BAN_UNBAN_USER');
								$j++;
						?>
						<li class="banmanager-row box-hover box-hover_list-row">
							<dl class="list-unstyled">
								<dd class="banned-id">
									<?php echo $j; ?>
								</dd>
								<dd class="banned-user">
									<?php echo CKunenaLink::GetProfileLink ( intval($userban->userid) ) ?>
								</dd>
								<dd class="banned-from">
									<span><?php echo $userban->blocked ? JText::_('COM_KUNENA_BAN_BANLEVEL_JOOMLA') : JText::_('COM_KUNENA_BAN_BANLEVEL_KUNENA') ?></span>
								</dd>
								<dd class="banned-start">
									<span><?php echo KunenaDate::getInstance($userban->created_time)->toKunena('datetime') ?></span>
								</dd>
								<dd class="banned-expire">
									<span><?php echo $userban->isLifetime() ? JText::_('COM_KUNENA_BAN_LIFETIME') : KunenaDate::getInstance($userban->expiration)->toKunena('datetime') ?></span>
								</dd>
							</dl>
						</li>
						<?php endforeach; ?>
						<?php else : ?>
						<li class="banmanager-row box-hover box-hover_list-row">
							<dl class="list-unstyled">
								<dd><?php echo JText::_('COM_KUNENA_BAN_NO_BANNED_USERS') ?></dd>
							</dl>
						</tr>
						<?php endif; ?>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="spacer"></div>