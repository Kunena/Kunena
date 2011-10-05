<?php
/**
 * Kunena Component
 * @package Kunena.Site
 *
 * @copyright (C) 2008 - 2011 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined ( '_JEXEC' ) or die ();

require_once JPATH_ADMINISTRATOR . '/components/com_kunena/api.php';

jimport('joomla.filter.output');
jimport('joomla.error.profiler');

class KunenaRouter {
	static $config = null;

	static $time = 0;
	static $catidcache = null;

	// List of reserved views (if category name is one of these, use always catid)
	// Contains array of default variable=>value pairs, which can be removed from URI
	static $views = array (
		'home'=>array(),
		'category'=>array('layout'=>'default', 'catid'=>'0'),
		'common'=>array('layout'=>'default'),
		'credits'=>array('layout'=>'default'),
		'search'=>array('layout'=>'default'),
		'statistics'=>array('layout'=>'default'),
		'topic'=>array('layout'=>'default'),
		'topics'=>array('layout'=>'default'),
		'user'=>array('layout'=>'default', 'userid'=>'0'),
		'users'=>array('layout'=>'default'),
		'misc'=>array('layout'=>'default'),
	);
	// Reserved layout names for category view
	static $layouts = array ('create'=>1, 'default'=>1, 'edit'=>1, 'manage'=>1, 'moderate'=>1, 'user'=>1);
	// Use category name only in these views
	static $sefviews = array (''=>1, 'home'=>1, 'category'=>1, 'topic'=>1);
	static $parsevars = array ('do'=>1, 'task'=>1, 'mode'=>1, 'catid'=>1, 'id'=>1, 'mesid'=>1, 'userid'=>1, 'page'=>1, 'sel'=>1 );
	// List of legacy views from previous releases
	static $functions = array (
		'listcat'=>1,
		'showcat'=>1,
		'latest'=>1,
		'mylatest'=>1,
		'noreplies'=>1,
		'subscriptions'=>1,
		'favorites'=>1,
		'userposts'=>1,
		'unapproved'=>1,
		'deleted'=>1,
		'view'=>1,
		'profile'=>1,
		'myprofile'=>1,
		'userprofile'=>1,
		'fbprofile'=>1,
		'moderateuser'=>1,
		'userlist'=>1,
		'rss'=>1,
		'post'=>1,
		'report'=>1,
		'template'=>1,

		'announcement'=>1,
		'article'=>1,
		'who'=>1,
		'poll'=>1,
		'polls'=>1,
		'stats'=>1,
		'help'=>1,
		'review'=>1,
		'rules'=>1,
		'search'=>1,
		'advsearch'=>1,
		'markallcatsread'=>1,
		'markthisread'=>1,
		'subscribecat'=>1,
		'unsubscribecat'=>1,
		'karma'=>1,
		'bulkactions'=>1,
		'templatechooser'=>1,
		'json'=>1,
		'pdf'=>1,
		'entrypage'=>1,
		'thankyou'=>1,
		'fb_pdf'=>1,
	);

	function initialize() {
		self::$config = KunenaFactory::getConfig ();
	}

	function loadCategories() {
		KUNENA_PROFILER ? KunenaProfiler::instance()->start('function '.__CLASS__.'::'.__FUNCTION__.'()') : null;
		$categories = KunenaForumCategoryHelper::getCategories();
		self::$catidcache = array();
		foreach ($categories as $id=>$category) {
			self::$catidcache[$id] = self::stringURLSafe ( $category->name );
		}
		KUNENA_PROFILER ? KunenaProfiler::instance()->stop('function '.__CLASS__.'::'.__FUNCTION__.'()') : null;
	}

	function isCategoryConflict($menuitem, $catid, $catname) {
		KUNENA_PROFILER ? KunenaProfiler::instance()->start('function '.__CLASS__.'::'.__FUNCTION__.'()') : null;
		if (isset ( self::$views[$catname] ) || isset ( self::$layouts[$catname] ) || isset ( self::$functions[$catname] ) ) {
			KUNENA_PROFILER ? KunenaProfiler::instance()->stop('function '.__CLASS__.'::'.__FUNCTION__.'()') : null;
			return true;
		}
		if (self::$catidcache === null) {
			self::loadCategories ();
		}
		$keys = array_keys(self::$catidcache, $catname);
		if (count($keys) == 1) return false;
		if (!empty($menuitem->query['catid'])) {
			$keys = array_flip($keys);
			unset($keys[$catid]);
			$categories = array_intersect_key($keys, KunenaForumCategoryHelper::getChildren($menuitem->query['catid']));
			KUNENA_PROFILER ? KunenaProfiler::instance()->stop('function '.__CLASS__.'::'.__FUNCTION__.'()') : null;
			return !empty($categories);
		}
		KUNENA_PROFILER ? KunenaProfiler::instance()->stop('function '.__CLASS__.'::'.__FUNCTION__.'()') : null;
		return true;
	}

	function filterOutput($str) {
		return JString::trim ( preg_replace ( array ('/\s+/', '/[\$\&\+\,\/\:\;\=\?\@\'\"\<\>\#\%\{\}\|\\\^\~\[\]\`\.]/' ), array ('-', '' ), $str ) );
	}

	function stringURLSafe($str) {
		static $filtered = array();
		KUNENA_PROFILER ? KunenaProfiler::instance()->start('function '.__CLASS__.'::'.__FUNCTION__.'()') : null;
		if (!isset($filtered[$str])) {
			if (self::$config->sefutf8) {
				$str = self::filterOutput ( $str );
				$filtered[$str] = urlencode ( $str );
			} else {
				$filtered[$str] = JFilterOutput::stringURLSafe ( $str );
			}
		}
		KUNENA_PROFILER ? KunenaProfiler::instance()->stop('function '.__CLASS__.'::'.__FUNCTION__.'()') : null;
		return $filtered[$str];
	}
}

KunenaRouter::initialize ();

/**
 * Build SEF URL
 *
 * All SEF URLs are formatted like this:
 *
 * http://site.com/menuitem/1-category-name/10-subject/[view]/[layout]/[param1]-value1/[param2]-value2?param3=value3&param4=value4
 *
 * - If catid exists, category will always be in the first segment
 * - If there is no catid, second segment for message will not be used (param-value: id-10)
 * - [view] and [layout] are the only parameters without value
 * - all other segments (task, id, userid, page, sel) are using param-value format
 *
 * NOTE! Only major variables are using SEF segments
 *
 * @param $query
 * @return segments
 */
