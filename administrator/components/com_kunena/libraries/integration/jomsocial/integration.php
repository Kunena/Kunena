<?php
/**
 * Kunena Component
 * @package Kunena.Framework
 * @subpackage Integration.JomSocial
 *
 * @copyright (C) 2008 - 2011 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined ( '_JEXEC' ) or die ();

class KunenaIntegrationJomSocial extends KunenaIntegration {
	public function __construct() {
		$path = JPATH_ROOT . '/components/com_community/libraries/core.php';
		if (!is_file ( $path )) return;
		include_once ($path);
		$this->loaded = 1;
	}

	public function enqueueErrors() {
		if (self::GetError ()) {
			$app = JFactory::getApplication ();
			$app->enqueueMessage ( COM_KUNENA_INTEGRATION_JOMSOCIAL_WARN_GENERAL, 'notice' );
			$app->enqueueMessage ( self::$errormsg, 'notice' );
			$app->enqueueMessage ( COM__KUNENA_INTEGRATION_JOMSOCIAL_WARN_HIDE, 'notice' );
		}
	}

	/**
	 * Triggers Jomsocial events
	 *
	 * Current events: profileIntegration=0/1, avatarIntegration=0/1
	 **/
	public function trigger($event, &$params) {
	}
}
