<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_menu
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

// Kunena detection and version check
$minKunenaVersion = '2.0';
if (!class_exists('KunenaForum') || !KunenaForum::installed() || !KunenaForum::isCompatible($minKunenaVersion)) {
	return;
}

// Include the syndicate functions only once
require_once dirname(__FILE__).'/helper.php';

$params = (object) $params;
$list	= modKunenamenuHelper::getList($params);
$app	= JFactory::getApplication();
$menu	= $app->getMenu();
$active	= $menu->getActive();
$active_id = isset($active) ? $active->id : $menu->getDefault()->id;
$path	= isset($active) ? $active->tree : array();
$showAll	= $params->get('showAllChildren');
$class_sfx	= htmlspecialchars($params->get('class_sfx'));

if(count($list)) {
	require JModuleHelper::getLayoutPath('mod_kunenamenu', $params->get('layout', 'default'));
}
