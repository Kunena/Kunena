<?php
/**
 * Kunena Plugin
 *
 * @package         Kunena.Plugins
 * @subpackage      AltaUserPoints
 *
 * @copyright       Copyright (C) 2008 - 2021 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/

namespace Kunena\Forum\Plugin\Kunena\Altauserpoints;

defined('_JEXEC') or die();

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Kunena\Forum\Libraries\Factory\KunenaFactory;
use Kunena\Forum\Libraries\Integration\KunenaProfile;
use RuntimeException;
use function defined;

/**
 * KunenaActivityAltaUserPoints class to handle profile integration with AltaUserPoints
 *
 * @since  5.0
 */
class KunenaProfileAltaUserPoints extends KunenaProfile
{
	/**
	 * @var     null
	 * @since   Kunena 6.0
	 */
	protected $params = null;

	/**
	 * KunenaProfileAltaUserPoints constructor.
	 *
	 * @param   mixed  $params  params
	 *
	 * @since   Kunena 6.0
	 */
	public function __construct($params)
	{
		$this->params = $params;
	}

	/**
	 * @param   string  $action  action
	 * @param   bool    $xhtml   xhtml
	 *
	 * @return string
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws Exception
	 */
	public function getUserListURL($action = '', $xhtml = true): string
	{
		$config = KunenaFactory::getConfig();
		$my     = Factory::getApplication()->getIdentity();

		if ($config->userlistAllowed == 0 && $my->id == 0)
		{
			return false;
		}

		return AltaUserPointsHelper::getAupUsersURL();
	}

	/**
	 * @param   int  $limit  limit
	 *
	 * @return  array|boolean
	 * @since   Kunena 6.0
	 */
	public function _getTopHits($limit = 0): array
	{
		$db    = Factory::getDBO();
		$query = $db->getQuery(true)
			->select($db->quoteName(['u.*', 'ju.username', 'ju.email', 'ju.lastvisitDate'], [null, null, 'last_login']))
			->from($db->quoteName('#__alpha_userpoints', 'a'))
			->innerJoin($db->quoteName('#__users', 'u') . ' ON u.id = a.userid')
			->where('a.profileviews > 0')
			->order('a.profileviews DESC');
		$query->setLimit($limit);
		$db->setQuery($query);

		try
		{
			$top = (array) $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			return false;
		}

		return $top;
	}

	/**
	 *
	 * @param   int     $view
	 * @param   object  $params
	 *
	 * @return  void
	 *
	 * @since   Kunena 6.0
	 */
	public function showProfile(int $view, object $params)
	{
	}

	/**
	 * @param   integer  $userid  userid
	 * @param   bool     $xhtml   xhtml
	 *
	 * @return  boolean
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws Exception
	 */
	public function getEditProfileURL(int $userid, $xhtml = true): bool
	{
		return $this->getProfileURL($userid, '', $xhtml);
	}

	/**
	 * @param   mixed   $user   user
	 * @param   string  $task   task
	 * @param   bool    $xhtml  xhtml
	 *
	 * @return  boolean
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws  Exception
	 */
	public function getProfileURL($user, $task = '', $xhtml = true): bool
	{
		if ($user == 0)
		{
			return false;
		}

		$user = KunenaFactory::getUser($user);
		$my   = Factory::getApplication()->getIdentity();

		if ($user === false)
		{
			return false;
		}

		$userid     = $my->id != $user->userid ? '&userid=' . AltaUserPointsHelper::getAnyUserReferreID($user->userid) : '';
		$AUP_itemid = AltaUserPointsHelper::getItemidAupProfil();

		return Route::_('index.php?option=com_altauserpoints&view=account' . $userid . '&Itemid=' . $AUP_itemid, $xhtml);
	}

	/**
	 * Return username of user
	 *
	 * @param   integer $userid userid
	 * @param   bool    $xhtml  xhtml
	 *
	 * @since Kunena 5.2
	 * @return string
	 */
	public function getProfileName($user, $visitorname = '', $escape = true)
	{
		$referrid = AltaUserPointsHelper::getAnyUserReferreID($user->userid);
		$userinfo = AltaUserPointsHelper::getUserInfo($referrid);

		return $userinfo->username;
	}
}
