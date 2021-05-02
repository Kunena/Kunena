<?php
/**
 * Kunena Component
 *
 * @package         Kunena.Template.Aurelia
 * @subpackage      Pages.Announcement
 *
 * @copyright       Copyright (C) 2008 - 2021 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/

namespace Kunena\Forum\Site;

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Kunena\Forum\Libraries\Route\KunenaRoute;
use function defined;

$this->addBreadcrumb(
	Text::_('COM_KUNENA_ANN_ANNOUNCEMENTS'),
	KunenaRoute::normalize("index.php?option=com_kunena&view=announcement&layout=listing")
);

echo $this->subRequest('Announcement/Listing');
?>

<div class="clearfix"></div>

