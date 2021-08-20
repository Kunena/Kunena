<?php
/**
 * Kunena Component
 *
 * @package    Kunena.Framework
 * @subpackage Icons
 *
 * @copyright  Copyright (C) 2008 - 2021 Kunena Team. All rights reserved.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.kunena.org
 **/

namespace Kunena\Forum\Libraries\Icons;

\defined('_JEXEC') or die();

use DOMDocument;
use Joomla\CMS\Uri\Uri;

/**
 * Class KunenaSvgIcons
 *
 * @since   Kunena 6.0
 */
class KunenaSvgIcons
{
	/**
	 * Class load svg icon.
	 *
	 * @param   string  $svgname  load svg name
	 * @param   string  $group    Load the svg location
	 * @param   string  $iconset  Load iconset when topicIcons
	 *
	 * @return  string|void
	 *
	 * @since   Kunena 6.0
	 */
	public static function loadsvg(string $svgname, $group = 'default', $iconset = 'default'): string
	{
		if (empty($svgname))
		{
			return false;
		}

		if ($group == 'default')
		{
			$file = Uri::root() . 'media/kunena/core/svg/' . $svgname;
		}
		elseif ($group == 'social')
		{
			$file = Uri::root() . 'media/kunena/core/images/social/' . $svgname;
		}
		elseif ($group == 'systemtopicIcons')
		{
			$file = Uri::root() . 'media/kunena/topic_icons/' . $iconset . '/system/svg/' . $svgname;
		}
		elseif ($group == 'usertopicIcons')
		{
			$file = Uri::root() . 'media/kunena/topic_icons/' . $iconset . '/user/svg/' . $svgname;
		}
		else
		{
			$file = Uri::root() . $group . $svgname;
		}

		if (!file_exists($file . '.svg'))
		{
			return false;
		}

		$iconfile = new DOMDocument;
		$opts     = [
			'http' => [
				'user_agent' => 'PHP libxml agent',
			],
		];
		$context  = stream_context_create($opts);
		libxml_set_streams_context($context);
		$iconfile->load($file . '.svg');

		return $iconfile->saveHTML($iconfile->getElementsByTagName('svg')[0]);
	}
}
