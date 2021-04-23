<?php
/**
 * Kunena Component
 *
 * @package         Kunena.Site
 * @subpackage      Controller.Category
 *
 * @copyright       Copyright (C) 2008 - 2021 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/

namespace Kunena\Forum\Site\Controller\Category\Manage;

defined('_JEXEC') or die();

use Exception;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Kunena\Forum\Libraries\Access\KunenaAccess;
use Kunena\Forum\Libraries\Controller\KunenaControllerDisplay;
use Kunena\Forum\Libraries\Exception\KunenaAuthorise;
use Kunena\Forum\Libraries\Factory\KunenaFactory;
use Kunena\Forum\Libraries\Forum\Category\KunenaCategory;
use Kunena\Forum\Libraries\Forum\Category\KunenaCategoryHelper;
use Kunena\Forum\Libraries\Pagination\KunenaPagination;
use Kunena\Forum\Libraries\Template\KunenaTemplate;
use Kunena\Forum\Libraries\User\KunenaUser;
use Kunena\Forum\Libraries\User\KunenaUserHelper;
use function defined;

/**
 * Class ComponentKunenaControllerApplicationMiscDisplay
 *
 * @since   Kunena 4.0
 */
class ComponentCategoryControllerManageDisplay extends KunenaControllerDisplay
{
	/**
	 * @var     string
	 * @since   Kunena 6.0
	 */
	protected $name = 'Category/Manage';

	/**
	 * @var     string
	 * @since   Kunena 6.0
	 */
	public $headerText;

	/**
	 * @var     KunenaCategory
	 * @since   Kunena 5.1
	 */
	public $category;

	/**
	 * @var     string
	 * @since   Kunena 6.0
	 */
	public $total;

	/**
	 * @var     string
	 * @since   Kunena 6.0
	 */
	public $topics;

	/**
	 * @var     KunenaPagination
	 * @since   Kunena 5.1
	 */
	public $pagination;

	/**
	 * @var     KunenaUser
	 * @since   Kunena 5.1
	 */
	public $me;

	/**
	 * @var     null
	 * @since   Kunena 6.0
	 */
	protected $state = null;

