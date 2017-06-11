<?php
/**
 * Kunena Component
 *
 * @package    Kunena.Installer
 *
 * @copyright  (C) 2008 - 2017 Kunena Team. All rights reserved.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.kunena.org
 **/
defined('_JEXEC') or die();

// Kunena 5.0.5: Update setting allow guest to see userlist
/**
 * @return array|null
 * @internal param $parent
 *
 * @since Kunena
 */
function kunena_505_2016_12_20_userlist()
{
	$config = KunenaFactory::getConfig();

	if ($config->userlist_allowed)
	{
		$config->userlist_allowed = 0;
	}
	else
	{
		$config->userlist_allowed = 1;
	}

	// Save configuration
	$config->save();

	return null;
}
