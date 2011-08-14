<?php
/**
 * Kunena Component
 * @package Kunena.Framework
 * @subpackage Integration
 *
 * @copyright (C) 2008 - 2011 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined ( '_JEXEC' ) or die ();

require_once KPATH_ADMIN . '/libraries/integration/integration.php';
kimport ( 'kunena.error' );
kimport ( 'kunena.forum.category.helper' );
kimport ( 'kunena.forum.topic.helper' );
kimport ( 'kunena.databasequery' );

abstract class KunenaAccess {
	public $priority = 0;

	protected static $instance = false;
	protected static $adminsByCatid = array();
	protected static $adminsByUserid = array();
	protected static $moderatorsByCatid = array();
	protected static $moderatorsByUserid = array();

	protected static $cacheKey = 'com_kunena.access.global';

	abstract public function __construct();

	static public function getInstance($integration = null) {
		KUNENA_PROFILER ? KunenaProfiler::instance()->start('function '.__CLASS__.'::'.__FUNCTION__.'()') : null;
		if (self::$instance === false) {
			if (! $integration) {
				$integration = KunenaFactory::getConfig ()->integration_access;
			}
			self::$instance = KunenaIntegration::initialize ( 'access', $integration );

			// Load administrators and moderators
			$cache = JFactory::getCache('com_kunena', 'output');
			$data = $cache->get(self::$cacheKey, 'com_kunena');
			if ($data) {
				$data = unserialize($data);
				self::$adminsByCatid = (array)$data['ac'];
				self::$adminsByUserid = (array)$data['au'];
				self::$moderatorsByCatid = (array)$data['mc'];
				self::$moderatorsByUserid = (array)$data['mu'];
			}
			//$my = JFactory::getUser();
			// If values were not cached (or users permissions have been changed), force reload
			if (!$data) { // || ($my->id && $my->authorize('com_kunena', 'administrator') == empty(self::$adminsByUserid[$my->id][0]) )) {
				self::$instance->clearCache();
			}
		}
		KUNENA_PROFILER ? KunenaProfiler::instance()->stop('function '.__CLASS__.'::'.__FUNCTION__.'()') : null;
		return self::$instance;
	}

	public function clearCache() {
		self::$adminsByCatid = array();
		self::$adminsByUserid = array();
		self::$moderatorsByCatid = array();
		self::$moderatorsByUserid = array();
		self::$instance->loadAdmins();
		self::$instance->loadModerators();

		// Store new data into cache
		$cache = JFactory::getCache('com_kunena', 'output');
		$cache->store(serialize(array(
			'ac'=>self::$adminsByCatid,
			'au'=>self::$adminsByUserid,
			'mc'=>self::$moderatorsByCatid,
			'mu'=>self::$moderatorsByUserid,
			)), self::$cacheKey, 'com_kunena');
	}

	public function getAccessLevelsList($category) {
		if ($category->accesstype == 'none' || $category->accesstype == 'joomla.level')
			return JHTML::_('list.accesslevel', $category);
		return $category->access;
	}

	public function getAccessTypesList($category) {
		static $enabled = false;
		if ($category->accesstype == 'none' || $category->accesstype == 'joomla.level') {
			if (!$enabled) {
				$enabled = true;
				JFactory::getDocument()->addScriptDeclaration("function kShowAccessType(htmlclass, el) {
	var name = new String(el.getSelected().get('value'));
	name = name.replace(/[^\\w\\d]+/, '-');
	$$('.'+htmlclass).each(function(e){
		e.setStyle('display', 'none');
	});
	$$('.'+htmlclass+'-'+name).each(function(e){
		e.setStyle('display', '');
	});
}
window.addEvent('domready', function(){
	kShowAccessType('kaccess', document.id('accesstype'));
});");
			}
			$accesstypes = array ();
			$accesstypes [] = JHTML::_ ( 'select.option', 'joomla.level', JText::_('COM_KUNENA_INTEGRATION_JOOMLA_LEVEL') );
			$accesstypes [] = JHTML::_ ( 'select.option', 'none', JText::_('COM_KUNENA_INTEGRATION_JOOMLA_GROUP') );
			return JHTML::_ ( 'select.genericlist', $accesstypes, 'accesstype', 'class="inputbox" size="2" onchange="javascript:kShowAccessType(\'kaccess\', document.id(this))"', 'value', 'text', $category->accesstype );
		}
		return JText::_('COM_KUNENA_INTEGRATION_'.strtoupper(preg_replace('/[^\w\d]+/', '_', $category->accesstype)));
	}

	public function getAdmins($catid = 0) {
		return !empty(self::$adminsByCatid[$catid]) ? self::$adminsByCatid[$catid] : array();
	}

	public function getModerators($catid = 0) {
		return !empty(self::$moderatorsByCatid[$catid]) ? self::$moderatorsByCatid[$catid] : array();
	}

	public function getAdminStatus($user = null) {
		$user = KunenaFactory::getUser($user);
		return !empty(self::$adminsByUserid[$user->userid]) ? self::$adminsByUserid[$user->userid] : array();
	}

	public function getModeratorStatus($user = null) {
		$user = KunenaFactory::getUser($user);
		return !empty(self::$moderatorsByUserid[$user->userid]) ? self::$moderatorsByUserid[$user->userid] : array();
	}

	public function isAdmin($user = null, $catid = 0) {
		$user = KunenaFactory::getUser($user);

		// Visitors cannot be administrators
		if (!$user->exists()) return false;

		// In backend every logged in user has global admin rights (for now)
		if (JFactory::getApplication()->isAdmin() && $user->userid == KunenaFactory::getUser()->userid)
			return true;

		// If $catid is not numeric: Is user administrator in ANY category?
		if (!is_numeric($catid)) return !empty(self::$adminsByUserid[$user->userid]);

		// Is user a global administrator?
		if (!empty(self::$adminsByUserid[$user->userid][0])) return true;
		// Is user a category administrator?
		if (!empty(self::$adminsByUserid[$user->userid][$catid])) return true;

		return false;
	}

	public function isModerator($user = null, $catid = 0) {
		$user = KunenaFactory::getUser($user);

		// Visitors cannot be moderators
		if (!$user->exists()) return false;

		// Administrators are always moderators
		if ($this->isAdmin($user, $catid)) return true;

		if (isset(self::$moderatorsByUserid[$user->userid])) {
			// Is user a global moderator?
			if (!empty(self::$moderatorsByUserid[$user->userid][0])) return true;
			// Were we looking only for global moderator?
			if (!is_numeric($catid)) return false;
			// Is user moderator in ANY category?
			if ($catid == 0) return true;
			// Is user a category moderator?
			if (!empty(self::$moderatorsByUserid[$user->userid][$catid])) return true;
		}
		return false;
	}

	/**
	 * Assign user as moderator or resign him
	 *
	 * @example if ($category->authorise('admin')) $category->setModerator($user, true);
	 **/
	public function setModerator($category, $user, $status = true) {
		// Check if category exists
		if (!$category->exists()) return false;

		// Check if user exists
		$user = KunenaUserHelper::get($user);
		if (!$user->exists()) {
			return false;
		}

		$catids = $this->getModeratorStatus ( $user );

		// Do not touch global moderators
		if (!empty($catids[0])) {
			return true;
		}

		// If the user state remains the same, do nothing
		if (!empty($catids[$category->id]) == $status) {
			return true;
		}

		$db = JFactory::getDBO ();
		$success = true;
		if ($status) {
			$query = "INSERT INTO #__kunena_moderation (catid, userid) VALUES ({$db->quote($category->id)}, {$db->quote($user->userid)})";
			$db->setQuery ( $query );
			$db->query ();
			$success = !KunenaError::checkDatabaseError ();
			// Finally set user to be a moderator
			if ($success && $user->moderator == 0) {
				$user->moderator = 1;
				$success = $user->save();
			}
		} else {
			$query = "DELETE FROM #__kunena_moderation WHERE catid={$db->Quote($category->id)} AND userid={$db->Quote($user->userid)}";
			$db->setQuery ( $query );
			$db->query ();
			unset($catids[$category->id]);
			$success = !KunenaError::checkDatabaseError ();
			// Finally check if user looses his moderator status
			if ($success && empty($catids)) {
				$user->moderator = 0;
				$success = $user->save();
			}
		}

		// Clear moderator cache
		$this->clearCache();
		return $success;
	}

	public function getAllowedCategories($user = null, $rule = 'read') {
		static $read = array();

		KUNENA_PROFILER ? KunenaProfiler::instance()->start('function '.__CLASS__.'::'.__FUNCTION__.'()') : null;
		$user = KunenaFactory::getUser($user);
		$id = $user->userid;

		$allowed = array();
		switch ($rule) {
			case 'read':
			case 'post':
			case 'reply':
			case 'edit':
				if (!isset($read[$id])) {
					$app = JFactory::getApplication();
					// TODO: handle guests/bots with no userstate
					$read[$id] = $app->getUserState("com_kunena.user{$id}_read");
					if ($read[$id] === null) {
						$read[$id] = array();
						$categories = KunenaForumCategoryHelper::getCategories(false, false, 'none');
						foreach ( $categories as $category ) {
							if (!$category->published) {
								unset($categories[$category->id]);
							}
							if ($id && self::isModerator($id, $category->id)) {
								$read[$id][$category->id] = $category->id;
								unset($categories[$category->id]);
							}
						}

						$read[$id] += self::$instance->loadAllowedCategories($id, $categories);
						$app->setUserState("com_kunena.user{$id}_read", $read[$id]);
					}
				}
				$allowed = $read[$id];
				break;
			case 'moderate':
				if (isset(self::$moderatorsByUserid[$id])) $allowed += self::$moderatorsByUserid[$id];
				// Continue: Administrators have also moderation permissions
			case 'admin':
				if (isset(self::$adminsByUserid[$id])) $allowed += self::$adminsByUserid[$id];
		}
		KUNENA_PROFILER ? KunenaProfiler::instance()->stop('function '.__CLASS__.'::'.__FUNCTION__.'()') : null;
		return $allowed;
	}

	public function getAllowedHold($user, $catid, $string=true) {
		// hold = 0: normal
		// hold = 1: unapproved
		// hold = 2: deleted
		$user = KunenaFactory::getUser($user);
		$config = KunenaFactory::getConfig ();

		$hold [0] = 0;
		if ($this->isModerator ( $user->userid, $catid )) {
			$hold [1] = 1;
		}
		if (($config->mod_see_deleted == '0' && $this->isAdmin ( $user->userid, $catid ))
			|| ($config->mod_see_deleted == '1' && $this->isModerator( $user->userid, $catid ))) {
			$hold [2] = 2;
			$hold [3] = 3;
	}
		if ($string) $hold = implode ( ',', $hold );
		return $hold;
	}

	public function getSubscribers($catid, $topic, $subscriptions = false, $moderators = false, $admins = false, $excludeList = null) {
		$topic = KunenaForumTopicHelper::get($topic);
		if (!$topic->exists())
			return array();

		if ($subscriptions) {
			$subslist = $this->loadSubscribers($topic, (int)$subscriptions);
		}
		if ($moderators) {
			$modlist = array();
			if (!empty(self::$moderatorsByCatid[0])) $modlist += self::$moderatorsByCatid[0];
			if (!empty(self::$moderatorsByCatid[$catid])) $modlist += self::$moderatorsByCatid[$catid];

			// If category has no moderators, send email to admins instead
			if (empty($modlist)) $admins = true;
		}
		if ($admins) {
			$adminlist = array();
			if (!empty(self::$adminsByCatid[0])) $adminlist += self::$adminsByCatid[0];
			if (!empty(self::$adminsByCatid[$catid])) $adminlist += self::$adminsByCatid[$catid];
		}

		$query = new KunenaDatabaseQuery();
		$query->select('u.id, u.name, u.username, u.email');
		$query->from('#__users AS u');
		$query->where("u.block=0");
		$userlist = array();
		if (!empty($subslist)) {
			$userlist = $subslist;
			$subslist = implode(',', array_keys($subslist));
			$query->select("IF( u.id IN ({$subslist}), 1, 0 ) AS subscription");
		} else {
			$query->select("0 AS subscription");
		}
		if (!empty($modlist)) {
			$userlist += $modlist;
			$modlist = implode(',', array_keys($modlist));
			$query->select("IF( u.id IN ({$modlist}), 1, 0 ) AS moderator");
		} else {
			$query->select("0 AS moderator");
		}
		if (!empty($adminlist)) {
			$userlist += $adminlist;
			$adminlist = implode(',', array_keys($adminlist));
			$query->select("IF( u.id IN ({$adminlist}), 1, 0 ) AS admin");
		} else {
			$query->select("0 AS admin");
		}
		if (empty($excludeList)) {
			// false, null, '', 0 and array(): get all subscribers
			$excludeList = array();
		} elseif (is_array($excludeList)) {
			// array() needs to be flipped to get userids into keys
			$excludeList = array_flip($excludeList);
		} else {
			// Others: let's assume that we have comma separated list of values (string)
			$excludeList = array_flip(explode(',', (string) $excludeList));
		}
		$userlist = array_diff_key($userlist, $excludeList);
		$userids = array();
		if (!empty($userlist)) {
			$userlist = implode(',', array_keys($userlist));
			$query->where("u.id IN ({$userlist})");
			$db = JFactory::getDBO();
			$db->setQuery ( $query );
			$userids = (array) $db->loadObjectList ();
			KunenaError::checkDatabaseError();
		}
		return $userids;
	}

	public function storeAdmins($list = array()) {
		// TODO: add caching
		foreach ( $list as $item ) {
			$userid = intval ( $item->userid );
			if (!$userid) continue;
			$catid = intval ( $item->catid );
			self::$adminsByUserid [$userid] [$catid] = 1;
			self::$adminsByCatid [$catid] [$userid] = 1;
		}
		return $list;
	}

	public function storeModerators($list = array()) {
		// TODO: add caching
		foreach ( $list as $item ) {
			$userid = intval ( $item->userid );
			if (!$userid) continue;
			$catid = intval ( $item->catid );
			self::$moderatorsByUserid [$userid] [$catid] = 1;
			self::$moderatorsByCatid [$catid] [$userid] = 1;
		}
		return $list;
	}

	public function &loadSubscribers($topic, $subsriptions) {
		$category = $topic->getCategory();
		$db = JFactory::getDBO ();
		$query = array();
		if ($subsriptions == 1 || $subsriptions == 2) {
			// Get topic subscriptions
			//FIXME: user topics is missing a column
			$once = false; //KunenaFactory::getConfig()->topic_subscriptions == 'first' ? 'AND future1=0' : '';
			$query[] = "SELECT user_id FROM #__kunena_user_topics WHERE topic_id={$topic->id} AND subscribed=1 {$once}";
		}
		if ($subsriptions == 1 || $subsriptions == 3) {
			// Get category subscriptions
			$query[] = "SELECT user_id FROM #__kunena_user_categories WHERE category_id={$category->id} AND subscribed=1";
		}
		$query = implode(' UNION ', $query);
		$db->setQuery ($query);
		$userids = (array) $db->loadResultArray();
		KunenaError::checkDatabaseError();
		if (!empty($userids)) {
			$this->checkSubscribers($topic, $userids);
		}
		if (!empty($userids)) {
			$userids = (array) array_combine ($userids, $userids);
		}
		return $userids;
	}

	abstract public function getGroupName($accesstype, $id);

	abstract public function checkSubscribers($topic, &$userids);

	abstract public function loadAllowedCategories($userid, &$categories);
}