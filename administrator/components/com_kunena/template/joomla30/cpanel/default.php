<?php
/**
 * Kunena Component
 * @package Kunena.Administrator.Template
 * @subpackage CPanel
 *
 * @copyright (C) 2008 - 2012 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined ( '_JEXEC' ) or die ();
?>
<div id="j-sidebar-container" class="span2">
	<div id="sidebar">
		<div class="sidebar-nav"><?php include KPATH_ADMIN.'/template/joomla30/common/menu.php'; ?></div>
	</div>
</div>
<div id="j-main-container" class="span10">
	<section class="content-block" role="main">
		<div class="alert alert-info">
			<button type="button" class="close" data-dismiss="alert">×</button>
			<strong>Welcome on the New Kunena Environment!!!</strong> Its all about Bootstrap.
		</div>
		<div class="row-fluid">
			<div class="span7">
				<div class="well well-small">
					<img class="pull-left" src="components/com_kunena/media/icons/kunena_logo.png" alt="" />
					<ul class="pull-left">
						<li><a href="http://docs.kunena.org/index.php/K_2.0_Installation_Guide" target="_blank">Faq: How to Setup </a></li>
						<li><a href="http://www.kunena.org/forum/K-2-0-Support/125990-kunena-2-0-3-known-issues" target="_blank">Faq: What are the knowing errors </a></li>
						<li><a href="http://www.kunena.org/forum" target="_blank">Faq: Free or Paid Support </a></li>
					</ul>
					<div class="clearfix"></div>
				</div>

				<div class="well well-small">
					<div class="btn-group">
						<div class="btn">
							<div class="icon">
								<a href="index.php?option=com_kunena&view=categories">
									<img src="components/com_kunena/media/icons/large/categories.png" alt="Categories" /><br />
									<span>Categories</span>
								</a>
							</div>
						</div>
						<div class="btn">
							<div class="icon">
								<a href="index.php?option=com_kunena&view=templates">
									<img src="components/com_kunena/media/icons/large/templates.png" alt="Templates" /><br />
									<span>Templates</span>
								</a>
							</div>
						</div>
						<div class="btn">
							<div class="icon">
								<a href="index.php?option=com_plugins&view=plugins&filter_folder=kunena">
									<img src="components/com_kunena/media/icons/large/pluginsmanager.png" alt="Plugin Manager" /><br/>
									<span>Plugin Manager</span>
								</a>
							</div>
						</div>
						<div class="btn">
							<div class="icon">
								<a href="index.php?option=com_kunena&view=tools">
									<img src="components/com_kunena/media/icons/large/purgerestatements.png" alt="Installer" /><br/>
									<span>Tools</span>
								</a>
							</div>
						</div>
					</div>
					<div class="clearfix"></div>
				</div>
			</div>

			<div class="span5">
				<div class="well well-small">
					<img class="pull-right" src="<?php JUri::root(true); ?>/media/kunena/images/icons/kunena-logo-110.png"/>
					<dl class="dl-horizontal">
						<dt>Version:</dt>
						<dd><?php echo KunenaForum::version(); ?></dd>
						<dt>Codename:</dt>
						<dd><?php echo KunenaForum::versionName(); ?></dd>
						<dt>Date:</dt>
						<dd><?php echo KunenaForum::versionDate(); ?></dd>
						<dt>Author:</dt>
						<dd><a href="http://www.kunena.org/team" target="_blank">Kunena Team</a></dd>
						<dt>Copyright:</dt>
						<dd>&copy; 2008 - 2012 Kunena, All rights reserved.</dd>
						<dt>License:</dt>
						<dd>GNU General Public License</dd>
					</dl>
				</div>
			</div>
		</div>
	</section>
</div>

<div class="pull-right small">
	<?php echo KunenaVersion::getLongVersionHTML(); ?>
</div>