	/**
	 * Prepare category display.
	 *
	 * @return  KunenaAuthorise|void
	 *
	 * @since   Kunena 5.1
	 *
	 * @throws  Exception
	 */
	protected function before()
	{
		$this->me = KunenaUserHelper::getMyself();

		if (!$this->me->isAdmin())
		{
			return new KunenaAuthorise(Text::_('COM_KUNENA_NO_ACCESS'), 403);
		}

		parent::before();

		$catid = $this->input->getInt('catid');

		KunenaFactory::loadLanguage('com_kunena.models', 'admin');
		KunenaFactory::loadLanguage('com_kunena.views', 'admin');
		KunenaFactory::loadLanguage('com_kunena', 'admin');

		$this->category = KunenaCategoryHelper::get($catid);
		$this->category->tryAuthorise();

		$category = $this->category;

		if (!$category)
		{
			return false;
		}

		$category->params = new Registry($category->params);

		$catList    = [];
		$catList [] = HTMLHelper::_('select.option', 0, Text::_('COM_KUNENA_TOPLEVEL'));

		// Make a standard yes/no list
		$published    = [];
		$published [] = HTMLHelper::_('select.option', 1, Text::_('COM_KUNENA_PUBLISHED'));
		$published [] = HTMLHelper::_('select.option', 0, Text::_('COM_KUNENA_UNPUBLISHED'));

		// Make a standard yes/no list
		$yesno    = [];
		$yesno [] = HTMLHelper::_('select.option', 0, Text::_('COM_KUNENA_NO'));
		$yesno [] = HTMLHelper::_('select.option', 1, Text::_('COM_KUNENA_YES'));

		// Anonymous posts default
		$postAnonymous    = [];
		$postAnonymous [] = HTMLHelper::_('select.option', '0', Text::_('COM_KUNENA_CATEGORY_ANONYMOUS_X_REG'));
		$postAnonymous [] = HTMLHelper::_('select.option', '1', Text::_('COM_KUNENA_CATEGORY_ANONYMOUS_X_ANO'));

		$catParams                = [];
		$catParams['ordering']    = 'ordering';
		$catParams['toplevel']    = Text::_('COM_KUNENA_TOPLEVEL');
		$catParams['sections']    = 1;
		$catParams['unpublished'] = 1;
		$catParams['catid']       = $category->id;
		$catParams['action']      = 'admin';

		$channelsParams           = [];
		$channelsParams['catid']  = $category->id;
		$channelsParams['action'] = 'admin';
		$channelsOptions          = [];
		$channelsOptions []       = HTMLHelper::_('select.option', 'THIS', Text::_('COM_KUNENA_CATEGORY_CHANNELS_OPTION_THIS'));
		$channelsOptions []       = HTMLHelper::_('select.option', 'CHILDREN', Text::_('COM_KUNENA_CATEGORY_CHANNELS_OPTION_CHILDREN'));

		if (empty($category->channels))
		{
			$category->channels = 'THIS';
		}

		$topicOrderingOptions   = [];
		$topicOrderingOptions[] = HTMLHelper::_('select.option', 'lastpost', Text::_('COM_KUNENA_CATEGORY_TOPIC_ORDERING_OPTION_LASTPOST'));
		$topicOrderingOptions[] = HTMLHelper::_('select.option', 'creation', Text::_('COM_KUNENA_CATEGORY_TOPIC_ORDERING_OPTION_CREATION'));
		$topicOrderingOptions[] = HTMLHelper::_('select.option', 'alpha', Text::_('COM_KUNENA_CATEGORY_TOPIC_ORDERING_OPTION_ALPHA'));
		$topicOrderingOptions[] = HTMLHelper::_('select.option', 'views', Text::_('COM_KUNENA_CATEGORY_TOPIC_ORDERING_OPTION_VIEWS'));
		$topicOrderingOptions[] = HTMLHelper::_('select.option', 'posts', Text::_('COM_KUNENA_CATEGORY_TOPIC_ORDERING_OPTION_POSTS'));

		$aliases = array_keys($category->getAliases());

		// Build the html select list
		// make a standard yes/no list
		$yesno    = [];
		$yesno [] = HTMLHelper::_('select.option', '0', Text::_('COM_KUNENA_A_NO'));
		$yesno [] = HTMLHelper::_('select.option', '1', Text::_('COM_KUNENA_A_YES'));

		$catParams = ['sections' => 1, 'catid' => 0];

		$lists                     = [];
		$lists ['accesstypes']     = Access::getInstance()->getAccessTypesList($category);
		$lists ['accesslists']     = Access::getInstance()->getAccessOptions($category);
		$lists ['categories']      = HTMLHelper::_('select.genericlist', $catParams, 'parentid', 'class="inputbox form-control"', 'value', 'text', $category->parentid);
		$lists ['channels']        = HTMLHelper::_('select.genericlist', $channelsOptions, 'channels', 'class="inputbox form-control" multiple="multiple"', 'value', 'text', explode(',', $category->channels));
		$lists ['aliases']         = $aliases ? HTMLHelper::_('kunenaforum.checklist', 'aliases', $aliases, true, 'category_aliases') : null;
		$lists ['published']       = HTMLHelper::_('select.genericlist', $published, 'published', 'class="inputbox form-control"', 'value', 'text', $category->published);
		$lists ['forumLocked']     = HTMLHelper::_('select.genericlist', $yesno, 'locked', 'class="inputbox form-control" size="1"', 'value', 'text', $category->locked);
		$lists ['forumReview']     = HTMLHelper::_('select.genericlist', $yesno, 'review', 'class="inputbox form-control" size="1"', 'value', 'text', $category->review);
		$lists ['allowPolls']     = HTMLHelper::_('select.genericlist', $yesno, 'allowPolls', 'class="inputbox form-control" size="1"', 'value', 'text', $category->allowPolls);
		$lists ['allowAnonymous'] = HTMLHelper::_('select.genericlist', $yesno, 'allowAnonymous', 'class="inputbox form-control" size="1"', 'value', 'text', $category->allowAnonymous);
		$lists ['postAnonymous']  = HTMLHelper::_('select.genericlist', $postAnonymous, 'postAnonymous', 'class="inputbox form-control" size="1"', 'value', 'text', $category->postAnonymous);
		$lists ['topicOrdering']  = HTMLHelper::_('select.genericlist', $topicOrderingOptions, 'topicOrdering', 'class="inputbox form-control" size="1"', 'value', 'text', $category->topicOrdering);
		$lists ['allowRatings']   = HTMLHelper::_('select.genericlist', $yesno, 'allowRatings', 'class="inputbox form-control" size="1"', 'value', 'text', $category->allowRatings);

		$options                 = [];
		$options[0]              = HTMLHelper::_('select.option', '0', Text::_('COM_KUNENA_A_CATEGORY_CFG_OPTION_NEVER'));
		$options[1]              = HTMLHelper::_('select.option', '1', Text::_('COM_KUNENA_A_CATEGORY_CFG_OPTION_SECTION'));
		$options[2]              = HTMLHelper::_('select.option', '2', Text::_('COM_KUNENA_A_CATEGORY_CFG_OPTION_CATEGORY'));
		$options[3]              = HTMLHelper::_('select.option', '3', Text::_('COM_KUNENA_A_CATEGORY_CFG_OPTION_SUBCATEGORY'));
		$lists['display_parent'] = HTMLHelper::_('select.genericlist', $options, 'params[display][index][parent]', 'class="inputbox form-control" size="1"', 'value', 'text', $category->params->get('display.index.parent', '3'));

		unset($options[1]);

		$lists['display_children'] = HTMLHelper::_('select.genericlist', $options, 'params[display][index][children]', 'class="inputbox form-control" size="1"', 'value', 'text', $category->params->get('display.index.children', '3'));

		$topicIcons     = [];
		$topicIconslist = Folder::folders(JPATH_ROOT . '/media/kunena/topic_icons');

		foreach ($topicIconslist as $icon)
		{
			$topicIcons[] = HTMLHelper::_('select.option', $icon, $icon);
		}

		if (empty($category->iconset))
		{
			$value = KunenaTemplate::getInstance()->params->get('DefaultIconset');
		}
		else
		{
			$value = $category->iconset;
		}

		$lists ['categoryIconset'] = HTMLHelper::_('select.genericlist', $topicIcons, 'iconset', 'class="inputbox form-control" size="1"', 'value', 'text', $value);

		$lists1 = $lists;
	}

	/**
	 * Prepare document.
	 *
	 * @return  void|boolean
	 *
	 * @since   Kunena 5.1
	 *
	 * @throws  Exception
	 */
	protected function prepareDocument(): bool
	{
		$menu_item = $this->app->getMenu()->getActive();

		$this->setMetaData('robots', 'nofollow, noindex');

		if ($menu_item)
		{
			$params       = $menu_item->getParams();
			$params_title = $params->get('page_title');

			if (!empty($params_title))
			{
				$title = $params->get('page_title');
				$this->setTitle($title);
			}
			else
			{
				$title = Text::_('COM_KUNENA_ADMIN');
				$this->setTitle($title);
			}
		}
	}
}
