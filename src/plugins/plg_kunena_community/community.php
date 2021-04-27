<?php
/**
 * Kunena Plugin
 *
 * @package          Kunena.Plugins
 * @subpackage       Community
 *
 * @copyright   (C)  2008 - 2021 Kunena Team. All rights reserved.
 * @copyright   (C)  2013 - 2014 iJoomla, Inc. All rights reserved.
 * @license          https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link             https://www.kunena.org
 **/

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Kunena\Forum\Libraries\Forum\KunenaForum;

/**
 * Class plgKunenaCommunity
 *
 * @since   Kunena 6.0
 */
class plgKunenaCommunity extends CMSPlugin
{
	/**
	 * plgKunenaCommunity constructor.
	 *
	 * @param $subject
	 * @param $config
	 *
	 * @since   Kunena 6.0
	 */
	public function __construct(&$subject, $config)
	{
		// Do not load if Kunena version is not supported or Kunena is offline
		if (!(class_exists('Kunena\Forum\Libraries\Forum\KunenaForum') && KunenaForum::isCompatible('6.0') && KunenaForum::installed()))
		{
			return;
		}

		// Do not load if JomSocial is not installed
		$path = JPATH_ROOT . '/components/com_community/libraries/core.php';

		if (!is_file($path))
		{
			if (PluginHelper::isEnabled('kunena', 'community'))
			{
				$db    = Factory::getDBO();
				$query = $db->getQuery(true);
				$query->update($db->quoteName('#__extensions'));
				$query->where($db->quoteName('element') . ' = ' . $db->quote('community'));
				$query->where($db->quoteName('type') . ' = ' . $db->quote('plugin'));
				$query->where($db->quoteName('folder') . ' = ' . $db->quote('kunena'));
				$query->set($db->quoteName('enabled') . ' = 0');
				$db->setQuery($query);
				$db->execute();
			}

			return;
		}

		include_once $path;

		parent::__construct($subject, $config);

		$this->loadLanguage('plg_kunena_community.sys', JPATH_ADMINISTRATOR) || $this->loadLanguage('plg_kunena_community.sys', KPATH_ADMIN);
	}

	/**
	 * Get Kunena access control object.
	 *
	 * @todo    Should we remove category ACL integration?
	 * @return  KunenaAccessCommunity|void
	 *
	 * @since   Kunena
	 */
	public function onKunenaGetAccessControl()
	{
		if (!$this->params->get('access', 1))
		{
			return;
		}

		return new KunenaAccessCommunity($this->params);
	}

	/**
	 * Get Kunena login integration object.
	 *
	 * @return  KunenaLoginCommunity|null|void
	 * @since   Kunena 6.0
	 */
	public function onKunenaGetLogin(): ?KunenaLoginCommunity
	{
		if (!$this->params->get('login', 1))
		{
			return;
		}

		return new KunenaLoginCommunity($this->params);
	}

	/**
	 * Get Kunena avatar integration object.
	 *
	 * @return  AvatarCommunity|null|void
	 * @since   Kunena 6.0
	 */
	public function onKunenaGetAvatar(): ?AvatarCommunity
	{
		if (!$this->params->get('avatar', 1))
		{
			return;
		}

		return new AvatarCommunity($this->params);
	}

	/**
	 * Get Kunena profile integration object.
	 *
	 * @return  KunenaProfileCommunity|null|void
	 * @since   Kunena 6.0
	 */
	public function onKunenaGetProfile(): ?KunenaProfileCommunity
	{
		if (!$this->params->get('profile', 1))
		{
			return;
		}

		return new KunenaProfileCommunity($this->params);
	}

	/**
	 * Get Kunena private message integration object.
	 *
	 * @return  KunenaPrivateCommunity|null|void
	 * @since   Kunena 6.0
	 */
	public function onKunenaGetPrivate(): ?KunenaPrivateCommunity
	{
		if (!$this->params->get('private', 1))
		{
			return;
		}

		return new KunenaPrivateCommunity($this->params);
	}

	/**
	 * Get Kunena activity stream integration object.
	 *
	 * @return  KunenaActivityCommunity|null|void
	 * @since   Kunena 6.0
	 * @throws Exception
	 */
	public function onKunenaGetActivity(): ?KunenaActivityCommunity
	{
		if (!$this->params->get('activity', 1))
		{
			return;
		}

		return new KunenaActivityCommunity($this->params);
	}
}
