<?php
/**
 * Kunena Component
 *
 * @package         Kunena.Site
 * @subpackage      Controller.Topic
 *
 * @copyright       Copyright (C) 2008 - 2021 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/

namespace Kunena\Forum\Site\Controller\Topic\Moderate;

defined('_JEXEC') or die();

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\Exception\ExecutionFailureException;
use Kunena\Forum\Libraries\Controller\KunenaControllerDisplay;
use Kunena\Forum\Libraries\Error\KunenaError;
use Kunena\Forum\Libraries\Exception\KunenaAuthorise;
use Kunena\Forum\Libraries\Forum\Message\KunenaMessage;
use Kunena\Forum\Libraries\Forum\Message\KunenaMessageHelper;
use Kunena\Forum\Libraries\Forum\Topic\KunenaTopic;
use Kunena\Forum\Libraries\Forum\Topic\KunenaTopicHelper;
use Kunena\Forum\Libraries\Template\KunenaTemplate;
use Kunena\Forum\Libraries\User\KunenaBan;
use function defined;

/**
 * Class ComponentTopicControllerModerateDisplay
 *
 * @since   Kunena 4.0
 */
class ComponentTopicControllerModerateDisplay extends KunenaControllerDisplay
{
	/**
	 * @var     KunenaTopic
	 * @since   Kunena 6.0
	 */
	public $topic;

	/**
	 * @var     KunenaMessage|null
	 * @since   Kunena 6.0
	 */
	public $message;

	/**
	 * @var     string
	 * @since   Kunena 6.0
	 */
	public $uri;

	/**
	 * @var     string
	 * @since   Kunena 6.0
	 */
	public $title;

	/**
	 * @var     string
	 * @since   Kunena 6.0
	 */
	public $topicIcons;

	/**
	 * @var     string
	 * @since   Kunena 6.0
	 */
	public $userLink;

	/**
	 * @var     string
	 * @since   Kunena 6.0
	 */
	protected $name = 'Topic/Moderate';

	/**
	 * Prepare topic moderate display.
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

		$catid = $this->input->getInt('catid');
		$id    = $this->input->getInt('id');
		$mesid = $this->input->getInt('mesid');

		if (!$mesid)
		{
			$this->topic = KunenaTopicHelper::get($id);
			$this->topic->tryAuthorise('move');
		}
		else
		{
			$this->message = KunenaMessageHelper::get($mesid);
			$this->message->tryAuthorise('move');
			$this->topic = $this->message->getTopic();
		}

		if ($this->config->readOnly)
		{
			throw new KunenaAuthorise(Text::_('COM_KUNENA_NO_ACCESS'), '401');
		}

		$category = $this->topic->getCategory();

		$this->uri   = "index.php?option=com_kunena&view=topic&layout=moderate"
			. "&catid={$category->id}&id={$this->topic->id}"
			. ($this->message ? "&mesid={$this->message->id}" : '');
		$this->title = !$this->message ?
			Text::_('COM_KUNENA_TITLE_MODERATE_TOPIC') :
			Text::_('COM_KUNENA_TITLE_MODERATE_MESSAGE');

		$template = KunenaTemplate::getInstance();
		$template->setCategoryIconset($this->topic->getCategory()->iconset);
		$this->topicIcons = $template->getTopicIcons(false);

		// Have a link to moderate user as well.
		if (isset($this->message))
		{
			$user = $this->message->getAuthor();

			if ($user->exists())
			{
				$username       = $user->getName();
				$this->userLink = $this->message->userid ? HTMLHelper::_('link',
					'index.php?option=com_kunena&view=user&layout=moderate&userid=' . $this->message->userid,
					$username . ' (' . $this->message->userid . ')', $username . ' (' . $this->message->userid . ')'
				)
					: null;
			}
		}

		if ($this->message)
		{
			$banHistory = KunenaBan::getUserHistory($this->message->userid);
			$me         = Factory::getApplication()->getIdentity();

			// Get thread and reply count from current message:
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('COUNT(mm.id) AS replies')
				->from($db->quoteName('#__kunena_messages', 'm'))
				->innerJoin($db->quoteName('#__kunena_messages', 't') . ' ON m.thread=t.id')
				->leftJoin($db->quoteName('#__kunena_messages', 'mm') . ' ON mm.thread=m.thread AND mm.time > m.time')
				->where('m.id=' . $db->quote($this->message->id));
			$query->setLimit(1);
			$db->setQuery($query);

			try
			{
				$replies = $db->loadResult();
			}
			catch (ExecutionFailureException $e)
			{
				KunenaError::displayDatabaseError($e);

				return;
			}
		}

		$banInfo = KunenaBan::getInstanceByUserid($this->app->getIdentity()->id, true);
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
				$this->setTitle($this->title);
			}

			if (!empty($params_description))
			{
				$description = $params->get('menu-meta_description');
				$this->setDescription($description);
			}
			else
			{
				$this->setDescription($this->title);
			}
		}
	}
}
