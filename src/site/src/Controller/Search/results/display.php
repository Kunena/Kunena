<?php
/**
 * Kunena Component
 *
 * @package         Kunena.Site
 * @subpackage      Controller.Search
 *
 * @copyright       Copyright (C) 2008 - 2021 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/

namespace Kunena\Forum\Site\Controller\Search\Results;

defined('_JEXEC') or die();

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Kunena\Forum\Libraries\Access\KunenaAccess;
use Kunena\Forum\Libraries\Controller\KunenaControllerDisplay;
use Kunena\Forum\Libraries\Pagination\KunenaPagination;
use Kunena\Forum\Libraries\Route\KunenaRoute;
use Kunena\Forum\Libraries\User\KunenaUserHelper;
use Kunena\Forum\Site\Model\SearchModel;
use function defined;

/**
 * Class ComponentSearchControllerResultsDisplay
 *
 * @since   Kunena 4.0
 */
class ComponentSearchControllerResultsDisplay extends KunenaControllerDisplay
{
	/**
	 * @var     SearchModel
	 * @since   Kunena 6.0
	 */
	public $model;

	/**
	 * @var     integer
	 * @since   Kunena 6.0
	 */
	public $total;

	/**
	 * @var     array
	 * @since   Kunena 6.0
	 */
	public $data = [];

	/**
	 * @var     string
	 * @since   Kunena 6.0
	 */
	protected $name = 'Search/Results';

	/**
	 * Prepare search results display.
	 *
	 * @return  void
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws  null
	 * @throws  Exception
	 */
	protected function before()
	{
		parent::before();

		$this->model = new SearchModel([], $this->input);
		$this->model->initialize($this->getOptions(), $this->getOptions()->get('embedded', false));
		$state = $this->model->getState();

		$me               = KunenaUserHelper::getMyself();
		$message_ordering = $me->getMessageOrdering();

		$searchwords = $this->model->getSearchWords();
		$isModerator = ($me->isAdmin() || KunenaAccess::getInstance()->getModeratorStatus());

		$results     = [];
		$this->total = $this->model->getTotal();
		$results     = $this->model->getResults();

		$doc = Factory::getApplication()->getDocument();
		$doc->setMetaData('robots', 'follow, noindex');

		foreach ($doc->_links as $key => $value)
		{
			if (is_array($value))
			{
				if (array_key_exists('relation', $value))
				{
					if ($value['relation'] == 'canonical')
					{
						$canonicalUrl               = KunenaRoute::_('index.php?option=com_kunena&view=search');
						$doc->_links[$canonicalUrl] = $value;
						unset($doc->_links[$key]);
						break;
					}
				}
			}
		}

		$pagination = new KunenaPagination(
			$this->total,
			$state->get('list.start'),
			$state->get('list.limit')
		);

		$error = $this->model->getError();
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
				$this->setTitle(Text::_('COM_KUNENA_SEARCH_ADVSEARCH'));
			}

			if (!empty($params_description))
			{
				$description = $params->get('menu-meta_description');
				$this->setDescription($description);
			}
			else
			{
				$description = Text::_('COM_KUNENA_SEARCH_ADVSEARCH') . ': ' . $this->config->boardTitle;
				$this->setDescription($description);
			}
		}
	}
}
