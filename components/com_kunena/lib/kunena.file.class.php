<?php
/**
 * Kunena Component
 * @package Kunena.Site
 * @subpackage Lib
 * @copyright (C) 2011 Kunena All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/

defined( '_JEXEC' ) or die();

jimport ( 'joomla.version' );
$jversion = new JVersion ();
if ($jversion->RELEASE == '1.5') {
	require_once (KPATH_SITE.'/lib/kunena.file.class.1.5.php');
} else {
	require_once (KPATH_SITE.'/lib/kunena.file.class.1.6.php');
}
?>
