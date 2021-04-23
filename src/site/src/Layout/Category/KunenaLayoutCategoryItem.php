<?php
/**
 * Kunena Component
 *
 * @package         Kunena.Site
 * @subpackage      Layout.Category.Item
 *
 * @copyright       Copyright (C) 2008 - 2021 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/

namespace Kunena\Forum\Site\Layout\Category;

defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Kunena\Forum\Libraries\Config\KunenaConfig;
use Kunena\Forum\Libraries\Controller\KunenaControllerDisplay;
use Kunena\Forum\Libraries\Factory\KunenaFactory;
use Kunena\Forum\Libraries\Forum\Category\KunenaCategory;
use Kunena\Forum\Libraries\Html\KunenaParser;
use Kunena\Forum\Libraries\Layout\KunenaLayout;
use Kunena\Forum\Libraries\Pagination\KunenaPagination;
use Kunena\Forum\Libraries\Template\KunenaTemplate;
use Kunena\Forum\Libraries\User\KunenaUser;
use function defined;

/**
 * KunenaLayoutCategoryItem
 *
 * @since   Kunena 4.0
 */
class KunenaLayoutCategoryItem extends KunenaLayout
{
	/**
	 * @var     integer
	 * @since   Kunena 6.0
	 */
	public $total;

	/**
	 * @var     object
	 * @since   Kunena 6.0
	 */
	public $state;

	/**
	 * @var     boolean
	 * @since   Kunena 6.0
	 */
	public $subcategories;

	/**
	 * @var     void
	 * @since   Kunena 6.0
	 */
	public $sections;

	/**
	 * @var     KunenaCategory
	 * @since   Kunena 6.0
	 */
	public $category;

	/**
	 * @var     KunenaTemplate|void
	 * @since   Kunena 6.0
	 */
	public $ktemplate;

	/**
	 * @var     KunenaUser
	 * @since   Kunena 6.0
	 */
	public $me;

	/**
	 * Method to display categories Index sublayout
	 *
	 * @return  void
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws  Exception
	 */
	public function displayCategories()
	{
		if ($this->sections)
		{
			$this->subcategories = true;
			echo $this->subLayout('Category/Index')->setProperties($this->getProperties())->setLayout('subcategories');
		}
	}

	/**
	 * Method to display category action sublayout
	 *
	 * @return  void
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws  Exception
	 */
	public function displayCategoryActions()
	{
		if (!$this->category->isSection())
		{
			echo $this->subLayout('Category/Item/Actions')->setProperties($this->getProperties());
		}
	}

