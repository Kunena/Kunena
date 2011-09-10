<?php
/**
 * Kunena Component
 * @package Kunena
 *
 * @Copyright (C) 2008 - 2011 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined ( '_JEXEC' ) or die ();

$this->displayAnnouncement ();
$location = 0;
foreach ( $this->sections as $section ) {
	$this->displaySection($section);
	$this->displayModulePosition('kunena_section_' . ++$location);
}
?>