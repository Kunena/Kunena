<?php
/**
 * Kunena Component
 * @package Kunena.Framework
 * @subpackage Template
 *
 * @copyright (C) 2008 - 2011 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined ( '_JEXEC' ) or die ();

jimport('joomla.html.parameter');
jimport('joomla.filesystem.file');

class KunenaParameter extends JParameter {
	public function getXml() {
		return $this->_xml;
	}
}
/**

* Kunena Users Table Class

* Provides access to the #__kunena_users table

*/
class KunenaTemplate extends JObject
{
	// Global for every instance
	protected static $_instances = array();

	public $name = null;
	public $params = null;

	protected $default = 'default';
	protected $smileyPath = array();
	protected $rankPath = array();
	public $topicIcons = array();

	/**
	* Constructor
	*
	* @access	protected
	*/
	public function __construct($name=null) {
		if (!$name) {
			$config = KunenaFactory::getConfig();
			$name = $config->template;
		}
		$xml = KPATH_SITE . "/template/{$name}/template.xml";
		if (!is_readable ( $xml )) {
			$name = 'default';
			$xml = KPATH_SITE . "/template/{$name}/template.xml";
		}
		$this->xml_path = $xml;
		$ini = KPATH_SITE . "/template/{$name}/params.ini";
		$content = '';
		if (is_readable( $ini ) ) {
			$content = file_get_contents($ini);
		}
		$this->name = $name;
		$this->params = new KunenaParameter($content, $xml);

		$xml = $this->params->getXml();
		foreach ($xml['_default']->children() as $param)  {
			if ($param->attributes('type') != 'spacer') $this->params->def($param->attributes('name'), $param->attributes('default'));
		}
	}

	public function initialize() {}

	public function getButton($name, $text) {
		return '<span class="'.$name.'"><span>'.$text.'</span></span>';
	}

	public function getIcon($name, $title='') {
		return '<span class="kicon '.$name.'" title="'.$title.'"></span>';
	}

	public function getImage($image, $alt='') {
		return '<img src="'.$this->getImagePath($image).'" alt="'.$alt.'" />';
	}

	public function getPaginationListFooter($list) {
		$html = '<div class="list-footer">';
		$html .= '<div class="limit">'.JText::_('COM_KUNENA_LIB_HTML_DISPLAY_NUM').' '.$list['limitfield'].'</div>';
		$html .= $list['pageslinks'];
		$html .= '<div class="counter">'.$list['pagescounter'].'</div>';
		$html .= '<input type="hidden" name="' . $list['prefix'] . 'limitstart" value="'.$list['limitstart'].'" />';
		$html .= '</div>';
		return $html;
	}

	public function getPaginationListRender($list) {
		$html = '<ul class="kpagination">';
		$html .= '<li class="page">'.JText::_('COM_KUNENA_PAGE').'</li>';
		$last = 0;
		foreach($list['pages'] as $i=>$page) {
			if ($last+1 != $i) $html .= '<li>...</li>';
			$html .= '<li>'.$page['data'].'</li>';
			$last = $i;
		}
		$html .= '</ul>';
		return $html;
	}

	public function getPaginationItemActive(&$item) {
		return '<a title="'.$item->text.'" href="'.$item->link.'" class="pagenav">'.$item->text.'</a>';
	}

	public function getPaginationItemInactive(&$item) {
		return '<span class="pagenav">'.$item->text.'</span>';
	}

	public function getClass($class, $class_sfx='') {
		return $class.($class_sfx ? " {$class}.{$class_sfx}" : '');
	}

	public function loadMootools() {
		$jversion = new JVersion ();
		if ($jversion->RELEASE == '1.5') {
			jimport ( 'joomla.plugin.helper' );
			$mtupgrade = JPluginHelper::isEnabled ( 'system', 'mtupgrade' );
			if (! $mtupgrade) {
				$app = JFactory::getApplication ();
				if (!class_exists ( 'JHTMLBehavior' )) {
					if (is_dir ( JPATH_PLUGINS . '/system/mtupgrade' )) {
						JHTML::addIncludePath ( JPATH_PLUGINS . '/system/mtupgrade' );
					} else {
						KunenaError::warning ( JText::_('COM_KUNENA_LIB_TEMPLATE_MOOTOOLS_NO_UPGRADE').' '.JText::_('COM_KUNENA_LIB_TEMPLATE_MOOTOOLS_WARNING') );
					}
				}
			}
			JHTML::_ ( 'behavior.mootools' );
			// Get the MooTools version string
			$mtversion = preg_replace('/[^\d\.]/','', JFactory::getApplication()->get('MooToolsVersion'));
			if (version_compare($mtversion, '1.2.4', '<')) {
				KunenaError::warning ( JText::_('COM_KUNENA_LIB_TEMPLATE_MOOTOOLS_LEGACY').' '.JText::_('COM_KUNENA_LIB_TEMPLATE_MOOTOOLS_WARNING') );
			}
		} else {
			// Joomla 1.6+
			JHTML::_ ( 'behavior.framework', true );
		}

		if (JDEBUG || KunenaFactory::getConfig()->debug) {
			// Debugging Mootools issues
			$this->addScript ( 'js/debug.js' );
		}
	}

