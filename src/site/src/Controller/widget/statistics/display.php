<?php
/**
 * Kunena Component
 *
 * @package         Kunena.Site
 * @subpackage      Controller.Widget
 *
 * @copyright       Copyright (C) 2008 - 2021 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/

namespace Kunena\Forum\Site\Controller\Widget\Statistics;

defined('_JEXEC') or die();

use Exception;
use Joomla\CMS\Language\Text;
use Kunena\Forum\Libraries\Config\KunenaConfig;
use Kunena\Forum\Libraries\Controller\KunenaControllerDisplay;
use Kunena\Forum\Libraries\Exception\KunenaAuthorise;
use Kunena\Forum\Libraries\Factory\KunenaFactory;
use Kunena\Forum\Libraries\Forum\KunenaStatistics;
use function defined;

/**
 * Class ComponentKunenaControllerWidgetStatisticsDisplay
 *
 * @since   Kunena 4.0
 */
class ComponentKunenaControllerWidgetStatisticsDisplay extends KunenaControllerDisplay
{
	/**
	 * @var     string
	 * @since   Kunena 6.0
	 */
	protected $name = 'Widget/Statistics';

	/**
	 * @var     object
	 * @since   Kunena 6.0
	 */
	public $config;

	/**
	 * @var     string
	 * @since   Kunena 6.0
	 */
	public $latestMemberLink;

	/**
	 * @var     string
	 * @since   Kunena 6.0
	 */
	public $statisticsUrl;
	private $lastUserId;

	/**
	 * Prepare statistics box display.
	 *
	 * @return  boolean
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws  Exception
	 * @throws  null
	 */
	protected function before()
	{
		parent::before();

		$this->config = KunenaConfig::getInstance();

		if (!$this->config->get('showStats'))
		{
			throw new KunenaAuthorise(Text::_('COM_KUNENA_NO_ACCESS'), '404');
		}

		$statistics = KunenaStatistics::getInstance();
		$statistics->loadGeneral();
		$this->setProperties($statistics);

		$this->latestMemberLink = KunenaFactory::getUser(intval($this->lastUserId))->getLink(null, null, '');
		$this->statisticsUrl    = KunenaFactory::getProfile()->getStatisticsURL();

		return true;
	}
}
