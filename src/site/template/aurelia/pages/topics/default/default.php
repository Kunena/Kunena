<?php
/**
 * Kunena Component
 *
 * @package         Kunena.Template.Aurelia
 * @subpackage      Pages.Topics
 *
 * @copyright       Copyright (C) 2008 - 2021 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/

namespace Kunena\Forum\Site;

defined('_JEXEC') or die();

use function defined;

// $this is an object of KunenaLayoutPage
$content = $this->execute('Topic/List/Recent')
	->setLayout('recent');

$this->addBreadcrumb(
	$content->headerText,
	'index.php?option=com_kunena&view=topics&layout=default'
);

echo $content;
echo $this->subRequest('Widget/WhoIsOnline');
echo $this->subRequest('Widget/Statistics');