function KunenaBuildRoute(&$query) {
	KUNENA_PROFILER ? KunenaProfiler::instance()->start('function '.__FUNCTION__.'()') : null;

	$segments = array ();

	// If Kunena SEF is not enabled, do nothing
	if (! KunenaRoute::$config->sef) {
		KUNENA_PROFILER ? KunenaProfiler::instance()->stop('function '.__FUNCTION__.'()') : null;
		return $segments;
	}

	// Get menu item
	if (isset ( $query ['Itemid'] )) {
		static $menuitems = array();
		$Itemid = $query ['Itemid'] = (int) $query ['Itemid'];
		if (!isset($menuitems[$Itemid])) {
			$menuitems[$Itemid] = JFactory::getApplication()->getMenu ()->getItem ( $Itemid );
			if (!$menuitems[$Itemid]) {
				// Itemid doesn't exist or is invalid
				unset ($query ['Itemid']);
			}
		}
		$menuitem = $menuitems[$Itemid];
	}

	// Safety check: we need view in order to create SEF URLs
	if (!isset ( $menuitem->query ['view'] ) && empty ( $query ['view'] )) {
		KUNENA_PROFILER ? KunenaProfiler::instance()->stop('function '.__FUNCTION__.'()') : null;
		return $segments;
	}

	// Get view for later use (query wins menu item)
	$view = isset ( $query ['view'] ) ? (string) preg_replace( '/[^a-z]/', '', $query ['view'] ) : $menuitem->query ['view'];

	// Get default values for URI variables
	if (isset(KunenaRouter::$views[$view])) {
		$defaults = KunenaRouter::$views[$view];
	}
	// Check all URI variables and remove those which aren't needed
	foreach ( $query as $var => $value ) {
		if (isset ( $defaults [$var] ) && !isset ( $menuitem->query [$var] ) && $value == $defaults [$var] ) {
			// Remove URI variable which has default value
			unset ( $query [$var] );
		} elseif ( isset ( $menuitem->query [$var] ) && $value == $menuitem->query [$var] && $var != 'Itemid' && $var != 'option' ) {
			// Remove URI variable which has the same value as menu item
			unset ( $query [$var] );
		}
	}

	// We may have catid also in the menu item (it will not be in URI)
	$numeric = !empty ( $menuitem->query ['catid'] );

	// Support URIs like: /forum/12-my_category
	if (!empty ( $query ['catid'] ) && ($view == 'category' || $view == 'topic' || $view == 'home')) {
		// TODO: ensure that we have view=categories/category/topic
		$catid = ( int ) $query ['catid'];
		if ($catid) {
			$numeric = true;

			if (KunenaRouter::$catidcache === null) {
				KunenaRouter::loadCategories ();
			}
			if (isset ( KunenaRouter::$catidcache [$catid] )) {
				$catname = KunenaRouter::$catidcache [$catid];
			}
			if (empty ( $catname )) {
				// If category name is empty (or doesn't exist), use numeric catid
				$segments [] = $catid;
			} elseif (KunenaRoute::$config->sefcats && isset(KunenaRouter::$sefviews[$view]) && !KunenaRouter::isCategoryConflict($menuitem, $catid, $catname)) {
				// If there's no naming conflict, we can use category name
				$segments [] = $catname;
			} else {
				// By default use 123-category_name
				$segments [] = "{$catid}-{$catname}";
			}
			// This segment fully defines category view so the variable is no longer needed
			if ($view == 'category') {
				unset ( $query ['view'] );
			}
		}
		unset ( $query ['catid'] );
	}

	// Support URIs like: /forum/12-category/123-topic
	if (!empty ( $query ['id'] ) && $numeric) {
		$id = (int) $query ['id'];
		if ($id) {
			$subject = KunenaRouter::stringURLSafe ( KunenaForumTopicHelper::get($id)->subject );
			if (empty ( $subject )) {
				$segments [] = $id;
			} else {
				$segments [] = "{$id}-{$subject}";
			}
			// This segment fully defines topic view so the variable is no longer needed
			if ($view == 'topic') {
				unset ( $query ['view'] );
			}
		}
		unset ( $query ['id'] );
	} else {
		// No id available, do not use numeric variable for mesid
		$numeric = false;
	}

	// View gets added only when we do not use short URI for category/topic
	if (!empty ( $query ['view'] )) {
		// Use filtered value
		$segments [] = $view;
	}

	// Support URIs like: /forum/12-category/123-topic/reply
	if (!empty ( $query ['layout'] )) {
		// Use filtered value
		$segments [] = (string) preg_replace( '/[^a-z]/', '', $query ['layout'] );
	}

	// Support URIs like: /forum/12-category/123-topic/reply/124
	if (isset ( $query ['mesid'] ) && $numeric) {
		$segments [] = (int) $query ['mesid'];
		unset ( $query ['mesid'] );
	}

	// Support URIs like: /forum/user/128-matias
	if (isset ( $query ['userid'] ) && $view == 'user') {
		$segments [] = (int) $query ['userid'] .'-'.KunenaRouter::stringURLSafe ( KunenaUserHelper::get((int)$query ['userid'])->getName() );
		unset ( $query ['userid'] );
	}

	unset ( $query ['view'], $query ['layout'] );

	// Rest of the known parameters are in var-value form
	foreach ( KunenaRouter::$parsevars as $var=>$dummy ) {
		if (isset ( $query [$var] )) {
			$segments [] = "{$var}-{$query[$var]}";
			unset ( $query [$var] );
		}
	}

	KUNENA_PROFILER ? KunenaProfiler::instance()->stop('function '.__FUNCTION__.'()') : null;
	return $segments;
}

