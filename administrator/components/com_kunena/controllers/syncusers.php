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
 * Kunena Syncusers Controller
 *
 * @since 2.0
 */
class KunenaAdminControllerSyncusers extends KunenaController {
	protected $baseurl = null;

	public function __construct($config = array()) {
		parent::__construct($config);
		$this->baseurl = 'index.php?option=com_kunena&view=syncusers';
	}

	function sync() {
		// FIXME: remove option:
		$usercache = JRequest::getBool ( 'usercache', 0 );
		$useradd = JRequest::getBool ( 'useradd', 0 );
		$userdel = JRequest::getBool ( 'userdel', 0 );
		$userrename = JRequest::getBool ( 'userrename', 0 );

		$app = JFactory::getApplication ();
		$db = JFactory::getDBO ();
		if (!JRequest::checkToken()) {
			$app->enqueueMessage ( JText::_ ( 'COM_KUNENA_ERROR_TOKEN' ), 'error' );
			$this->setRedirect(KunenaRoute::_($this->baseurl, false));
			return;
		}

		if ($useradd) {
			$db->setQuery ( "INSERT INTO #__kunena_users (userid) SELECT a.id FROM #__users AS a LEFT JOIN #__kunena_users AS b ON b.userid=a.id WHERE b.userid IS NULL" );
			$db->query ();
			if (KunenaError::checkDatabaseError()) return;
			$app->enqueueMessage ( JText::_('COM_KUNENA_SYNC_USERS_DO_ADD') . ' ' . $db->getAffectedRows () );
		}
		if ($userdel) {
			$db->setQuery ( "DELETE a FROM #__kunena_users AS a LEFT JOIN #__users AS b ON a.userid=b.id WHERE b.username IS NULL" );
			$db->query ();
			if (KunenaError::checkDatabaseError()) return;
			$app->enqueueMessage ( JText::_('COM_KUNENA_SYNC_USERS_DO_DEL') . ' ' . $db->getAffectedRows () );
		}
		if ($userrename) {
			$model = $this->getModel('Syncusers');
			$cnt = $model->KupdateNameInfo ();
			$app->enqueueMessage ( JText::_('COM_KUNENA_SYNC_USERS_DO_RENAME') . " $cnt" );
		}

		$this->setRedirect(KunenaRoute::_($this->baseurl, false));
	}

}