	/**
	 * Wrapper to addStyleSheet
	 */
	function addStyleSheet($filename) {
		if (!JDEBUG && !KunenaFactory::getConfig ()->debug && !KunenaForum::isSvn ()) {
			// If we are in debug more, make sure we load the unpacked css
			$filename = preg_replace ( '/\.css$/u', '-min.css', $filename );
		}
		return JFactory::getDocument ()->addStyleSheet ( JURI::root(true).'/'.$this->getFile($filename) );
	}

	/**
	 * Wrapper to addScript
	 */
	function addScript($filename) {
		if (!JDEBUG && !KunenaFactory::getConfig ()->debug && !KunenaForum::isSvn ()) {
			// If we are in debug more, make sure we load the unpacked css
			$filename = preg_replace ( '/\.js$/u', '-min.js', $filename );
		}
		return JFactory::getDocument ()->addScript ( JURI::root(true).'/'.$this->getFile($filename) );
	}

	public function getPath($default = false) {
		if ($default) return "template/{$this->default}";
		return "template/{$this->name}";
	}

	public function getFile($file) {
		$path = $this->getPath();
		if (!is_file(KPATH_SITE . "/{$path}/{$file}")) {
			$path = $this->getPath(true);
		}
		return KPATH_COMPONENT_RELATIVE."/{$path}/{$file}";
	}

	public function getSmileyPath($filename='', $url = false) {
		if (!isset($this->smileyPath[$filename])) {
			$path = "{$this->getPath()}/images/emoticons/{$filename}";
			if (($filename && !is_file(KPATH_SITE . "/{$path}")) || !is_dir(KPATH_SITE . "/{$path}")) {
				$path = "{$this->getPath(true)}/images/emoticons/{$filename}";
			}
			$this->smileyPath[$filename] = $path;
		}
		$base = '';
		if ($url) $base = JURI::root(true).'/'.KPATH_COMPONENT_RELATIVE.'/';
		return $base.$this->smileyPath[$filename];
	}

	public function getRankPath($filename='', $url = false) {
		if (!isset($this->rankPath[$filename])) {
			$path = "{$this->getPath()}/images/ranks/{$filename}";
			if (($filename && !is_file(KPATH_SITE . "/{$path}")) || !is_dir(KPATH_SITE . "/{$path}")) {
				$path = "{$this->getPath(true)}/images/ranks/{$filename}";
			}
			$this->rankPath[$filename] = $path;
		}
		$base = '';
		if ($url) $base = JURI::root(true).'/'.KPATH_COMPONENT_RELATIVE.'/';
		return $base.$this->rankPath[$filename];
	}

	public function getImagePath($image, $url = true) {
		$path = $this->getPath();
		if (!is_file(KPATH_SITE . "/{$path}/images/{$image}")) {
			$path = $this->getPath(true);
		}
		$base = '';
		if ($url) $base = JURI::root(true).'/'.KPATH_COMPONENT_RELATIVE.'/';
		return "{$base}{$path}/images/{$image}";
	}

	public function getTopicIcons($all = false) {
		if (empty($this->topicIcons)) {
			$xmlfile = JPATH_ROOT.'/media/kunena/topicicons/default/topicicons.xml';
			if (file_exists($xmlfile)) {
				$xml = simplexml_load_file($xmlfile);
			}
			if (isset($xml->icons)) {
				foreach($xml->icons as $icons) {
					$type = (string) $icons->attributes()->type;
					$width = (int) $icons->attributes()->width;
					$height = (int) $icons->attributes()->height;
					foreach($icons->icon as $icon) {
						$attributes = $icon->attributes();
						$icon = new stdClass();
						$icon->id = (int) $attributes->id;
						$icon->type = (string) $attributes->type ? (string) $attributes->type : $type;
						$icon->name = (string) $attributes->name;
						if ($icon->type != 'user') {
							$icon->id = $icon->type.'_'.$icon->name;
						}
						$icon->published = (int) $attributes->published;
						$icon->title = (string) $attributes->title;
						$icon->filename = (string) $attributes->src;
						$icon->width = (int) $attributes->width ? (int) $attributes->width : $width;
						$icon->height = (int) $attributes->height ? (int) $attributes->height : $height;
						$this->topicIcons[$icon->id] = $icon;
					}
				}
			}
			// Make sure that default icon exists (use user/default.png in current template)
			if (!isset($this->topicIcons[0])) {
				$icon = new StdClass();
				$icon->id = 0;
				$icon->type = 'user';
				$icon->name = 'default';
				$icon->published = 0;
				$icon->title = 'Default';
				$icon->filename = 'default.png';
				$icon->width = 48;
				$icon->height = 48;
				$default = !is_file( KPATH_SITE . "/{$this->getPath()}/images/topicicons/user/{$icon->filename}" );
				$icon->relpath = KPATH_COMPONENT_RELATIVE."/{$this->getPath($default)}/images/topicicons/user/{$icon->filename}";
				$this->topicIcons[0] = $icon;
			}
		}
		if ($all) {
			$icons = $this->topicIcons;
		} else {
			$icons = array();
			foreach ($this->topicIcons as $icon) {
				if ($icon->published && is_numeric($icon->id)) {
					$icons[$icon->id] = $icon;
				}
			}
		}
		return $icons;
	}

