<?php
/**
 * Kunena Plugin
 *
 * @package     Kunena.Plugins
 * @subpackage    Community
 *
 * @copyright   (C) 2008 - 2015 Kunena Team. All rights reserved.
 * @copyright (C) 2013 - 2014 iJoomla, Inc. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link        http://www.kunena.org
 **/
defined('_JEXEC') or die ();

/**
 * Class KunenaProfileCommunity
 */
class KunenaProfileCommunity extends KunenaProfile
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
	 * @param string    $action
	 * @param bool|true $xhtml
	 *
	 * @return bool|string
	 */
	public function getUserListURL($action = '', $xhtml = true)
	{
		$config = KunenaFactory::getConfig();
		$my     = JFactory::getUser();
		if ($config->userlist_allowed == 1 && $my->id == 0)
		{
			return false;
		}

		return CRoute::_('index.php?option=com_community&view=search&task=browse', $xhtml);
	}

	/**
	 * @param           $userid
	 * @param string    $task
	 * @param bool|true $xhtml
	 *
	 * @return bool|string
	 */
	public function getProfileURL($userid, $task = '', $xhtml = true)
	{
		// Make sure that user profile exist.
		if (!$userid || CFactory::getUser($userid) === null)
		{
			return false;
		}

		return CRoute::_('index.php?option=com_community&view=profile&userid=' . (int) $userid, $xhtml);
	}

	/**
	 * @param int $limit
	 *
	 * @return array
	 */
	public function _getTopHits($limit = 0)
	{
		$db    = JFactory::getDBO();
		$query = "SELECT cu.userid AS id, cu.view AS count
			FROM #__community_users AS cu
			INNER JOIN #__users AS u ON u.id=cu.userid
			WHERE cu.view>0
			ORDER BY cu.view DESC";
		$db->setQuery($query, 0, $limit);
		$top = (array) $db->loadObjectList();
		KunenaError::checkDatabaseError();

		return $top;
	}

	/**
	 * @param $view
	 * @param $params
	 */
	public function showProfile($view, &$params)
	{
	}

	/**
	 * @param           $userid
	 * @param bool|true $xhtml
	 *
	 * @return bool|string|void
	 */
	public function getEditProfileURL($userid, $xhtml = true)
	{
		return $this->getProfileURL($userid, 'edit', $xhtml);
	}
}
