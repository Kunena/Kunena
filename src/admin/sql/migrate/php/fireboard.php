<?php
/**
 * Kunena Component
 *
 * @package        Kunena.Installer
 *
 * @copyright      Copyright (C) 2008 - 2021 Kunena Team. All rights reserved.
 * @license        https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link           https://www.kunena.org
 **/
defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Kunena\Forum\Libraries\KunenaInstaller;

/**
 * Class KunenaMigratorFireboard
 *
 * @since   Kunena 6.0
 */
class KunenaMigratorFireboard
{
	/**
	 * @var     array
	 * @since   Kunena 6.0
	 */
	protected $versions = [
		['version' => '1.0.4', 'date' => '2007-12-23', 'table' => 'fb_sessions', 'column' => 'currvisit'],
		['version' => '1.0.3', 'date' => '2007-09-04', 'table' => 'fb_categories', 'column' => 'headerdesc'],
		['version' => '1.0.2', 'date' => '2007-08-03', 'table' => 'fb_users', 'column' => 'rank'],
		['version' => '1.0.1', 'date' => '2007-05-20', 'table' => 'fb_users', 'column' => 'uhits'],
		['version' => '1.0.0', 'date' => '2007-04-15', 'table' => 'fb_messages', 'column' => 'id'],
	];

	/**
	 * @return  KunenaMigratorFireboard|null
	 *
	 * @since   Kunena 6.0
	 */
	public static function getInstance(): ?KunenaMigratorFireboard
	{
		static $instance = null;

		if (!$instance)
		{
			$instance = new KunenaMigratorFireboard;
		}

		return $instance;
	}

	/**
	 * Detect FireBoard version.
	 *
	 * @return  string  FireBoard version or null.
	 *
	 * @since   Kunena 6.0
	 */
	public function detect(): string
	{
		// Check if FireBoard can be found from the Joomla installation.
		if (KunenaInstaller::detectTable('fb_version'))
		{
			// Get installed version.
			$db = Factory::getContainer()->get('db');
			$db->setQuery("SELECT version, versiondate AS date FROM `#__fb_version` ORDER BY id DESC", 0, 1);
			$version = $db->loadRow();

			// Do not detect Kunena 1.x.
			if ($version && version_compare($version->version, '1.0.5', '>'))
			{
				return false;
			}

			// Return FireBoard version.
			if ($version->version)
			{
				return $version->version;
			}
		}

		foreach ($this->versions as $version)
		{
			if (KunenaInstaller::getTableColumn($version['table'], $version['column']))
			{
				return $version->version;
			}
		}

		return true;
	}
}
