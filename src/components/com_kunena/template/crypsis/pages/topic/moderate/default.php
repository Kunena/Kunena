<?php
/**
 * Kunena Component
 * @package         Kunena.Template.Crypsis
 * @subpackage      Pages.Topic
 *
 * @copyright       Copyright (C) 2008 - 2023 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/
defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;

$content = $this->execute('Topic/Moderate');

// Display breadcrumb path to the current category / topic / message / moderate.
$parents   = KunenaForumCategoryHelper::getParents($content->category->id);
$parents[] = $content->category;

foreach ($parents as $parent)
{
	$this->addBreadcrumb(
		$parent->displayField('name'),
		$parent->getUri()
	);
}

$this->addBreadcrumb(
	Text::_('COM_KUNENA_MENU_TOPIC'),
	$content->topic->getUri()
);

if ($content->message)
{
	$this->addBreadcrumb(
		Text::_('COM_KUNENA_MESSAGE'),
		$content->message->getUri()
	);
}

$this->addBreadcrumb(
	Text::_('COM_KUNENA_MESSAGE_ACTIONS_LABEL_MODERATE'),
	$content->uri
);

echo $content;