	/**
	 * Method to return array of actions sublayout
	 *
	 * @return  array|boolean
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws  Exception
	 */
	public function getCategoryActions()
	{
		$category = $this->category;
		$token    = '&' . Session::getFormToken() . '=1';
		$actions  = [];

		// Is user allowed to post new topic?
		$url             = $category->getNewTopicUrl();
		$this->ktemplate = KunenaFactory::getTemplate();
		$topicicontype   = $this->ktemplate->params->get('topicicontype');
		$config          = KunenaConfig::getInstance();

		if ($config->readOnly)
		{
			return false;
		}

		if ($category->isAuthorised('topic.create'))
		{
			if ($url && $topicicontype == 'B3')
			{
				$actions['create'] = $this->subLayout('Widget/Button')
					->setProperties(['url'  => $url, 'name' => 'create', 'scope' => 'topic', 'type' => 'communication', 'success' => true,
									 'icon' => 'glyphicon glyphicon-edit glyphicon-white', ]
					);
			}
			elseif ($url && $topicicontype == 'B4')
			{
				$actions['create'] = $this->subLayout('Widget/Button')
					->setProperties(['url'  => $url, 'name' => 'create', 'scope' => 'topic', 'type' => 'communication', 'success' => true,
									 'icon' => 'pencil', ]
					);
			}
			elseif ($url && $topicicontype == 'fa')
			{
				$actions['create'] = $this->subLayout('Widget/Button')
					->setProperties(['url'  => $url, 'name' => 'create', 'scope' => 'topic', 'type' => 'communication', 'success' => true,
									 'icon' => 'fa fa-pencil-alt', ]
					);
			}
			else
			{
				$actions['create'] = $this->subLayout('Widget/Button')
					->setProperties(['url'  => $url, 'name' => 'create', 'scope' => 'topic', 'type' => 'communication', 'success' => true,
									 'icon' => 'icon-edit icon-white', ]
					);
			}
		}

		if ($category->getTopics() > 0)
		{
			// Is user allowed to mark forums as read?
			$url = $category->getMarkReadUrl();

			if ($this->me->exists())
			{
				if ($url && $topicicontype == 'B3')
				{
					$actions['markread'] = $this->subLayout('Widget/Button')
						->setProperties(['url'  => $url, 'name' => 'markread', 'scope' => 'category', 'type' => 'user',
										 'icon' => 'glyphicon glyphicon-check', ]
						);
				}
				elseif ($url && $topicicontype == 'B4')
				{
					$actions['markread'] = $this->subLayout('Widget/Button')
						->setProperties(['url' => $url, 'name' => 'markread', 'scope' => 'category', 'type' => 'user', 'icon' => 'bookmark-fill']);
				}
				elseif ($url && $topicicontype == 'fa')
				{
					$actions['markread'] = $this->subLayout('Widget/Button')
						->setProperties(['url' => $url, 'name' => 'markread', 'scope' => 'category', 'type' => 'user', 'icon' => 'fa fa-book']);
				}
				else
				{
					$actions['markread'] = $this->subLayout('Widget/Button')
						->setProperties(['url' => $url, 'name' => 'markread', 'scope' => 'category', 'type' => 'user', 'icon' => 'icon-drawer']);
				}
			}
		}

		// Is user allowed to subscribe category?
		if ($category->isAuthorised('subscribe'))
		{
			$subscribed = $category->getSubscribed($this->me->userid);

			if ($url && $topicicontype == 'B3')
			{
				if (!$subscribed)
				{
					$url                  = "index.php?option=com_kunena&view=category&task=subscribe&catid={$category->id}{$token}";
					$actions['subscribe'] = $this->subLayout('Widget/Button')
						->setProperties(['url'  => $url, 'name' => 'subscribe', 'scope' => 'category', 'type' => 'user',
										 'icon' => 'glyphicon glyphicon-envelope', ]
						);
				}
				else
				{
					$url                    = "index.php?option=com_kunena&view=category&task=unsubscribe&catid={$category->id}{$token}";
					$actions['unsubscribe'] = $this->subLayout('Widget/Button')
						->setProperties(['url'  => $url, 'name' => 'unsubscribe', 'scope' => 'category', 'type' => 'user',
										 'icon' => 'glyphicon glyphicon-envelope', ]
						);
				}
			}
			elseif ($url && $topicicontype == 'B4')
			{
				if (!$subscribed)
				{
					$url                  = "index.php?option=com_kunena&view=category&task=subscribe&catid={$category->id}{$token}";
					$actions['subscribe'] = $this->subLayout('Widget/Button')
						->setProperties(['url'  => $url, 'name' => 'subscribe', 'scope' => 'category', 'type' => 'user',
										 'icon' => 'envelope', ]
						);
				}
				else
				{
					$url                    = "index.php?option=com_kunena&view=category&task=unsubscribe&catid={$category->id}{$token}";
					$actions['unsubscribe'] = $this->subLayout('Widget/Button')
						->setProperties(['url'  => $url, 'name' => 'unsubscribe', 'scope' => 'category', 'type' => 'user',
										 'icon' => 'envelope-open', ]
						);
				}
			}
			elseif ($url && $topicicontype == 'fa')
			{
				if (!$subscribed)
				{
					$url                  = "index.php?option=com_kunena&view=category&task=subscribe&catid={$category->id}{$token}";
					$actions['subscribe'] = $this->subLayout('Widget/Button')
						->setProperties(['url'  => $url, 'name' => 'subscribe', 'scope' => 'category', 'type' => 'user',
										 'icon' => 'fa fa-envelope', ]
						);
				}
				else
				{
					$url                    = "index.php?option=com_kunena&view=category&task=unsubscribe&catid={$category->id}{$token}";
					$actions['unsubscribe'] = $this->subLayout('Widget/Button')
						->setProperties(['url'  => $url, 'name' => 'unsubscribe', 'scope' => 'category', 'type' => 'user',
										 'icon' => 'fas fa-envelope-open', ]
						);
				}
			}
			else
			{
				if (!$subscribed)
				{
					$url                  = "index.php?option=com_kunena&view=category&task=subscribe&catid={$category->id}{$token}";
					$actions['subscribe'] = $this->subLayout('Widget/Button')
						->setProperties(['url'  => $url, 'name' => 'subscribe', 'scope' => 'category', 'type' => 'user',
										 'icon' => 'icon-envelope', ]
						);
				}
				else
				{
					$url                    = "index.php?option=com_kunena&view=category&task=unsubscribe&catid={$category->id}{$token}";
					$actions['unsubscribe'] = $this->subLayout('Widget/Button')
						->setProperties(['url'  => $url, 'name' => 'unsubscribe', 'scope' => 'category', 'type' => 'user',
										 'icon' => 'icon-envelope-opened', ]
						);
				}
			}
		}

		return $actions;
	}

	/**
	 * Method to get the last post link
	 *
	 * @see     \Kunena\Forum\Libraries\Layout\Layout::getLastPostLink()
	 *
	 * @param   KunenaCategory  $category   The KunenaCategory object
	 * @param   string          $content    The content of last topic subject
	 * @param   string          $title      The title of the link
	 * @param   string          $class      The class attribute of the link
	 * @param   int             $length     length
	 * @param   bool            $follow     follow
	 * @param   bool            $canonical  canonical
	 *
	 * @return  string
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws Exception
	 * @throws null
	 */
	public function getLastPostLink(KunenaCategory $category, string $content, string $title, string $class, int $length = 30, bool $follow = true, bool $canonical = false): string
	{
		$lastTopic = $category->getLastTopic();
		$channels  = $category->getChannels();

		if (!isset($channels[$lastTopic->category_id]))
		{
			$category = $lastTopic->getCategory();
		}

		$uri = $lastTopic->getUri($category, 'last');

		if (!$content)
		{
			if (KunenaConfig::getInstance()->disableRe)
			{
				$content = KunenaParser::parseText($category->getLastTopic()->subject, $length);
			}
			else
			{
				$content = $lastTopic->first_post_id != $lastTopic->last_post_id ? Text::_('COM_KUNENA_RE') . ' ' : '';
				$content .= KunenaParser::parseText($category->getLastTopic()->subject, $length);
			}
		}

		if ($title === null)
		{
			$title = Text::sprintf('COM_KUNENA_TOPIC_LAST_LINK_TITLE', $this->escape($category->getLastTopic()->subject));
		}

		return HTMLHelper::_('link', $uri, $content, $title, $class, 'nofollow');
	}

	/**
	 * Return the links of pagination item
	 *
	 * @param   int  $maxpages  The maximum number of pages
	 *
	 * @return  string
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws  Exception
	 */
	public function getPagination($maxpages)
	{
		$pagination = new KunenaPagination($this->total, $this->state->get('list.start'), $this->state->get('list.limit'));
		$pagination->setDisplayedPages($maxpages);

		return $pagination->getPagesLinks();
	}
}
