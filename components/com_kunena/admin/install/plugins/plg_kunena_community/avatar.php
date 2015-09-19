<?php
/**
 * Kunena Plugin
 *
 * @package     Kunena.Plugins
 * @subpackage    Community
 *
 * @copyright   (C) 2008 - 2015 Kunena Team. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link        http://www.kunena.org
 **/
defined('_JEXEC') or die ();

/**
 * Class KunenaAvatarCommunity
 */
class KunenaAvatarCommunity extends KunenaAvatar
{
	protected $params = null;

	/**
	 * @param $params
	 */
	public function __construct($params)
	{
		$this->params = $params;
	}

	/**
	 * @param $userlist
	 */
	public function load($userlist)
	{
		KUNENA_PROFILER ? KunenaProfiler::instance()->start('function ' . __CLASS__ . '::' . __FUNCTION__ . '()') : null;
		if (class_exists('CFactory') && method_exists('CFactory', 'loadUsers'))
		{
			CFactory::loadUsers($userlist);
		}
		KUNENA_PROFILER ? KunenaProfiler::instance()->stop('function ' . __CLASS__ . '::' . __FUNCTION__ . '()') : null;
	}

	/**
	 * @return string
	 */
	public function getEditURL()
	{
		return CRoute::_('index.php?option=com_community&view=profile&task=uploadAvatar');
	}

	/**
	 * @param $user
	 * @param $sizex
	 * @param $sizey
	 *
	 * @return string
	 */
	protected function _getURL($user, $sizex, $sizey)
	{
		$kuser = KunenaFactory::getUser($user);
		// Get CUser object
		$user = CFactory::getUser($kuser->userid);

		if ($kuser->userid == 0)
		{
			$avatar = str_replace(JUri::root(true), '', COMMUNITY_PATH_ASSETS) . "user-Male.png";
		}
		else
		{
			if ($sizex <= 90)
			{
				$avatar = $user->getThumbAvatar();
			}
			else
			{
				$avatar = $user->getAvatar();
			}
		}

		return $avatar;
	}
}
