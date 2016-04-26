<?php
/**
 * Kunena Component
 * @package    Kunena.Framework
 *
 * @copyright  (C) 2008 - 2016 Kunena Team. All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.kunena.org
 **/
defined('_JEXEC') or die();

/**
 * Kunena View Class
 */
class KunenaView extends JViewLegacy
{

	public $document = null;
	public $app = null;
	public $me = null;
	public $config = null;
	public $embedded = false;
	public $templatefiles = array();
	public $teaser = null;

	protected $inLayout = 0;
	protected $_row = 0;

	/**
	 * @param   array $config
	 *
	 * @throws Exception
	 */
	public function __construct($config = array())
	{
		$name = isset($config['name']) ? $config['name'] : $this->getName();
		$this->document = JFactory::getDocument();
		$this->document->setBase('');
		$this->profiler = KunenaProfiler::instance('Kunena');
		$this->app = JFactory::getApplication();
		$this->me = KunenaUserHelper::getMyself();
		$this->config = KunenaFactory::getConfig();
		$this->ktemplate = KunenaFactory::getTemplate();

		// Set the default template search path
		if ($this->app->isSite() && !isset($config['template_path']))
		{
			$config['template_path'] = $this->ktemplate->getTemplatePaths("html/$name", true);
		}

		if ($this->app->isAdmin())
		{
			$templateAdmin = KunenaFactory::getAdminTemplate();
			$templateAdmin->initialize();

			$config['template_path'] = $templateAdmin->getTemplatePaths($name);
		}

		parent::__construct($config);

		if ($this->app->isSite())
		{
			// Add another template file lookup path specific to the current template
			$fallback = JPATH_THEMES . "/{$this->app->getTemplate()}/html/com_kunena/{$this->ktemplate->name}/{$this->getName()}";
			$this->addTemplatePath($fallback);
		}

		// Use our own browser side cache settings.
		JFactory::getApplication()->allowCache(false);
		JFactory::getApplication()->setHeader('Expires', 'Mon, 1 Jan 2001 00:00:00 GMT', true);
		JFactory::getApplication()->setHeader('Last-Modified', gmdate("D, d M Y H:i:s") . ' GMT', true);
		JFactory::getApplication()->setHeader('Cache-Control', 'no-store, must-revalidate, post-check=0, pre-check=0', true);
	}

	/**
	 * @throws Exception
	 */
	public function displayAll()
	{
		if ($this->inLayout)
		{
			throw new LogicException(sprintf('HMVC template should not call %s::%s()', __CLASS__, __FUNCTION__));
		}

		$this->displayLayout();
	}

	/**
	 * @param   null $layout
	 * @param   null $tpl
	 *
	 * @return mixed|void
	 * @throws Exception
	 */
	public function displayLayout($layout = null, $tpl = null)
	{
		if ($this->inLayout)
		{
			throw new LogicException(sprintf('HMVC template should not call %s::%s()', __CLASS__, __FUNCTION__));
		}

		if ($layout)
		{
			$this->setLayout($layout);
		}

		$view = $this->getName();
		$layout = $this->getLayout();
		$viewName = ucfirst($view);
		$layoutName = ucfirst($layout);
		$layoutFunction = 'display' . $layoutName;

		KUNENA_PROFILER ? $this->profiler->start("display {$viewName}/{$layoutName}") : null;

		if (!$this->embedded && isset($this->common))
		{
			if ($this->config->board_offline && !$this->me->isAdmin())
			{
				// Forum is offline
				JFactory::getApplication()->setHeader('Status', '503 Service Temporarily Unavailable', true);
				$this->common->header = JText::_('COM_KUNENA_FORUM_IS_OFFLINE');
				$this->common->body = $this->config->offline_message;
				$this->common->html = true;
				$this->common->display('default');
				KUNENA_PROFILER ? $this->profiler->stop("display {$viewName}/{$layoutName}") : null;

				return;
			}
			elseif ($this->config->regonly && ! $this->me->exists() && ! $this->teaser)
			{
				// Forum is for registered users only
				JFactory::getApplication()->setHeader('Status', '403 Forbidden', true);
				$this->common->header = JText::_('COM_KUNENA_LOGIN_NOTIFICATION');
				$this->common->body = JText::_('COM_KUNENA_LOGIN_FORUM');
				$this->common->display('default');
				KUNENA_PROFILER ? $this->profiler->stop("display {$viewName}/{$layoutName}") : null;

				return;
			}
			elseif (!method_exists($this, $layoutFunction) && !is_file(KPATH_SITE . "/views/{$view}/{$layout}.php"))
			{
				// Layout was not found (don't allow Joomla to raise an error)
				$this->displayError(array(JText::_('COM_KUNENA_NO_ACCESS')), 404);
				KUNENA_PROFILER ? $this->profiler->stop("display {$viewName}/{$layoutName}") : null;

				return;
			}
		}

		$this->state = $this->get('State');

		if (method_exists($this, $layoutFunction))
		{
			$contents = $this->$layoutFunction($tpl ? $tpl : null);
		}
		elseif (method_exists($this, 'displayDefault'))
		{
			// TODO: should raise error instead, used just in case..
			$contents = $this->displayDefault($tpl ? $tpl : null);
		}
		else
		{
			// TODO: should raise error instead..
			$contents = $this->display($tpl ? $tpl : null);
		}

		KUNENA_PROFILER ? $this->profiler->stop("display {$viewName}/{$layoutName}") : null;

		return $contents;
	}

