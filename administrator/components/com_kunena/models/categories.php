<?php
/**
 * Kunena Component
 * @package Kunena.Administrator
 * @subpackage Models
 *
 * @copyright (C) 2008 - 2012 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined ( '_JEXEC' ) or die ();

jimport ( 'joomla.application.component.model' );
jimport( 'joomla.html.pagination' );

/**
 * Categories Model for Kunena
 *
 * @since 2.0
 */
class KunenaAdminModelCategories extends KunenaModel {
	protected $__state_set = false;
	protected $_admincategories = false;
	protected $_admincategory = false;

	/**
	 * Method to auto-populate the model state.
	 *
	 * @return	void
	 * @since	1.6
	 */
	protected function populateState() {
		// List state information
		$value = $this->getUserStateFromRequest ( "com_kunena.admin.categories.list.limit", 'limit', $this->app->getCfg ( 'list_limit' ), 'int' );
		$this->setState ( 'list.limit', $value );

		$value = $this->getUserStateFromRequest ( 'com_kunena.admin.categories.list.ordering', 'filter_order', 'ordering', 'cmd' );
		$this->setState ( 'list.ordering', $value );

		$value = $this->getUserStateFromRequest ( "com_kunena.admin.categories.list.start", 'limitstart', 0, 'int' );
		$this->setState ( 'list.start', $value );

		$value = $this->getUserStateFromRequest ( 'com_kunena.admin.categories.list.direction', 'filter_order_Dir', 'asc', 'word' );
		if ($value != 'asc')
			$value = 'desc';
		$this->setState ( 'list.direction', $value );

		$value = $this->getUserStateFromRequest ( 'com_kunena.admin.categories.list.search', 'filter_search', '', 'string' );
		$this->setState ( 'list.search', $value );

		$filterTitle = $this->getUserStateFromRequest ( 'com_kunena.admin.categories.list.filter_title', 'filter_title', '', 'string' );
		$this->setState ( 'list.filter_title', $filterTitle );

		$filterType = $this->getUserStateFromRequest ( 'com_kunena.admin.categories.list.filter_type', 'filter_type', '', 'string' );
		$this->setState ( 'list.filter_type', $filterType );

		$filterAccess = $this->getUserStateFromRequest ( 'com_kunena.admin.categories.list.filter_access', 'filter_access', '', 'string' );
		$this->setState ( 'list.filter_access', $filterAccess );

		$filterLocked = $this->getUserStateFromRequest ( 'com_kunena.admin.categories.list.filter_locked', 'filter_locked', '', 'string' );
		$this->setState ( 'list.filter_locked', $filterLocked );

		$filterReview = $this->getUserStateFromRequest ( 'com_kunena.admin.categories.list.filter_review', 'filter_review', '', 'string' );
		$this->setState ( 'list.filter_review', $filterReview );

		$filterAnonymous = $this->getUserStateFromRequest ( 'com_kunena.admin.categories.list.filter_anonymous', 'filter_anonymous', '', 'string' );
		$this->setState ( 'list.filter_anonymous', $filterAnonymous );

		$value = $this->getUserStateFromRequest ( "com_kunena.admin.categories.list.levels", 'levellimit', 10, 'int' );
		$this->setState ( 'list.levels', $value );

		$catid = $this->getInt ( 'catid', 0 );
		$layout = $this->getWord ( 'layout', 'edit' );
		$parent_id = 0;
		if ($layout == 'create') {
			$parent_id = $catid;
			$catid = 0;
		}
		$this->setState ( 'item.id', $catid );
		$this->setState ( 'item.parent_id', $parent_id );

		$access = $this->getUserStateFromRequest('com_kunena.admin.categories.filter.access', 'filter_access', 0, 'int');
		$this->setState('filter.access', $access);

		$published = $this->getUserStateFromRequest('com_kunena.admin.categories.jgrid.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$type = $this->getUserStateFromRequest('com_kunena.admin.categories.filter.type', 'filter_type', '');
		$this->setState('filter.type', $type);
	}

	public function getAdminCategories() {
		if ( $this->_admincategories === false ) {
			$type=$this->getState('filter.type');
			$getparents = true;
			if ( $type ) $getparents = false;

			$params = array (
				'ordering'=>$this->getState ( 'list.ordering' ),
				'direction'=>$this->getState ( 'list.direction' ) == 'asc' ? 1 : -1,
				'search'=>$this->getState ( 'list.search' ),
				'unpublished'=>1,
				'action'=>'admin',
				'parents'=>$getparents);
			$catid = $this->getState ( 'item.id', 0 );
			$categories = array();
			$orphans = array();

			if ( $this->getState('filter.access') != 0 ) {
				$categories = KunenaForumCategoryHelper::getCategoriesByAccess('joomla.level',$this->getState('filter.access'));
			} else {
				if ($catid) {
					$categories = KunenaForumCategoryHelper::getParents($catid, $this->getState ( 'list.levels' ), array('unpublished'=>1, 'action'=>'none'));
					$categories[] = KunenaForumCategoryHelper::get($catid);
				} else {
					$orphans = KunenaForumCategoryHelper::getOrphaned($this->getState ( 'list.levels' ), $params);
 				}

				$categories = array_merge($categories, KunenaForumCategoryHelper::getChildren($catid, $this->getState ( 'list.levels' ), $params));
				$categories = array_merge($orphans, $categories);
			}

			$published = $this->getState('filter.published');

			$getcategories=0;
			if ( $type== 2 ) $getcategories=1;

			if ( !empty($published)  || $getcategories ) {
				$list = array ();
				foreach($categories as $cat) {

					if ( $this->getState('filter.published') == $cat->published ) $list[] = $cat;
					if ($getcategories && $cat->parent_id > 0 ) $list[] = $cat;
				}
				$categories = $list;
			}

			$categories = KunenaForumCategoryHelper::getIndentation($categories);
			$this->setState ( 'list.total', count($categories) );
			if ($this->getState ( 'list.limit' )) $this->_admincategories = array_slice ( $categories, $this->getState ( 'list.start' ), $this->getState ( 'list.limit' ) );
			else $this->_admincategories = $categories;
			$admin = 0;
			$acl = KunenaAccess::getInstance();
			foreach ($this->_admincategories as $category) {
				$parent = $category->getParent();
				$siblings = array_keys(KunenaForumCategoryHelper::getCategoryTree($category->parent_id));
				$category->up = $this->me->isAdmin($parent) && reset($siblings) != $category->id;
				$category->down = $this->me->isAdmin($parent) && end($siblings) != $category->id;
				$category->reorder = $this->me->isAdmin($parent);
				// FIXME: stop creating access names manually
				if ($category->accesstype == 'joomla.level') {
					$groupname = $acl->getGroupName($category->accesstype, $category->access);
					$category->accessname = JText::_('COM_KUNENA_INTEGRATION_JOOMLA_LEVEL').': '.($groupname ? $groupname : JText::_('COM_KUNENA_NOBODY'));
				} elseif ($category->accesstype != 'joomla.group') {
					$category->accessname = JText::_('COM_KUNENA_INTEGRATION_TYPE_'.strtoupper(preg_replace('/[^\w\d]+/', '_', $category->accesstype))).': '.$acl->getGroupName($category->accesstype, $category->access);
				} else {
					$groupname = $acl->getGroupName($category->accesstype, $category->pub_access);
					$category->accessname = JText::sprintf( $category->pub_recurse ? 'COM_KUNENA_A_GROUP_X_PLUS' : 'COM_KUNENA_A_GROUP_X_ONLY', $groupname ? JText::_( $groupname ) : JText::_('COM_KUNENA_NOBODY') );
					$groupname = $acl->getGroupName($category->accesstype, $category->admin_access);
					if ($groupname && $category->pub_access != $category->admin_access) {
						$category->accessname .= ' / '.JText::sprintf( $category->admin_recurse ? 'COM_KUNENA_A_GROUP_X_PLUS' : 'COM_KUNENA_A_GROUP_X_ONLY', JText::_( $groupname ));
					}
				}
				if ($category->accesstype != 'joomla.group') {
					$category->admin_group = '';
				} else {
					$category->admin_group = JText::_ ( $acl->getGroupName($category->accesstype, $category->admin_access ));
				}
				if ($this->me->isAdmin($category) && $category->isCheckedOut(0)) {
					$category->editor = KunenaFactory::getUser($category->checked_out)->getName();
				} else {
					$category->checked_out = 0;
					$category->editor = '';
				}
				$admin += $this->me->isAdmin($category);
			}
			$this->setState ( 'list.count.admin', $admin );
		}
		if (!empty($orphans)) {
			$this->app->enqueueMessage ( JText::_ ( 'COM_KUNENA_CATEGORY_ORPHAN_DESC' ), 'notice' );
		}
		return $this->_admincategories;
	}

	public function getAdminNavigation() {
		$navigation = new JPagination ($this->getState ( 'list.total'), $this->getState ( 'list.start'), $this->getState ( 'list.limit') );
		return $navigation;
	}

	public function getAdminCategory() {
		$category = KunenaForumCategoryHelper::get ( $this->getState ( 'item.id' ) );
		if (!$this->me->isAdmin($category)) {
			return false;
		}
		if ($this->_admincategory === false) {
			if ($category->exists ()) {
				if (!$category->isCheckedOut ( $this->me->userid ))
					$category->checkout ( $this->me->userid );
			} else {
				// New category is by default child of the first section -- this will help new users to do it right
				$db = JFactory::getDBO ();
				$db->setQuery ( "SELECT a.id, a.name FROM #__kunena_categories AS a WHERE parent_id='0' AND id!='$category->id' ORDER BY ordering" );
				$sections = $db->loadObjectList ();
				KunenaError::checkDatabaseError ();
				$category->parent_id = $this->getState ( 'item.parent_id' );
				$category->published = 0;
				$category->ordering = 9999;
				$category->pub_recurse = 1;
				$category->admin_recurse = 1;
				$category->accesstype = 'joomla.level';
				$category->access = 1;
				$category->pub_access = 1;
				$category->admin_access = 8;

			}
			$this->_admincategory = $category;
		}
		return $this->_admincategory;
	}

	public function getAdminOptions() {
		$category = $this->getAdminCategory();
		if (!$category) return false;

		$catList = array ();
		$catList [] = JHtml::_ ( 'select.option', 0, JText::_ ( 'COM_KUNENA_TOPLEVEL' ) );

		// make a standard yes/no list
		$published = array ();
		$published [] = JHtml::_ ( 'select.option', 1, JText::_ ( 'COM_KUNENA_PUBLISHED' ) );
		$published [] = JHtml::_ ( 'select.option', 0, JText::_ ( 'COM_KUNENA_UNPUBLISHED' ) );

		// make a standard yes/no list
		$yesno = array ();
		$yesno [] = JHtml::_ ( 'select.option', 0, JText::_ ( 'COM_KUNENA_NO' ) );
		$yesno [] = JHtml::_ ( 'select.option', 1, JText::_ ( 'COM_KUNENA_YES' ) );

		// Anonymous posts default
		$post_anonymous = array ();
		$post_anonymous [] = JHtml::_ ( 'select.option', '0', JText::_ ( 'COM_KUNENA_CATEGORY_ANONYMOUS_X_REG' ) );
		$post_anonymous [] = JHtml::_ ( 'select.option', '1', JText::_ ( 'COM_KUNENA_CATEGORY_ANONYMOUS_X_ANO' ) );

		$cat_params = array ();
		$cat_params['ordering'] = 'ordering';
		$cat_params['toplevel'] = JText::_('COM_KUNENA_TOPLEVEL');
		$cat_params['sections'] = 1;
		$cat_params['unpublished'] = 1;
		$cat_params['catid'] = $category->id;
		$cat_params['action'] = 'admin';

		$channels_params = array();
		$channels_params['catid'] = $category->id;
		$channels_params['action'] = 'admin';
		$channels_options = array();
		$channels_options [] = JHtml::_ ( 'select.option', 'THIS', JText::_ ( 'COM_KUNENA_CATEGORY_CHANNELS_OPTION_THIS' ) );
		$channels_options [] = JHtml::_ ( 'select.option', 'CHILDREN', JText::_ ( 'COM_KUNENA_CATEGORY_CHANNELS_OPTION_CHILDREN' ) );
		if (empty($category->channels)) $category->channels = 'THIS';

		$topic_ordering_options = array();
		$topic_ordering_options[] = JHtml::_ ( 'select.option', 'lastpost', JText::_ ( 'COM_KUNENA_CATEGORY_TOPIC_ORDERING_OPTION_LASTPOST' ) );
		$topic_ordering_options[] = JHtml::_ ( 'select.option', 'creation', JText::_ ( 'COM_KUNENA_CATEGORY_TOPIC_ORDERING_OPTION_CREATION' ) );
		$topic_ordering_options[] = JHtml::_ ( 'select.option', 'alpha', JText::_ ( 'COM_KUNENA_CATEGORY_TOPIC_ORDERING_OPTION_ALPHA' ) );

		$aliases = array_keys($category->getAliases());

		$lists = array ();
		$lists ['accesstypes'] = KunenaAccess::getInstance()->getAccessTypesList($category);
		$lists ['accesslists'] = KunenaAccess::getInstance()->getAccessOptions($category);
		$lists ['categories'] = JHtml::_('kunenaforum.categorylist', 'parent_id', 0, null, $cat_params, 'class="inputbox"', 'value', 'text', $category->parent_id);
		$lists ['channels'] = JHtml::_('kunenaforum.categorylist', 'channels[]', 0, $channels_options, $channels_params, 'class="inputbox" multiple="multiple"', 'value', 'text', explode(',', $category->channels));
		$lists ['aliases'] = $aliases ? JHtml::_ ( 'kunenaforum.checklist', 'aliases', $aliases, true) : null;
		$lists ['published'] = JHtml::_ ( 'select.genericlist', $published, 'published', 'class="inputbox"', 'value', 'text', $category->published );
		$lists ['forumLocked'] = JHtml::_ ( 'select.genericlist', $yesno, 'locked', 'class="inputbox" size="1"', 'value', 'text', $category->locked );
		$lists ['forumReview'] = JHtml::_ ( 'select.genericlist', $yesno, 'review', 'class="inputbox" size="1"', 'value', 'text', $category->review );
		$lists ['allow_polls'] = JHtml::_ ( 'select.genericlist', $yesno, 'allow_polls', 'class="inputbox" size="1"', 'value', 'text', $category->allow_polls );
		$lists ['allow_anonymous'] = JHtml::_ ( 'select.genericlist', $yesno, 'allow_anonymous', 'class="inputbox" size="1"', 'value', 'text', $category->allow_anonymous );
		$lists ['post_anonymous'] = JHtml::_ ( 'select.genericlist', $post_anonymous, 'post_anonymous', 'class="inputbox" size="1"', 'value', 'text', $category->post_anonymous );
		$lists ['topic_ordering'] = JHtml::_ ( 'select.genericlist', $topic_ordering_options, 'topic_ordering', 'class="inputbox" size="1"', 'value', 'text', $category->topic_ordering );

		// TODO:
		/*
		$topicicons = array ();
		jimport( 'joomla.filesystem.folder' );
		$topiciconslist = JFolder::folders(JPATH_ROOT.'/media/kunena/topicicons');
		foreach( $topiciconslist as $icon ) {
			$topicicons[] = JHtml::_ ( 'select.option', $icon, $icon );
		}
		$lists ['category_iconset'] = JHtml::_ ( 'select.genericlist', $topicicons, 'iconset', 'class="inputbox" size="1"', 'value', 'text', $category->iconset );
		*/

		return $lists;
	}

	function getAdminModerators() {
		$category = $this->getAdminCategory();
		if (!$category) return false;

		$moderators = $category->getModerators(false);
		return $moderators;
	}

	/**
	 * Method to get users to be moderators in a specific category
	 *
	 * @return	html list
	 * @since	3.0
	 */
	function getUserNotModerators() {
		$cat_id = (int)$this->getState('item.id');

		$db = JFactory::getDBO ();
		$query = "SELECT u.username, ku.userid
			FROM #__kunena_users AS ku
			INNER JOIN #__users AS u ON ku.userid=u.id
			LEFT JOIN #__kunena_user_categories AS ucat ON ucat.user_id=ku.userid
			WHERE ucat.category_id!='0' AND ucat.category_id!={$db->quote($cat_id)}";
		$db->setQuery ( $query );
		$modCatList = $db->loadObjectList ();
		KunenaError::checkDatabaseError ();

		if ( !empty($modCatList) ) {
			$userid = array();
			$userid[] = JHtml::_ ( 'select.option', 0, JText::_('COM_KUNENA_CATEGORY_SELECT_MODERATORS') );

			foreach ($modCatList as $mod) {
				$userid[] = JHtml::_ ( 'select.option', $mod->userid, $mod->username );
			}

			$modCats  = JHtml::_ ( 'select.genericlist', $userid, 'mod_userid[]', 'class="inputbox" multiple="multiple" size="15"', 'value', 'text', 0 );

			return $modCats;
		} else {
			return;
		}
	}
}