<?php
/**
 * Kunena Component
 *
 * @package       Kunena.Administrator
 * @subpackage    Controllers
 *
 * @copyright     (C) 2008 - 2015 Kunena Team. All rights reserved.
 * @license       http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link          http://www.kunena.org
 **/
defined('_JEXEC') or die();

/**
 * Kunena Backend Config Controller
 *
 * @since 2.0
 */
class KunenaAdminControllerConfig extends KunenaController
{
	protected $baseurl = null;

	/**
	 * @param   array $config
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->baseurl       = 'administrator/index.php?option=com_kunena&view=config';
		$this->kunenabaseurl = 'administrator/index.php?option=com_kunena';
	}

	/**
	 *
	 */
	function apply()
	{
		$this->save($this->baseurl);
	}

	/**
	 * @param   null $url
	 */
	function save($url = null)
	{
		if (!JSession::checkToken('post'))
		{
			$this->app->enqueueMessage(JText::_('COM_KUNENA_ERROR_TOKEN'), 'error');
			$this->setRedirect(KunenaRoute::_($this->baseurl, false));

			return;
		}

		$properties = $this->config->getProperties();

		//Todo: fix depricated value
		foreach (JRequest::get('post', JREQUEST_ALLOWHTML) as $postsetting => $postvalue)
		{
			if (Joomla\String\StringHelper::strpos($postsetting, 'cfg_') === 0)
			{
				//remove cfg_ and force lower case
				if (is_array($postvalue))
				{
					$postvalue = implode(',', $postvalue);
				}

				$postname = Joomla\String\StringHelper::strtolower(Joomla\String\StringHelper::substr($postsetting, 4));

				// No matter what got posted, we only store config parameters defined
				// in the config class. Anything else posted gets ignored.
				if (array_key_exists($postname, $properties))
				{
					$this->config->set($postname, $postvalue);
				}
			}
		}

		$this->config->save();

		$this->app->enqueueMessage(JText::_('COM_KUNENA_CONFIGSAVED'));

		if (empty($url))
		{
			$this->setRedirect(KunenaRoute::_($this->kunenabaseurl, false));

			return;
		}

		$this->setRedirect(KunenaRoute::_($url, false));
	}

	/**
	 *
	 */
	function setdefault()
	{
		if (!JSession::checkToken('post'))
		{
			$this->app->enqueueMessage(JText::_('COM_KUNENA_ERROR_TOKEN'), 'error');
			$this->setRedirect(KunenaRoute::_($this->baseurl, false));

			return;
		}

		$this->config->reset();
		$this->config->save();

		$this->setRedirect('index.php?option=com_kunena&view=config', JText::_('COM_KUNENA_CONFIG_DEFAULT'));
	}
}
