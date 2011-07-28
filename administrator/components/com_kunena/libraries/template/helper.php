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

kimport('kunena.error');

/**
 * Kunena Template Helper Class
 */
class KunenaTemplateHelper {
	protected static $_instances = array ();

	private function __construct() {}

	public static function isDefault($template) {
		$config = KunenaFactory::getConfig ();
		$defaultemplate = $config->template;
		return $defaultemplate == $template ? 1 : 0;
	}

	public static function parseXmlFiles($templateBaseDir) {
		// Read the template folder to find templates
		jimport('joomla.filesystem.folder');
		$templateDirs = JFolder::folders($templateBaseDir);
		$rows = array();
		// Check that the directory contains an xml file
		foreach ($templateDirs as $templateDir)
		{
			if(!$data = self::parseXmlFile($templateBaseDir, $templateDir)){
				continue;
			} else {
				$rows[] = $data;
			}
		}
		return $rows;
	}

	function parseXmlFile($templateBaseDir, $templateDir) {
		// Check if the xml file exists
		if(!is_file($templateBaseDir.'/'.$templateDir.'/template.xml')) {
			return false;
		}
		$data = self::parseKunenaInstallFile($templateBaseDir.'/'.$templateDir.'/template.xml');
		if ($data->type != 'kunena-template') {
			return false;
		}
		$data->directory = basename($templateDir);
		return $data;
	}

	function parseKunenaInstallFile($path) {
		// FIXME : deprecated under Joomla! 1.6
		$xml = JFactory::getXMLParser ( 'Simple' );
		if (! $xml->loadFile ( $path )) {
			unset ( $xml );
			return false;
		}
		if (! is_object ( $xml->document ) || ($xml->document->name () != 'kinstall')) {
			unset ( $xml );
			return false;
		}

		$data = new stdClass ();
		$element = & $xml->document->name [0];
		$data->name = $element ? $element->data () : '';
		$data->type = $element ? $xml->document->attributes ( "type" ) : '';

		$element = & $xml->document->creationDate [0];
		$data->creationdate = $element ? $element->data () : JText::_ ( 'Unknown' );

		$element = & $xml->document->author [0];
		$data->author = $element ? $element->data () : JText::_ ( 'Unknown' );

		$element = & $xml->document->copyright [0];
		$data->copyright = $element ? $element->data () : '';

		$element = & $xml->document->authorEmail [0];
		$data->authorEmail = $element ? $element->data () : '';

		$element = & $xml->document->authorUrl [0];
		$data->authorUrl = $element ? $element->data () : '';

		$element = & $xml->document->version [0];
		$data->version = $element ? $element->data () : '';

		$element = & $xml->document->description [0];
		$data->description = $element ? $element->data () : '';

		$element = & $xml->document->thumbnail [0];
		$data->thumbnail = $element ? $element->data () : '';

		return $data;
	}
}