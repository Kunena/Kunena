<?php
/**
 * Kunena Component
 *
 * @package         Kunena.Site
 * @subpackage      Controller.Statistics
 *
 * @copyright       Copyright (C) 2008 - 2021 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/

namespace Kunena\Forum\Site\Controller\Statistics\General;

defined('_JEXEC') or die();

use Exception;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Kunena\Forum\Libraries\Controller\KunenaControllerDisplay;
use Kunena\Forum\Libraries\Exception\KunenaAuthorise;
use Kunena\Forum\Libraries\Factory\KunenaFactory;
use Kunena\Forum\Libraries\Forum\KunenaStatistics;
use Kunena\Forum\Libraries\Route\KunenaRoute;
use function defined;

/**
 * Class ComponentStatisticsControllerGeneralDisplay
 *
 * @since   Kunena 4.0
 */
class ComponentStatisticsControllerGeneralDisplay extends KunenaControllerDisplay
{
	/**
	 * @var     string
	 * @since   Kunena 6.0
	 */
	protected $name = 'Statistics/General';
	private $lastUserId;

	/**
	 * Prepare general statistics display.
	 *
	 * @return  void
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws  Exception
	 * @throws  null
	 */
	protected function before()
	{
		parent::before();

		$Itemid = $this->input->getInt('Itemid');

		if (!$Itemid && $this->config->sefRedirect)
		{
			$itemid     = KunenaRoute::fixMissingItemID();
			$controller = BaseController::getInstance("kunena");
			$controller->setRedirect(KunenaRoute::_("index.php?option=com_kunena&view=statistics&Itemid={$itemid}", false));
			$controller->redirect();
		}

		if (!$this->config->get('showStats'))
		{
			throw new KunenaAuthorise(Text::_('COM_KUNENA_NO_ACCESS'), '404');
		}

		if (!$this->config->statsLinkAllowed && Factory::getApplication()->getIdentity()->guest)
		{
			throw new KunenaAuthorise(Text::_('COM_KUNENA_NO_ACCESS'), '401');
		}

		$statistics = KunenaStatistics::getInstance();
		$statistics->loadAll();
		$this->setProperties($statistics);

		$latestMemberLink = KunenaFactory::getUser((int) $this->lastUserId)->getLink(null, null, '');
		$userlistUrl      = KunenaFactory::getProfile()->getUserListUrl();
	}

	/**
	 * Prepare document.
	 *
	 * @return  void|boolean
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws  Exception
	 */
	protected function prepareDocument(): bool
	{
		$menu_item = $this->app->getMenu()->getActive();
		$componentParams = ComponentHelper::getParams('com_config');
		$robots          = $componentParams->get('robots');

		if ($robots == 'noindex, follow')
		{
			$this->setMetaData('robots', 'noindex, follow');
		}
		elseif ($robots == 'index, nofollow')
		{
			$this->setMetaData('robots', 'index, nofollow');
		}
		elseif ($robots == 'noindex, nofollow')
		{
			$this->setMetaData('robots', 'noindex, nofollow');
		}
		else
		{
			$this->setMetaData('robots', 'index, follow');
		}

		if ($menu_item)
		{
			$params             = $menu_item->getParams();
			$params_title       = $params->get('page_title');
			$params_description = $params->get('menu-meta_description');

			if (!empty($params_title))
			{
				$title = $params->get('page_title');
				$this->setTitle($title);
			}
			else
			{
				$this->setTitle(Text::_('COM_KUNENA_STAT_FORUMSTATS'));
			}

			if (!empty($params_description))
			{
				$description = $params->get('menu-meta_description');
				$this->setDescription($description);
			}
			else
			{
				$description = Text::_('COM_KUNENA_STAT_FORUMSTATS') . ': ' . $this->config->boardTitle;
				$this->setDescription($description);
			}

			if (!empty($params_robots))
			{
				$robots = $params->get('robots');
				$this->setMetaData('robots', $robots);
			}
		}
	}
}
