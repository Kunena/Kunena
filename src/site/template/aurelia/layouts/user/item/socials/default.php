<?php

/**
 * Kunena Component
 *
 * @package         Kunena.Template.Aurelia
 * @subpackage      Layout.User
 *
 * @copyright       Copyright (C) 2008 - @currentyear@ Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/

namespace Kunena\Forum\Site;

\defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;

$showAll = isset($this->showAll) ? $this->showAll : false;
?>
<div class="inline float-end">
    <?php foreach ($this->socials as $key => $social) {
        // Only show icons for networks that have values and that have Font Awesome icons
        if (isset($social->fa) && !empty($social->value)) {
            echo '<a href="' . htmlspecialchars(str_replace('##VALUE##', $social->value, $social->url), ENT_QUOTES, 'UTF-8') . '" ';
            echo 'target="_blank" rel="nofollow" title="' . Text::_($social->title) . '">';
            echo '<i class="' . htmlspecialchars($social->fa, ENT_QUOTES, 'UTF-8') . '"></i>';
            echo '</a> ';
        }
    }
    ?>
</div>