	/**
	 * Render new layout if available, otherwise continue to the old logic.
	 *
	 * @param   string $layout
	 * @param   string $tpl
	 * @param   array  $hmvcParams
	 * @throws LogicException
	 */
	public function render($layout, $tpl, array $hmvcParams = array())
	{
		if ($this->inLayout)
		{
			throw new LogicException(sprintf('HMVC template should not call %s::%s()', __CLASS__, __FUNCTION__));
		}

		if (isset($tpl) && $tpl == 'default')
		{
			$tpl = null;
		}

		if ($this->embedded)
		{
			// Support legacy embedded views.
			$file = isset($tpl) ? $this->getLayout() . '_' . $tpl : $this->getLayout();
			foreach ($this->_path['template'] as $path)
			{
				$found = !strstr($path, '/com_kunena/') && is_file($path . $file . '.php');

				if ($found)
				{
					$this->display($tpl);

					return;
				}
			}
		}

		// Support new layouts.
		$hmvc = KunenaLayout::factory($layout);
		if ($hmvc->getPath())
		{
			$this->inLayout++;
			if ($hmvcParams)
			{
				$hmvc->setProperties($hmvcParams);
			}

			echo $hmvc->setLegacy($this)->setLayout($tpl ? $tpl : $this->getLayout());

			$this->inLayout--;
		}
		else
		{
			$this->display($tpl);
		}
	}

	/**
	 * @param      $view
	 * @param      $layout
	 * @param   null $template
	 *
	 * @return $this
	 */
	public function displayTemplateFile($view, $layout, $template = null)
	{
		// HMVC legacy support.
		list($name, $override) = $this->ktemplate->mapLegacyView("{$view}/{$layout}_{$template}");
		$hmvc = KunenaLayout::factory($name)->setLayout($override);

		if ($hmvc->getPath())
		{
			return $hmvc->setLegacy($this);
		}

		// Old code.
		if (!isset($this->_path['template_' . $view]))
		{
			$this->_path['template_' . $view] = $this->_path['template'];

			foreach ($this->_path['template_' . $view] as &$dir)
			{
				$dir = preg_replace("#/{$this->_name}/$#", "/{$view}/", $dir);
			}
		}

		if ($template)
		{
			$template = '_' . $template;
		}

		$file = "{$layout}{$template}.php";
		$file = KunenaPath::find($this->_path['template_' . $view], $file);

		if (!is_file($file))
		{
			JError::raiseError(500, JText::sprintf('JLIB_APPLICATION_ERROR_LAYOUTFILE_NOT_FOUND', $file));
		}

		ob_start();
		include $file;
		$output = ob_get_contents();
		ob_end_clean();

		if (JDEBUG || $this->config->get('debug'))
		{
			$output = trim($output);
			$output = "\n<!-- START {$file} -->\n{$output}\n<!-- END {$file} -->\n";
		}

		echo $output;
	}

	/**
	 * Load a template file -- first look in the templates folder for an override
	 *
	 * @param   string  $tpl	The name of the template source file ...
	 * 					automatically searches the template paths and compiles as needed.
	 * @param   array   $hmvcParams	Extra parameters for HMVC.
	 * @return  string   The output of the the template script.
	 */
	public function loadTemplateFile($tpl = null, $hmvcParams = null)
	{
		KUNENA_PROFILER ? $this->profiler->start('function ' . __CLASS__ . '::' . __FUNCTION__ . '()') : null;

		// HMVC legacy support.
		$view = $this->getName();
		$layout = $this->getLayout();
		list($name, $override) = $this->ktemplate->mapLegacyView("{$view}/{$layout}_{$tpl}");
		$hmvc = KunenaLayout::factory($name)->setLayout($override);

		if ($hmvc->getPath())
		{
			if ($hmvcParams)
			{
				$hmvc->setProperties($hmvcParams);
			}

			KUNENA_PROFILER ? $this->profiler->stop('function ' . __CLASS__ . '::' . __FUNCTION__ . '()') : null;

			return $hmvc->setLegacy($this);
		}

		// Create the template file name based on the layout
		$file = isset($tpl) ? $layout . '_' . $tpl : $layout;

		if (!isset($this->templatefiles[$file]))
		{
			// Clean the file name
			$file = preg_replace('/[^A-Z0-9_\.-]/i', '', $file);
			$tpl  = isset($tpl) ? preg_replace('/[^A-Z0-9_\.-]/i', '', $tpl) : $tpl;

			// Load the template script
			$filetofind	= $this->_createFileName('template', array('name' => $file));
			$this->templatefiles[$file] = KunenaPath::find($this->_path['template'], $filetofind);
		}

		$this->_template = $this->templatefiles[$file];

		if ($this->_template != false)
		{
			$templatefile = preg_replace('%' . KunenaPath::clean(JPATH_ROOT, '/') . '/%', '', KunenaPath::clean($this->_template, '/'));

			// Unset so as not to introduce into template scope
			unset($tpl);
			unset($file);

			// Never allow a 'this' property
			if (isset($this->this))
			{
				unset($this->this);
			}

			// Start capturing output into a buffer
			ob_start();
			// Include the requested template filename in the local scope
			// (this will execute the view logic).
			include $this->_template;

			// Done with the requested template; get the buffer and
			// clear it.
			$output = ob_get_contents();
			ob_end_clean();

			if (JDEBUG || $this->config->get('debug'))
			{
				$output = trim($output);
				$output = "\n<!-- START {$templatefile} -->\n{$output}\n<!-- END {$templatefile} -->\n";
			}
		}
		else
		{
			$output = JError::raiseError(500, JText::sprintf('JLIB_APPLICATION_ERROR_LAYOUTFILE_NOT_FOUND', $this->getName() . '/' . $file));
		}

		KUNENA_PROFILER ? $this->profiler->stop('function ' . __CLASS__ . '::' . __FUNCTION__ . '()') : null;

		return $output;
	}
}
