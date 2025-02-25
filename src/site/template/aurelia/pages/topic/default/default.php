<?php

/**
 * Kunena Component
 *
 * @package         Kunena.Template.Aurelia
 * @subpackage      Pages.Topic
 *
 * @copyright       Copyright (C) 2008 - @currentyear@ Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/

defined('_JEXEC') or die();

use Kunena\Forum\Libraries\Forum\Category\KunenaCategoryHelper;
use Kunena\Forum\Libraries\User\KunenaUserHelper;

$content = $this->execute('Topic/Item')
    ->setLayout(KunenaUserHelper::getMyself()->getTopicLayout());

// Display breadcrumb path to the current category / topic / message / moderate.
$parents   = KunenaCategoryHelper::getParents($content->category->id);
$parents[] = $content->category;

foreach ($parents as $parent) {
    $this->addBreadcrumb(
        $parent->displayField('name'),
        $parent->getUri()
    );
}

$this->addBreadcrumb(
    $content->topic->subject,
    $content->topic->getUri()
);

echo $content;
