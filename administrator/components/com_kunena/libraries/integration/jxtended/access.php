<?php
/**
 * Kunena Component
 * @package Kunena.Framework
 * @subpackage Integration.JXtended
 *
 * @copyright (C) 2008 - 2011 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined ( '_JEXEC' ) or die ();

require_once KPATH_ADMIN . '/libraries/integration/joomla15/access.php';

class KunenaAccessJXtended extends KunenaAccessJoomla15 {
	public function __construct() {
		$jversion = new JVersion ();
		if ($jversion->RELEASE != '1.5')
			return null;

		$loader = JPATH_ADMINISTRATOR . '/components/com_artofuser/libraries/loader.php';
		if (is_file($loader)) {
			require_once $loader;
		}
		if (!function_exists('juimport') || !function_exists('jximport'))
			return null;

		$this->priority = 40;
	}
}