<?php
/**
 * Kunena Component
 * @package Kunena.Administrator
 * @subpackage Controllers
 *
 * @copyright (C) 2008 - 2011 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined ( '_JEXEC' ) or die ();

/**
 * Kunena Categories Controller
 *
 * @since 2.0
 */
class KunenaAdminControllerCategories extends KunenaController {
	protected $baseurl = null;
	protected $baseurl2 = null;

	public function __construct($config = array()) {
		parent::__construct($config);
		$this->baseurl = 'index.php?option=com_kunena&view=categories';
		$this->baseurl2 = 'index.php?option=com_kunena&view=categories';
		$this->me = KunenaUserHelper::getMyself();
	}

	function lock() {
		$cid = JRequest::getVar ( 'cid', array (), 'post', 'array' );
		$this->setVariable($cid, 'locked', 1);
		$this->redirectBack();
	}
	function unlock() {
		$cid = JRequest::getVar ( 'cid', array (), 'post', 'array' );
		$this->setVariable($cid, 'locked', 0);
		$this->redirectBack();
	}
	function review() {
		$cid = JRequest::getVar ( 'cid', array (), 'post', 'array' );
		$this->setVariable($cid, 'review', 1);
		$this->redirectBack();
	}
	function unreview() {
		$cid = JRequest::getVar ( 'cid', array (), 'post', 'array' );
		$this->setVariable($cid, 'review', 0);
		$this->redirectBack();
	}
	function allow_anonymous() {
		$cid = JRequest::getVar ( 'cid', array (), 'post', 'array' );
		$this->setVariable($cid, 'allow_anonymous', 1);
		$this->redirectBack();
	}
	function deny_anonymous() {
		$cid = JRequest::getVar ( 'cid', array (), 'post', 'array' );
		$this->setVariable($cid, 'allow_anonymous', 0);
		$this->redirectBack();
	}
	function allow_polls() {
		$cid = JRequest::getVar ( 'cid', array (), 'post', 'array' );
		$this->setVariable($cid, 'allow_polls', 1);
		$this->redirectBack();
	}
	function deny_polls() {
		$cid = JRequest::getVar ( 'cid', array (), 'post', 'array' );
		$this->setVariable($cid, 'allow_polls', 0);
		$this->redirectBack();
	}
	function publish() {
		$cid = JRequest::getVar ( 'cid', array (), 'post', 'array' );
		$this->setVariable($cid, 'published', 1);
		$this->redirectBack();
	}
	function unpublish() {
		$cid = JRequest::getVar ( 'cid', array (), 'post', 'array' );
		$this->setVariable($cid, 'published', 0);
		$this->redirectBack();
	}

	function add() {
		$lang = JFactory::getLanguage();
		$lang->load('com_kunena', JPATH_ADMINISTRATOR);

		$app = JFactory::getApplication ();
		if (! JRequest::checkToken ()) {
			$app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
			$this->redirectBack();
		}

		$cid = JRequest::getVar ( 'cid', array (), 'post', 'array' );
		$id = (int)array_shift($cid);
		$this->setRedirect(KunenaRoute::_($this->baseurl2."&layout=create&catid={$id}", false));
	}

	function edit() {
		$lang = JFactory::getLanguage();
		$lang->load('com_kunena', JPATH_ADMINISTRATOR);

		$app = JFactory::getApplication ();
		if (! JRequest::checkToken ()) {
			$app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
			$this->redirectBack();
		}

		$cid = JRequest::getVar ( 'cid', array (), 'post', 'array' );
		$id = array_shift($cid);
		if (!$id) {
			$app->enqueueMessage ( JText::_ ( 'COM_KUNENA_A_NO_CATEGORIES_SELECTED' ), 'notice' );
			$this->redirectBack();
		} else {
			$this->setRedirect(KunenaRoute::_($this->baseurl2."&layout=edit&catid={$id}", false));
		}
	}

	function apply() {
		$this->_save();
		$this->redirectBack();
	}

	function save2new() {
		$this->_save();
		$this->setRedirect(KunenaRoute::_($this->baseurl2."&layout=create", false));
	}

	function save() {
		$this->_save();
		JFactory::getApplication ()->redirect ( KunenaRoute::_($this->baseurl, false) );
	}