	public function getTopicIconPath($index, $url = false) {
		if (empty($this->topicIcons)) {
			$this->getTopicIcons();
		}
		if (empty($this->topicIcons[$index]->published)) {
			$index = 0;
		}
		$icon = $this->topicIcons[$index];
		if (!isset($icon->relpath)) {
			$curpath = $this->getPath();
			$defpath = $this->getPath(true);
			if (is_file( KPATH_SITE . "/{$curpath}/images/topicicons/default/{$icon->filename}" )) {
				$icon->relpath = KPATH_COMPONENT_RELATIVE."/{$curpath}/images/topicicons/default/{$icon->filename}";
			} elseif (is_file( KPATH_SITE . "/{$defpath}/images/topicicons/default/{$icon->filename}" )) {
				$icon->relpath = KPATH_COMPONENT_RELATIVE."/{$defpath}/images/topicicons/default/{$icon->filename}";
			} else {
				$icon->relpath = "media/kunena/topicicons/default/{$icon->filename}";
			}
		}
		return ($url ? JURI::root(true).'/' : '') . $icon->relpath;
	}

	public function getMovedIconPath($url = false) {
		static $moved = false;
		if ($moved === false) {
			$path = $this->getPath();
			if (!is_file(KPATH_SITE . "/{$path}/images/topicicons/user/moved.png")) {
				$path = $this->getPath(true);
			}
			$moved =  "{$path}/images/topicicons/user/moved.png";
		}

		$base = '';
		if ($url) $base = JURI::root(true).'/'.KPATH_COMPONENT_RELATIVE.'/';
		return $base.$moved;
	}

	public function getTopicIcon($topic ) {
		$config = KunenaFactory::getConfig ();
		if ($config->topicicons) {
			if ( $topic->moved_id == 0 ) $iconurl = $this->getTopicIconPath($topic->icon_id, true);
			else $iconurl = $this->getMovedIconPath(true);
		} else {
			$icon = 'normal';
			if ($topic->posts < 2) $icon = 'unanswered';
			if ($topic->ordering) $icon = 'sticky';
			//if ($topic->myfavorite) $icon = 'favorite';
			if ($topic->locked) $icon = 'locked';
			if ($topic->hold == 1) $icon = 'unapproved';
			if ($topic->hold == 2) $icon = 'deleted';
			if ($topic->moved_id) $icon = 'moved';
			if (!empty($topic->unread)) $icon .= '_new';
			$iconurl = $this->getImagePath("topicicons/system/{$icon}.png");
		}
		$html = '<img src="'.$iconurl.'" alt="emo" />';
		return $html;
	}

	// FIXME: remove:
	public function getTopicsIconPath($filename) {
		if ( empty($filename) ) return;

		return "media/kunena/topicicons/{$filename}";
	}

	public function getTemplateDetails() {
		$templatedetails = new stdClass();
		$xml_tmpl = JFactory::getXMLparser('Simple');
		$xml_tmpl->loadFile($this->xml_path);

		$templatedetails->creationDate = $xml_tmpl->document->creationDate[0]->data();
		$templatedetails->author = $xml_tmpl->document->author[0]->data();
		$templatedetails->version = $xml_tmpl->document->version[0]->data();
		$templatedetails->name = $xml_tmpl->document->name[0]->data();

		return $templatedetails;
	}

	static public function loadTemplate($file) {
		$path = self::getInstance()->getPath();
		if (!is_file(KPATH_SITE . "/{$path}/{$file}")) {
			$path = self::getInstance()->getPath(true);
		}
		include KPATH_SITE . "/{$path}/{$file}";
	}

	/**
	 * Returns the global KunenaTemplate object, only creating it if it doesn't already exist.
	 *
	 * @access	public
	 * @param	int	$name		Template name or null for default/selected template in your configuration
	 * @return	KunenaTemplate	The template object.
	 * @since	1.6
	 */
	static public function getInstance($name=null) {
		if (!$name) {
			$config = KunenaFactory::getConfig();
			$name = $config->template;
		}
		if (empty(self::$_instances[$name])) {
			// Find overridden template class
			$classname = "KunenaTemplate{$name}";
			if (!class_exists($classname)) {
				$file = KPATH_SITE."/template/{$name}/template.php";
				if (!file_exists($file)) {
					$classname = "KunenaTemplateDefault";
					$file = KPATH_SITE."/template/default/template.php";
				}
				if (file_exists($file)) {
					require_once $file;
				}
			}
			if (class_exists($classname)) {
				self::$_instances[$name] = new $classname($name);
			} else {
				self::$_instances[$name] = new KunenaTemplate($name);
			}
		}

		return self::$_instances[$name];
	}
}
