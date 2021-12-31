<?php
/**
 * Kunena Component
 *
 * @package        Kunena.Installer
 *
 * @copyright      Copyright (C) 2008 - 2022 Kunena Team. All rights reserved.
 * @license        https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link           https://www.kunena.org
 **/

use Kunena\Forum\Libraries\KunenaInstaller;

defined('_JEXEC') or die();

/**
 * Class KunenaMigratorJoomlaboard
 *
 * @since   Kunena 6.0
 */
class KunenaMigratorJoomlaboard
{
	/**
	 * @var     array
	 * @since   Kunena 6.0
	 */
	protected $versions = [
		['version' => '1.0', 'date' => '1000-01-01', 'table' => 'sb_messages', 'column' => 'id'],
	];

	/**
	 * @return  KunenaMigratorJoomlaboard|null
	 *
	 * @since   Kunena 6.0
	 */
	public static function getInstance(): ?KunenaMigratorJoomlaboard
	{
		static $instance = null;

		if (!$instance)
		{
			$instance = new KunenaMigratorJoomlaboard;
		}

		return $instance;
	}

	/**
	 * Detect JoomlaBoard version.
	 *
	 * @return  string  JoomlaBoard version or null.
	 *
	 * @since   Kunena 6.0
	 */
	public function detect(): string
	{
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
