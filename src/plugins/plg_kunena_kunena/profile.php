<?php
/**
 * Kunena Plugin
 *
 * @package         Kunena.Plugins
 * @subpackage      Kunena
 *
 * @copyright       Copyright (C) 2008 - 2021 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/

defined('_JEXEC') or die();

use Joomla\CMS\Access\Access;
use Joomla\CMS\Factory;
use Kunena\Forum\Libraries\Error\KunenaError;
use Kunena\Forum\Libraries\Factory\KunenaFactory;
use Kunena\Forum\Libraries\Integration\KunenaProfile;
use Kunena\Forum\Libraries\Route\KunenaRoute;
use Kunena\Forum\Libraries\User\KunenaUser;
use Kunena\Forum\Libraries\User\KunenaUserHelper;

/**
 * Class KunenaProfile
 *
 * @since   Kunena 6.0
 */
class KunenaProfileKunena extends KunenaProfile
{
	/**
	 * @var     null
	 * @since   Kunena 6.0
	 */
	protected $params = null;

	/**
	 * @param   object  $params  params
	 *
	 * @since   Kunena 6.0
	 */
	public function __construct(object $params)
	{
		$this->params = $params;
	}

	/**
	 * @param   string  $action  action
	 * @param   bool    $xhtml   xhtml
	 *
	 * @return  string
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws  Exception
	 * @throws  null
	 */
	public function getUserListURL($action = '', $xhtml = true)
	{
		$config = KunenaFactory::getConfig();
		$my     = Factory::getApplication()->getIdentity();

		if ($config->userlistAllowed == 0 && $my->id == 0)
		{
			return false;
		}

		return KunenaRoute::_('index.php?option=com_kunena&view=user&layout=list' . $action, $xhtml);
	}

	/**
	 * @param   int  $limit  limit
	 *
	 * @return  array
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws  Exception
	 */
	public function _getTopHits($limit = 0): array
	{
		$db    = Factory::getDBO();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(['u.id', 'ku.uhits'], [null, 'count']));
		$query->from($db->quoteName(['#__kunena_users'], ['ku']));
		$query->innerJoin($db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('ku.userid'));
		$query->where($db->quoteName('ku.uhits') . ' > 0');
		$query->order($db->quoteName('ku.uhits') . ' DESC');

		if (KunenaFactory::getConfig()->superAdminUserlist)
		{
			$filter = Access::getUsersByGroup(8);
			$query->andwhere('u.id NOT IN (' . implode(',', $filter) . ')');
		}

		$query->setLimit($limit);
		$db->setQuery($query);

		try
		{
			$top = (array) $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			KunenaError::displayDatabaseError($e);
		}

		return $top;
	}

	/**
	 * @param   int     $view    view
	 * @param   object  $params  params
	 *
	 * @return  void
	 *
	 * @since   Kunena 6.0
	 */
	public function showProfile($view, object $params)
	{
	}

	/**
	 * @param   int   $userid  userid
	 * @param   bool  $xhtml   xhtml
	 *
	 * @return  boolean
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws null
	 * @throws Exception
	 */
	public function getEditProfileURL(int $userid, $xhtml = true)
	{
		$avatartab = '&avatartab=1';

		return $this->getProfileURL($userid, 'edit', $xhtml = true, $avatartab);
	}

	/**
	 * @param   KunenaUser   $user       user
	 * @param   string       $task       task
	 * @param   bool         $xhtml      xhtml
	 * @param   bool|string  $avatarTab  avatarTab
	 *
	 * @return  boolean
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws null
	 * @throws Exception
	 */
	public function getProfileURL($user, $task = '', $xhtml = true, $avatarTab = '')
	{
		if ($user == 0)
		{
			return false;
		}

		if (!($user instanceof KunenaUser))
		{
			$user = KunenaUserHelper::get($user);
		}

		if ($user === false)
		{
			return false;
		}

		$userid = "&userid={$user->userid}";

		if ($task && $task != 'edit')
		{
			// TODO: remove in the future.
			$do = $task ? '&do=' . $task : '';

			return KunenaRoute::_("index.php?option=com_kunena&func=profile{$do}{$userid}", $xhtml);
		}

		$layout = $task ? '&layout=' . $task : '';

		if ($layout)
		{
			return KunenaRoute::_("index.php?option=com_kunena&view=user{$layout}{$userid}{$avatarTab}", $xhtml);
		}

		return KunenaRoute::getUserUrl($user, $xhtml);
	}

	/**
	 * Get the name of the user from this profile
	 *
	 * @param   KunenaUser  $user
	 * @param   string      $visitorname
	 * @param   bool        $escape
	 *
	 * @return string
	 * @see KunenaProfile::getProfileName()
	 * @since Kunena 5.2
	 */
	public function getProfileName($user, $visitorname = '', $escape = true)
	{
		$config = KunenaFactory::getConfig();

		if (!$user->userid && !$user->name)
		{
			$name = $visitorname;
		}
		else
		{
			$name = $config->username ? $user->username : $user->name;
		}

		if ($escape)
		{
			$name = htmlspecialchars($name, ENT_COMPAT, 'UTF-8');
		}

		return $name;
	}
}
