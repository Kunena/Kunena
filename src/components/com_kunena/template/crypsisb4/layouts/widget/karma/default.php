<?php
/**
 * Kunena Component
 *
 * @package         Kunena.Template.Crypsisb4
 * @subpackage      Layout.Widget
 *
 * @copyright       Copyright (C) 2008 - 2020 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

$karma = '';

if ($this->karmatype == 'karmadown')
{
	$url = 'index.php?option=com_kunena&view=user&task=karmadown&userid=' . $this->userid . '&' . Session::getFormToken() . '=1';
	$karmatype = 'minus';
}
else
{
	$url = 'index.php?option=com_kunena&view=user&task=karmaup&userid=' . $this->userid . '&' . Session::getFormToken() . '=1';
	$karmatype = 'plus';
}

if ($this->topicicontype == 'B3')
{
	$karmaIcon = '<span class="glyphicon-karma glyphicon glyphicon-' . $karmatype . '-sign text-danger" title="' . Text::_('COM_KUNENA_KARMA_SMITE') . '"></span>';
}
elseif ($this->topicicontype == 'fa')
{
    $karmaIcon = '<i class="fa fa-' . $karmatype . '-circle" title="' . Text::_('COM_KUNENA_KARMA_SMITE') . '"></i>';
}
elseif ($this->topicicontype == 'B2')
{
    $karmaIcon = '<span class="icon-karma icon icon-' . $karmatype .  ' text-error" title="' . Text::_('COM_KUNENA_KARMA_SMITE') . '"></span>';
}
else
{
    $karmaIcon = '<span class="kicon-profile kicon-profile-' .$karmatype. '" title="' . Text::_('COM_KUNENA_KARMA_SMITE') . '"></span>';
}

$karma .= ' ' . HTMLHelper::_('kunenaforum.link', $url, $karmaIcon);

echo $karma;
?>