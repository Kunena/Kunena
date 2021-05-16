<?php
/**
 * Kunena Component
 *
 * @package         Kunena.Administrator
 * @subpackage      Controllers
 *
 * @copyright       Copyright (C) 2008 - 2021 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/

namespace Kunena\Forum\Administrator\Controller;

defined('_JEXEC') or die();

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Utilities\ArrayHelper;
use Kunena\Forum\Libraries\Bbcode\KunenaBBCodeEditor;
use function defined;

/**
 * Kunena Plugins Controller
 *
 * @since   Kunena 2.0
 */
class PluginsController extends AdminController
{
	/**
	 * @var     null|string
	 * @since   Kunena 6.0
	 */
	protected $baseurl = null;

	/**
	 * @var string
	 * @since version
	 */
	private $baseurl2;

	/**
	 * @var string
	 * @since version
	 */
	private $textPrefix = 'COM_PLUGINS';

	/**
	 * @var string
	 * @since version
	 */
	private $viewList = 'plugins';

	/**
	 * Construct
	 *
	 * @param   array  $config  config
	 *
	 * @throws  Exception
	 * @since   Kunena 2.0
	 */
	public function __construct($config = [])
	{
		$this->option = 'com_kunena';

		parent::__construct($config);
		$this->baseurl    = 'administrator/index.php?option=com_kunena&view=plugins';
		$this->baseurl2   = 'administrator/index.php?option=com_kunena&view=plugins';
		$this->viewList   = 'plugins';
		$this->textPrefix = 'COM_PLUGINS';

		// Value = 0
		$this->registerTask('unpublish', 'publish');

		// Value = 2
		$this->registerTask('archive', 'publish');

		// Value = -2
		$this->registerTask('trash', 'publish');

		// Value = -3
		$this->registerTask('report', 'publish');
		$this->registerTask('orderup', 'reOrder');
		$this->registerTask('orderdown', 'reOrder');

		Factory::getLanguage()->load('com_plugins', JPATH_ADMINISTRATOR);
	}

	/**
	 * Method to publish a list of items
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 * @since   12.2
	 */
	public function publish()
	{
		// Check for request forgeries
		Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

		// Get items to publish from the request.
		$cid   = $this->input->get('cid', [], 'array');
		$cid   = ArrayHelper::toInteger($cid, []);
		$data  = ['publish' => 1, 'unpublish' => 0, 'archive' => 2, 'trash' => -2, 'report' => -3];
		$task  = $this->getTask();
		$value = ArrayHelper::getValue($data, $task, 0, 'int');

		if (empty($cid))
		{
			Log::add(Text::_($this->textPrefix . '_NO_ITEM_SELECTED'), Log::WARNING, 'jerror');
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			// Make sure the item ids are integers
			$cid = ArrayHelper::toInteger($cid);

			// Publish the items.
			if (!$model->publish($cid, $value))
			{
				Log::add($model->getError(), Log::WARNING, 'jerror');
			}
			else
			{
				if ($value == 1)
				{
					$ntext = $this->textPrefix . '_N_ITEMS_PUBLISHED';
				}
				elseif ($value == 0)
				{
					$ntext = $this->textPrefix . '_N_ITEMS_UNPUBLISHED';
				}
				elseif ($value == 2)
				{
					$ntext = $this->textPrefix . '_N_ITEMS_ARCHIVED';
				}
				else
				{
					$ntext = $this->textPrefix . '_N_ITEMS_TRASHED';
				}

				$this->setMessage(Text::plural($ntext, count($cid)));
			}
		}

		$editor = KunenaBBCodeEditor::getInstance();
		$editor->initializeHMVC();

		$extension    = $this->input->get('extension');
		$extensionURL = ($extension) ? '&extension=' . $extension : '';
		$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->viewList . $extensionURL, false));
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 *
	 * @since   Kunena 2.0
	 */
	public function getModel($name = 'Plugin', $prefix = 'Administrator', $config = array('ignore_request' => true)): object
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Changes the order of one or more records.
	 *
	 * @return  boolean  True on success
	 *
	 * @throws  Exception
	 * @since   12.2
	 */
	public function reOrder(): bool
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$ids = $this->input->get('cid', [], 'array');
		$ids = ArrayHelper::toInteger($ids, []);

		$inc = ($this->getTask() == 'orderup') ? -1 : +1;

		$model  = $this->getModel();
		$return = $model->reOrder($ids, $inc);

		if ($return === false)
		{
			// Reorder failed.
			$message = Text::sprintf('JLIB_APPLICATION_ERROR_REORDER_FAILED', $model->getError());
			$this->setRedirect(
				Route::_('index.php?option=' . $this->option . '&view=' . $this->viewList, false),
				$message,
				'error'
			);

			return false;
		}

		// Reorder succeeded.
		$message = Text::_('JLIB_APPLICATION_SUCCESS_ITEM_REORDERED');
		$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->viewList, false), $message);

		return true;
	}

	/**
	 * Method to save the submitted ordering values for records.
	 *
	 * @return  boolean  True on success
	 *
	 * @since   12.2
	 */
	public function saveOrder(): bool
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		// Get the input
		$pks = $this->input->get('cid', [], 'array');
		$pks = ArrayHelper::toInteger($pks, []);

		$order = $this->input->get('order', [], 'array');
		$order = ArrayHelper::toInteger($order, []);

		// Get the model
		$model = $this->getModel();

		// Save the ordering
		$return = $model->saveOrder($pks, $order);

		if ($return === false)
		{
			// Reorder failed
			$message = Text::sprintf('JLIB_APPLICATION_ERROR_REORDER_FAILED', $model->getError());
			$this->setRedirect(
				Route::_('index.php?option=' . $this->option . '&view=' . $this->viewList, false),
				$message,
				'error'
			);

			return false;
		}

		// Reorder succeeded.
		$this->setMessage(Text::_('JLIB_APPLICATION_SUCCESS_ORDERING_SAVED'));
		$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->viewList, false));

		return true;
	}

	/**
	 * Check in of one or more records.
	 *
	 * @return  boolean  True on success
	 *
	 * @throws  Exception
	 * @since   12.2
	 */
	public function checkIn(): bool
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$cid = $this->input->get('cid', [], 'array');
		$ids = ArrayHelper::toInteger($cid, []);

		$model  = $this->getModel();
		$return = $model->checkIn($ids);

		if ($return === false)
		{
			// Checkin failed.
			$message = Text::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError());
			$this->setRedirect(
				Route::_('index.php?option=' . $this->option . '&view=' . $this->viewList, false),
				$message,
				'error'
			);

			return false;
		}

		$editor = KunenaBbcodeEditor::getInstance();
		$editor->initializeHMVC();

		// Checkin succeeded.
		$message = Text::plural($this->textPrefix . '_N_ITEMS_CHECKED_IN', count($ids));
		$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->viewList, false), $message);

		return true;
	}

	/**
	 * Regenerate editor file
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 * @since   Kunena 5.0.2
	 */
	public function resync(): void
	{
		$editor = KunenaBbcodeEditor::getInstance();
		$editor->initializeHMVC();

		$message = 'Sync done';
		$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->viewList, false), $message);
	}
}
