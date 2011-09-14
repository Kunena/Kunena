<?php
/**
 * Kunena Component
 * @package Kunena.Administrator
 * @subpackage Models
 *
 * @copyright (C) 2008 - 2011 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined ( '_JEXEC' ) or die ();

jimport ( 'joomla.application.component.model' );

/**
 * Prune Model for Kunena
 *
 * @since 2.0
 */
class KunenaAdminModelPrune extends KunenaModel {
	function getForumlist() {
		$cat_params = array ();
		$cat_params['ordering'] = 'ordering';
		$cat_params['toplevel'] = 0;
		$cat_params['sections'] = 0;
		$cat_params['direction'] = 1;
		$cat_params['unpublished'] = 1;
		$cat_params['action'] = 'admin';

		$forum = JHTML::_('kunenaforum.categorylist', 'prune_forum', 0, null, $cat_params, 'class="inputbox" multiple="multiple"', 'value', 'text');
		return $forum;
	}

	function getListtrashdelete() {
		$trashdelete = array();
		$trashdelete [] = JHTML::_ ( 'select.option', '0', JText::_('COM_KUNENA_TRASH_USERMESSAGES') );
		$trashdelete [] = JHTML::_ ( 'select.option', '1', JText::_('COM_KUNENA_GEN_DELETE') );

		return JHTML::_('select.genericlist', $trashdelete, 'trashdelete', 'class="inputbox" size="1"', 'value', 'text', 0);
	}
}