	protected function _save() {
		$lang = JFactory::getLanguage();
		$lang->load('com_kunena', JPATH_ADMINISTRATOR);

		$app = JFactory::getApplication ();
		if (! JRequest::checkToken ()) {
			$app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
			$this->redirectBack();
		}

		$post = JRequest::get('post', JREQUEST_ALLOWRAW);
		$accesstype = JRequest::getCmd('accesstype', 'none');
		$post['access'] = JRequest::getInt("access-{$accesstype}", JRequest::getInt('access', 0));
		$success = false;

		$category = KunenaForumCategoryHelper::get ( intval ( $post ['catid'] ) );

		if ($category->exists() && !$category->authorise ( 'admin' )) {
			// Category exists and user is not admin in category
			$app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_A_CATEGORY_NO_ADMIN', $this->escape ( $category->name ) ), 'notice' );
		} elseif (!$category->exists() && !$this->me->isAdmin ( intval ( $post ['parent_id'] ) )) {
			// Category doesn't exist and user is not admin in parent, parent_id=0 needs global admin rights
			$parent = KunenaForumCategoryHelper::get ( intval ( $post ['parent_id'] ) );
			$app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_A_CATEGORY_NO_ADMIN', $this->escape ( $parent->name ) ), 'notice' );
		} elseif (! $category->isCheckedOut ( $this->me->userid )) {
			// Nobody can change id or statistics
			$ignore = array('option', 'view', 'task', 'catid', 'id', 'id_last_msg','numTopics','numPosts','time_last_msg');
			// User needs to be admin in parent (both new and old) in order to move category, parent_id=0 needs global admin rights
			if (!$this->me->isAdmin ( intval ( $post ['parent_id'] )) || ($category->exists() && !$this->me->isAdmin ( $category->parent_id ))) {
				$ignore = array_merge($ignore, array('parent_id', 'ordering'));
				$post ['parent_id'] = $category->parent_id;
			}
			// Only global admin can change access control and class_sfx (others are inherited from parent)
			if (!$this->me->isAdmin ()) {
				$access = array('accesstype', 'access', 'pub_access', 'pub_recurse', 'admin_access', 'admin_recurse', 'channels', 'class_sfx');
				if (!$category->exists() || intval ($post ['parent_id']) != $category->parent_id) {
					// If category didn't exist or is moved, copy access and class_sfx from parent
					$parent = KunenaForumCategoryHelper::get (intval ( $post ['parent_id']));
					$category->bind($parent->getProperties(), $access, true);
				}
				$ignore = array_merge($ignore, $access);
			}

			$category->bind ( $post, $ignore );

			if (!$category->exists()) {
				$category->ordering = 99999;
			}
			$success = $category->save ();

			// Update read access
			$read = $app->getUserState("com_kunena.user{$this->me->userid}_read");
			$read[$category->id] = $category->id;
			$app->setUserState("com_kunena.user{$this->me->userid}_read", null);

			if (! $success) {
				$app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_A_CATEGORY_SAVE_FAILED', $category->id, $this->escape ( $category->getError () ) ), 'notice' );
			}
			$category->checkin();
		} else {
			$app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_A_CATEGORY_X_CHECKED_OUT', $this->escape ( $category->name ) ), 'notice' );
		}

		if ($success) {
			$app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_A_CATEGORY_SAVED', $this->escape ( $category->name ) ) );
		}

		if (!empty($post['rmmod'])) {
			foreach ((array) $post['rmmod'] as $userid=>$value) {
				$user = KunenaFactory::getUser($userid);
				if ($category->authorise('admin', null, false) && $category->removeModerator($user)) {
					$app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_VIEW_CATEGORY_EDIT_MODERATOR_REMOVED', $this->escape ( $user->getName() ), $this->escape ( $category->name ) ) );
				}
			}
		}
	}

	function remove() {
		$lang = JFactory::getLanguage();
		$lang->load('com_kunena', JPATH_ADMINISTRATOR);

		$app = JFactory::getApplication ();
		if (! JRequest::checkToken ()) {
			$app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
			$this->redirectBack();
		}

		$cid = JRequest::getVar ( 'cid', array (), 'post', 'array' );

		if (empty ( $cid )) {
			$app->enqueueMessage ( JText::_ ( 'COM_KUNENA_A_NO_CATEGORIES_SELECTED' ), 'notice' );
			$this->redirectBack();
		}

		$count = 0;
		$categories = KunenaForumCategoryHelper::getCategories ( $cid );
		foreach ( $categories as $category ) {
			if (!$category->authorise ( 'admin' )) {
				$app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_A_CATEGORY_NO_ADMIN', $this->escape ( $category->name ) ), 'notice' );
			} elseif (! $category->isCheckedOut ( $this->me->userid )) {
				if ($category->delete ()) {
					$count ++;
					$name = $category->name;
				} else {
					$app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_A_CATEGORY_DELETE_FAILED', $this->escape ( $category->getError () ) ), 'notice' );
				}
			} else {
				$app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_A_CATEGORY_X_CHECKED_OUT', $this->escape ( $category->name ) ), 'notice' );
			}
		}

		if ($count == 1)
			$app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_A_CATEGORY_DELETED', $this->escape ( $name ) ) );
		if ($count > 1)
			$app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_A_CATEGORIES_DELETED', $count ) );
		$this->redirectBack();
	}

	function cancel() {
		$lang = JFactory::getLanguage();
		$lang->load('com_kunena', JPATH_ADMINISTRATOR);

		$app = JFactory::getApplication ();
		if (! JRequest::checkToken ()) {
			$app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
			$this->redirectBack();
		}

		$id = JRequest::getInt('catid', 0);

		$category = KunenaForumCategoryHelper::get ( $id );
		if (!$category->authorise ( 'admin' )) {
			$app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_A_CATEGORY_NO_ADMIN', $this->escape ( $category->name ) ), 'notice' );
		} elseif (! $category->isCheckedOut ( $this->me->userid )) {
			$category->checkin ();
		} else {
			$app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_A_CATEGORY_X_CHECKED_OUT', $this->escape ( $category->name ) ), 'notice' );
		}
		$this->redirectBack();
	}

