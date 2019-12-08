<?php
/**
 * Kunena Component
 *
 * @package         Kunena.Site
 * @subpackage      Controller.User
 *
 * @copyright       Copyright (C) 2008 - 2019 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

/**
 * Class ComponentKunenaControllerUserEditUserDisplay
 *
 * @since  K4.0
 */
class ComponentKunenaControllerUserEditUserDisplay extends ComponentKunenaControllerUserEditDisplay
{
	/**
	 * @var string
	 * @since Kunena
	 */
	protected $name = 'User/Edit/User';

	/**
	 * @var
	 * @since Kunena
	 */
	public $changeUsername;

	/**
	 * @var
	 * @since Kunena
	 */
	public $frontendForm;

	/**
	 * Load user form.
	 *
	 * @return void
	 * @since Kunena
	 * @throws null
	 */
	protected function before()
	{
		parent::before();

		$userParams = ComponentHelper::getParams('com_users');

		// Check if user is allowed to change his name.
		$this->changeUsername = $userParams->get('change_login_name', 1);

		// Check to see if Frontend User Params have been enabled.
		if ($userParams->get('frontend_userparams', 0))
		{
			Factory::getLanguage()->load('com_users', JPATH_ADMINISTRATOR);

			Form::addFormPath(JPATH_ROOT . '/components/com_users/forms');
			Form::addFieldPath(JPATH_ROOT . '/components/com_users/models/fields');

			PluginHelper::importPlugin('user');

			$registry     = new Registry($this->user->params);
			$form         = Form::getInstance('com_users.profile', 'frontend');
			$data         = new StdClass;
			$data->params = $registry->toArray();
			Factory::getApplication()->triggerEvent('onContentPrepareForm', array($form, $data));

			$form->bind($data);
			$this->frontendForm = $form->getFieldset('params');
		}

		$this->headerText = Text::_('COM_KUNENA_PROFILE_EDIT_USER_TITLE');
	}

	/**
	 * Prepare document.
	 *
	 * @return void
	 * @since Kunena
	 * @throws Exception
	 */
	protected function prepareDocument()
	{
		$menu_item = $this->app->getMenu()->getActive();

		if ($menu_item)
		{
			$params             = $menu_item->params;
			$params_title       = $params->get('page_title');
			$params_keywords    = $params->get('menu-meta_keywords');
			$params_description = $params->get('menu-meta_description');

			if (!empty($params_title))
			{
				$title = $params->get('page_title');
				$this->setTitle($title);
			}
			else
			{
				$this->setTitle($this->headerText);
			}

			if (!empty($params_keywords))
			{
				$keywords = $params->get('menu-meta_keywords');
				$this->setKeywords($keywords);
			}
			else
			{
				$this->setKeywords($this->headerText);
			}

			if (!empty($params_description))
			{
				$description = $params->get('menu-meta_description');
				$this->setDescription($description);
			}
			else
			{
				$this->setDescription($this->headerText);
			}
		}
	}
}
