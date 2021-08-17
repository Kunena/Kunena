<?php
/**
 * Kunena Component
 *
 * @package         Kunena.Template.Aurelia
 * @subpackage      Layout.Widget
 *
 * @copyright       Copyright (C) 2008 - 2021 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/

namespace Kunena\Forum\Site;

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Kunena\Forum\Libraries\Route\KunenaRoute;
use function defined;

$catid = Factory::getApplication()->input->getInt('catid', 0);
?>
<script>
	document.addEventListener('DOMContentLoaded', () => {
		const jumpTo = document.querySelector('#jumpto option[value=<?php echo $catid;?>]');
		if (jumpTo !== null) {
			jumpTo.selected = true;
		}
	})
</script>
<form action="<?php echo KunenaRoute::_('index.php?option=com_kunena'); ?>" id="jumpto" name="jumpto" method="post"
	  target="_self">
	<input type="hidden" name="view" value="category"/>
	<input type="hidden" name="task" value="jump"/>
	<span><?php echo $this->categorylist; ?></span>
</form>