	function saveorder() {
		$lang = JFactory::getLanguage();
		$lang->load('com_kunena', JPATH_ADMINISTRATOR);

		$app = JFactory::getApplication ();
		if (! JRequest::checkToken ()) {
			$app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
			$this->redirectBack();
		}

		$cid = JRequest::getVar ( 'cid', array (), 'post', 'array' );
		$order = JRequest::getVar ( 'order', array (), 'post', 'array' );

		if (empty ( $cid )) {
			$app->enqueueMessage ( JText::_ ( 'COM_KUNENA_A_NO_CATEGORIES_SELECTED' ), 'notice' );
			$this->redirectBack();
		}

		$success = false;

		$categories = KunenaForumCategoryHelper::getCategories ( $cid );
		foreach ( $categories as $category ) {
			if (! isset ( $order [$category->id] ) || $category->get ( 'ordering' ) == $order [$category->id])
				continue;
			if (!$category->parent()->authorise ( 'admin' )) {
				$app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_A_CATEGORY_NO_ADMIN', $this->escape ( $category->getParent()->name ) ), 'notice' );
			} elseif (! $category->isCheckedOut ( $this->me->userid )) {
				$category->set ( 'ordering', $order [$category->id] );
				$success = $category->save ();
				if (! $success) {
					$app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_A_CATEGORY_SAVE_FAILED', $category->id, $this->escape ( $category->getError () ) ), 'notice' );
				}
			} else {
				$app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_A_CATEGORY_X_CHECKED_OUT', $this->escape ( $category->name ) ), 'notice' );
			}
		}

		if ($success) {
			$app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_NEW_ORDERING_SAVED' ) );
		}
		$this->redirectBack();
	}

	function orderup() {
		$cid = JRequest::getVar ( 'cid', array (), 'post', 'array' );
		$this->orderUpDown ( array_shift($cid), -1 );
		$this->redirectBack();
	}

	function orderdown() {
		$cid = JRequest::getVar ( 'cid', array (), 'post', 'array' );
		$this->orderUpDown ( array_shift($cid), 1 );
		$this->redirectBack();
	}

	protected function orderUpDown($id, $direction) {
		$lang = JFactory::getLanguage();
		$lang->load('com_kunena', JPATH_ADMINISTRATOR);

		if (!$id) return;

		$app = JFactory::getApplication ();
		if (! JRequest::checkToken ()) {
			$app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
			return;
		}

		$category = KunenaForumCategoryHelper::get ( $id );
		if (!$category->parent()->authorise ( 'admin' )) {
			$app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_A_CATEGORY_NO_ADMIN', $this->escape ( $category->getParent()->name ) ), 'notice' );
			return;
		}
		if ($category->isCheckedOut ( $this->me->userid )) {
			$app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_A_CATEGORY_X_CHECKED_OUT', $this->escape ( $category->name ) ), 'notice' );
			return;
		}

		$db = JFactory::getDBO ();
		$row = new TableKunenaCategories ( $db );
		$row->load ( $id );

		// Ensure that we have the right ordering
		$where = 'parent_id=' . $db->quote ( $row->parent_id );
		$row->reorder ( $where );
		$row->move ( $direction, $where );
	}

	protected function setVariable($cid, $variable, $value) {
		$lang = JFactory::getLanguage();
		$lang->load('com_kunena', JPATH_ADMINISTRATOR);

		$app = JFactory::getApplication ();
		if (! JRequest::checkToken ()) {
			$app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
			return;
		}
		if (empty ( $cid )) {
			$app->enqueueMessage ( JText::_ ( 'COM_KUNENA_A_NO_CATEGORIES_SELECTED' ), 'notice' );
			return;
		}

		$count = 0;

		$categories = KunenaForumCategoryHelper::getCategories ( $cid );
		foreach ( $categories as $category ) {
			if ($category->get ( $variable ) == $value)
				continue;
			if (!$category->authorise ( 'admin' )) {
				$app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_A_CATEGORY_NO_ADMIN', $this->escape ( $category->name ) ), 'notice' );
			} elseif (! $category->isCheckedOut ( $this->me->userid )) {
				$category->set ( $variable, $value );
				if ($category->save ()) {
					$count ++;
					$name = $category->name;
				} else {
					$app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_A_CATEGORY_SAVE_FAILED', $category->id, $this->escape ( $category->getError () ) ), 'notice' );
				}
			} else {
				$app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_A_CATEGORY_X_CHECKED_OUT', $this->escape ( $category->name ) ), 'notice' );
			}
		}

		if ($count == 1)
			$app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_A_CATEGORY_SAVED', $this->escape ( $name ) ) );
		if ($count > 1)
			$app->enqueueMessage ( JText::sprintf ( 'COM_KUNENA_A_CATEGORIES_SAVED', $count ) );
	}
}