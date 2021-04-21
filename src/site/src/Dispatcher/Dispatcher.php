<?php

/**
 * @package         Joomla.Site
 * @subpackage      com_content
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Kunena\Forum\Site\Dispatcher;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Dispatcher\ComponentDispatcher;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Kunena\Forum\Libraries\Controller\KunenaController;
use Kunena\Forum\Libraries\Controller\KunenaControllerApplication;
use Kunena\Forum\Libraries\Error\KunenaError;
use Kunena\Forum\Libraries\Factory\KunenaFactory;
use Kunena\Forum\Libraries\Forum\KunenaForum;
use Kunena\Forum\Libraries\Profiler\KunenaProfiler;
use Kunena\Forum\Libraries\Route\KunenaRoute;
use Kunena\Forum\Libraries\User\KunenaUserHelper;

/**
 * ComponentDispatcher class for com_kunena
 *
 * @since  4.0.0
 */
class Dispatcher extends ComponentDispatcher
{
	public $option = 'com_kunena';

	/**
	 * Dispatch a controller task. Redirecting the user if appropriate.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function dispatch()
	{
	    // Display time it took to create the entire page in the footer.
	    $kunena_profiler = KunenaProfiler::instance('Kunena');
	    $kunena_profiler->start('Total Time');
	    KUNENA_PROFILER ? $kunena_profiler->mark('afterLoad') : null;
	    
	    // Initialize Kunena Framework.
	    KunenaForum::setup();
	    
	    // Initialize custom error handlers.
	    KunenaError::initialize();
	    
	    // Initialize session.
	    $ksession = KunenaFactory::getSession(true);
	    
	    if ($ksession->userid > 0)
	    {
	        // Create user if it does not exist
	        $kuser = KunenaUserHelper::getMyself();
	        
	        if (!$kuser->exists())
	        {
	            $kuser->save();
	        }
	        
	        // Save session
	        if (!$ksession->save())
	        {
	            Factory::getApplication()->enqueueMessage(Text::_('COM_KUNENA_ERROR_SESSION_SAVE_FAILED'), 'error');
	        }
	    }
	    
	    $app   = Factory::getApplication();
	    $input = $app->input;
	    $input->set('limitstart', $input->getInt('limitstart', $input->getInt('start')));
	    $view    = $input->getWord('func', $input->getWord('view', 'home'));
	    $subview = $input->getWord('layout', 'default');
	    $task    = $input->getCmd('task', 'display');
	    
	    // Import plugins and event listeners.
	    PluginHelper::importPlugin('kunena');
	    
	    // Get HMVC controller and if exists, execute it.
	    $controller = KunenaControllerApplication::getInstance($view, $subview, $task, $input, $app);
	    
	    if ($controller)
	    {
	        KunenaRoute::cacheLoad();
	        $contents = $controller->execute();
	        KunenaRoute::cacheStore();
	    }
	    elseif (is_file(KPATH_SITE . "/controllers/{$view}.php"))
	    {
	        // Execute old MVC.
	        // Legacy support: If the content layout doesn't exist on HMVC, load and execute the old controller.
	        $controller = KunenaController::getInstance();
	        KunenaRoute::cacheLoad();
	        ob_start();
	        $controller->execute($task);
	        $contents = ob_get_clean();
	        KunenaRoute::cacheStore();
	        $controller->redirect();
	    }
	    else
	    {
	        // Legacy URL support.
	        $uri = KunenaRoute::current(true);
	        
	        if ($uri)
	        {
	            // FIXME: using wrong Itemid
	            Factory::getApplication()->redirect(KunenaRoute::_($uri, false));
	        }
	        else
	        {
	            throw new \Exception("Kunena view '{$view}' not found", 404);
	        }
	    }
	    
	    // Prepare and display the output.
	    $params       = new \stdClass;
	    $params->text = '';
	    $topics       = new \stdClass;
	    $topics->text = '';
	    PluginHelper::importPlugin('content');
	    Factory::getApplication()->triggerEvent('onContentPrepare', array("com_kunena.{$view}", &$topics, &$params, 0));
	    Factory::getApplication()->triggerEvent('onKunenaBeforeRender', array("com_kunena.{$view}", &$contents));
	    $contents = (string) $contents;
	    Factory::getApplication()->triggerEvent('onKunenaAfterRender', array("com_kunena.{$view}", &$contents));
	    echo $contents;
	    
	    // Remove custom error handlers.
	    KunenaError::cleanup();
	    
	    // Display profiler information.
	    if (KUNENA_PROFILER)
	    {
	        $kunena_profiler->stop('Total Time');
	        
	        echo '<div class="kprofiler">';
	        echo "<h3>Kunena Profile Information</h3>";
	        
	        foreach ($kunena_profiler->getAll() as $item)
	        {
	            echo sprintf("Kunena %s: %0.3f / %0.3f seconds (%d calls)<br/>", $item->name, $item->getInternalTime(),
	                $item->getTotalTime(), $item->calls
	                );
	        }
	        
	        echo '</div>';
	    }
	}
}
