<?php
/**
 * Kunena Component
 * @package Kunena.Administrator
 * @subpackage Views
 *
 * @copyright (C) 2008 - 2011 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined ( '_JEXEC' ) or die ();

/**
 * About view for Kunena config backend
 */
class KunenaAdminViewConfig extends KunenaView {
	function displayDefault() {
		$this->setToolBarDefault();
		$this->lists = $this->get('Configlists');

		$joomlaemail = $this->get('Joomlaemail');
		if ( !empty($joomlaemail) && $this->config->email == 'change@me.com' ) {
			$this->joomlaemail = $joomlaemail;
		}

		$this->display ();
	}

	protected function setToolBarDefault() {
		JToolBarHelper::title ( '&nbsp;', 'kunena.png' );
		JToolBarHelper::spacer();
		JToolBarHelper::apply();
		JToolBarHelper::spacer();
		JToolBarHelper::save('save');
		JToolBarHelper::spacer();
		JToolBarHelper::custom('setdefault', 'restore.png','restore_f2.png', 'COM_KUNENA_RESET_CONFIG', false);
		JToolBarHelper::spacer();
		JToolBarHelper::cancel('config');
	}
}