function KunenaParseRoute($segments) {
	$profiler = JProfiler::getInstance('Application');
	KUNENA_PROFILER ? $profiler->mark('kunenaRoute') : null;
	$starttime = $profiler->getmicrotime();

	// Get current menu item and get query variables from it
	$active = JFactory::getApplication()->getMenu ()->getActive ();
	$vars = isset ( $active->query ) ? $active->query : array ('view'=>'home');
	if (empty($vars['view']) || $vars['view']=='home' || $vars['view']=='entrypage') {
		$vars['view'] = '';
	}

	// Fix bug in Joomla 1.5 when using /components/kunena instead /component/kunena
	if (!$active && $segments[0] == 'kunena') array_shift ( $segments );

	// Enable SEF category feature
	$sefcats = KunenaRoute::$config->sefcats && isset(KunenaRouter::$sefviews[$vars['view']]) && empty($vars ['id']);

	// Handle all segments
	while ( ($segment = array_shift ( $segments )) !== null ) {
		$seg = explode ( ':', $segment );
		$var = array_shift ( $seg );
		$value = array_shift ( $seg );

		if (is_numeric ( $var )) {
			$value = (int) $var;
			if ($vars['view'] == 'user') {
				$var = 'userid';
			} else {
				// Numeric variable is always catid or id
				if (empty($vars ['catid'])) {
					//|| (empty($vars ['id']) && KunenaForumCategoryHelper::get($value)->exists() && KunenaForumTopicHelper::get($value)->category_id != $vars ['catid'])) {
					// First numbers are always categories
					// FIXME: what if $topic->catid == catid
					$var = 'catid';
					$vars ['view'] = 'category';
				} elseif (empty($vars ['id'])) {
					// Next number is always topic
					$var = 'id';
					$vars ['view'] = 'topic';
					$sefcats = false;
				} elseif (empty($vars ['mesid'])) {
					// Next number is always message
					$var = 'mesid';
					$vars ['view'] = 'topic';
				} else {
					// Invalid parameter, skip it
					continue;
				}
			}
		} elseif (empty ( $var ) && empty ( $value )) {
			// Empty parameter, skip it
			continue;
		} elseif ($sefcats && (($value !== null && ! isset ( KunenaRouter::$parsevars[$var] ))
		|| ($value === null && ! isset ( KunenaRouter::$views[$var] ) && ! isset ( KunenaRouter::$layouts[$var] ) && ! isset ( KunenaRouter::$functions[$var] )))) {
			// We have SEF category: translate category name into catid=123
			// TODO: cache filtered values to gain some speed -- I would like to start using category names instead of catids if it gets fast enough
			$var = 'catid';
			$value = -1;
			$catname = strtr ( $segment, ':', '-' );
			$categories = empty($vars ['catid']) ? KunenaForumCategoryHelper::getCategories() : KunenaForumCategoryHelper::getChildren($vars ['catid']);
			foreach ( $categories as $category ) {
				if ($catname == KunenaRouter::filterOutput ( $category->name ) || $catname == JFilterOutput::stringURLSafe ( $category->name )) {
					$value = (int) $category->id;
					break;
				}
			}
			$vars ['view'] = 'category';
		} elseif ($value === null) {
			// Variable must be either view or layout
			$sefcats = false;
			$value = $var;
			if (empty($vars ['view']) || ($value=='topic' && $vars ['view'] == 'category')) {
				$var = 'view';
			} elseif (empty($vars ['layout'])) {
				$var = 'layout';
			} else {
				// Unknown parameter: continue
				if (!empty($vars ['view'])) continue;
				// Oops: unknown view or non-existing category
				$var = 'view';
			}
		}
		$vars [$var] = $value;
	}
	if (empty($vars ['layout'])) $vars ['layout'] = 'default';
	KunenaRouter::$time = $profiler->getmicrotime() - $starttime;
	return $vars;
}