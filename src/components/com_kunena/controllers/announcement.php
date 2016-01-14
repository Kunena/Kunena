<?php
/**
 * Kunena Component
 *
 * @package       Kunena.Site
 * @subpackage    Controllers
 *
 * @copyright (C) 2008 - 2016 Kunena Team. All rights reserved.
 * @license       http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link          http://www.kunena.org
 **/
defined('_JEXEC') or die();

/**
 * Kunena Announcements Controller
 *
 * @since        2.0
 */
class KunenaControllerAnnouncement extends KunenaController
{

	/**
	 *
	 */
	public function none()
	{
		// FIXME: This is workaround for task=none on edit.
		$this->edit();
	}

	/**
	 * @throws Exception
	 */
	public function publish()
	{
		if (!JSession::checkToken('post'))
		{
			$this->app->enqueueMessage(JText::_('COM_KUNENA_ERROR_TOKEN'), 'error');
			$this->setRedirectBack();

			return;
		}

		$cid = JFactory::getApplication()->input->get('cid', array(), 'post', 'array'); // Array of integers
		Joomla\Utilities\ArrayHelper::toInteger($cid);

		foreach ($cid as $id)
		{
			$announcement = KunenaForumAnnouncementHelper::get($id);
			if ($announcement->published == 1)
			{
				continue;
			}

			$announcement->published = 1;
			if (!$announcement->authorise('edit') || !$announcement->save())
			{
				$this->app->enqueueMessage($announcement->getError(), 'error');
			}
			else
			{
				$this->app->enqueueMessage(JText::sprintf('COM_KUNENA_ANN_SUCCESS_PUBLISH', $this->escape($announcement->title)));
			}
		}

		$this->setRedirectBack();
	}

	/**
	 * @throws Exception
	 */
	public function unpublish()
	{
		if (!JSession::checkToken('post'))
		{
			$this->app->enqueueMessage(JText::_('COM_KUNENA_ERROR_TOKEN'), 'error');
			$this->setRedirectBack();

			return;
		}

		$cid = JFactory::getApplication()->input->get('cid', array(), 'post', 'array'); // Array of integers
		Joomla\Utilities\ArrayHelper::toInteger($cid);

		foreach ($cid as $id)
		{
			$announcement = KunenaForumAnnouncementHelper::get($id);

			if ($announcement->published == 0)
			{
				continue;
			}

			$announcement->published = 0;

			if (!$announcement->authorise('edit') || !$announcement->save())
			{
				$this->app->enqueueMessage($announcement->getError(), 'error');
			}
			else
			{
				$this->app->enqueueMessage(JText::sprintf('COM_KUNENA_ANN_SUCCESS_UNPUBLISH', $this->escape($announcement->title)));
			}
		}

		$this->setRedirectBack();
	}

	/**
	 * @throws Exception
	 */
	public function edit()
	{
		$cid = JFactory::getApplication()->input->get('cid', array(), 'post', 'array'); // Array of integers
		Joomla\Utilities\ArrayHelper::toInteger($cid);

		$announcement = KunenaForumAnnouncementHelper::get(array_pop($cid));

		$this->setRedirect($announcement->getUrl('edit', false));
	}

	/**
	 * @throws Exception
	 */
	public function delete()
	{
		if (!JSession::checkToken('request'))
		{
			$this->app->enqueueMessage(JText::_('COM_KUNENA_ERROR_TOKEN'), 'error');
			$this->setRedirectBack();

			return;
		}

		$cid = JFactory::getApplication()->input->get('cid', (array) JFactory::getApplication()->input->getInt('id'), 'post', 'array'); // Array of integers
		Joomla\Utilities\ArrayHelper::toInteger($cid);

		foreach ($cid as $id)
		{
			$announcement = KunenaForumAnnouncementHelper::get($id);

			if (!$announcement->authorise('delete') || !$announcement->delete())
			{
				$this->app->enqueueMessage($announcement->getError(), 'error');
			}
			else
			{
				$this->app->enqueueMessage(JText::_('COM_KUNENA_ANN_DELETED'));
			}
		}

		$this->setRedirect(KunenaForumAnnouncementHelper::getUrl('list', false));
	}

	/**
	 * @throws Exception
	 */
	public function save()
	{
		if (!JSession::checkToken('post'))
		{
			$this->app->enqueueMessage(JText::_('COM_KUNENA_ERROR_TOKEN'), 'error');
			$this->setRedirectBack();

			return;
		}

		$now                    = new JDate();
		$fields                 = array();
		$fields['title']        = JFactory::getApplication()->input->getString('title', '', 'post', 'raw');
		$fields['description']  = JFactory::getApplication()->input->getString('description', '', 'post', 'raw');
		$fields['sdescription'] = JFactory::getApplication()->input->getString('sdescription', '', 'post', 'raw');
		$fields['created']      = JFactory::getApplication()->input->getString('created', $now->toSql());
		$fields['published']    = JFactory::getApplication()->input->getInt('published', 1);
		$fields['showdate']     = JFactory::getApplication()->input->getInt('showdate', 1);

		$id           = JFactory::getApplication()->input->getInt('id');
		$announcement = KunenaForumAnnouncementHelper::get($id);
		$announcement->bind($fields);

		if (!$announcement->authorise($id ? 'edit' : 'create') || !$announcement->save())
		{
			$this->app->enqueueMessage($announcement->getError(), 'error');
			$this->setRedirectBack();

			return;
		}

		$this->app->enqueueMessage(JText::_($id ? 'COM_KUNENA_ANN_SUCCESS_EDIT' : 'COM_KUNENA_ANN_SUCCESS_ADD'));
		$this->setRedirect($announcement->getUrl('default', false));
	}
}
