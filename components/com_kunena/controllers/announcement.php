<?php
/**
 * Kunena Component
 * @package Kunena.Site
 * @subpackage Controllers
 *
 * @copyright (C) 2008 - 2011 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined ( '_JEXEC' ) or die ();

/**
 * Kunena Announcements Controller
 *
 * @since		2.0
 */
class KunenaControllerAnnouncement extends KunenaController {
	public function __construct($config = array()) {
		$this->db = JFactory::getDBO ();
		parent::__construct($config);
	}

	public function edit() {
		require_once KPATH_SITE . '/lib/kunena.link.class.php';
		$app = JFactory::getApplication ();
		if (! JRequest::checkToken ()) {
			$app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
			$this->redirectBack ();
		}

		$model = $this->getModel('announcement');
		$this->canEdit = $model->getCanEdit();
		if (! $this->canEdit) {
			$app->enqueueMessage ( JText::_ ( 'COM_KUNENA_POST_NOT_MODERATOR' ), 'error' );
			$this->redirectBack ();
		}

		$model->edit();
		if (1) {
			$id = JRequest::getInt ( 'id', 0 );
			$app->enqueueMessage ( JText::_ ( $id ? 'COM_KUNENA_ANN_SUCCESS_EDIT' : 'COM_KUNENA_ANN_SUCCESS_ADD' ) );
		}
		$this->setRedirect (CKunenaLink::GetAnnouncementURL ( 'show', false, false ));
	}

	public function delete($id) {
		$app = JFactory::getApplication ();
		if (! JRequest::checkToken ('get')) {
			$app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
			$this->redirectBack ();
		}

		$model = $this->getModel('announcement');
		$this->canEdit = $model->getCanEdit();
		if (! $this->canEdit) {
			$app->enqueueMessage ( JText::_ ( 'COM_KUNENA_POST_NOT_MODERATOR' ), 'error' );
			$this->redirectBack ();
		}
		$model->delete();
		if (1) {
			$app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ANN_DELETED' ) );
		}
		$this->redirectBack ();
	}

	public function publish() {
		$app = JFactory::getApplication ();
		if (! JRequest::checkToken ('get')) {
			$app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
			$this->redirectBack ();
		}

	}

	public function unpublish() {
		$app = JFactory::getApplication ();
		if (! JRequest::checkToken ('get')) {
			$app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
			$this->redirectBack ();
		}
	}
}