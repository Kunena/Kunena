<?php

/**
 * Kunena Component
 *
 * @package       Kunena.Administrator
 * @subpackage    Views
 *
 * @copyright     Copyright (C) 2008 - @currentyear@ Kunena Team. All rights reserved.
 * @license       https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link          https://www.kunena.org
 **/

namespace Kunena\Forum\Administrator\View\Labels;

\defined('_JEXEC') or die();

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarFactoryInterface;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Labels view for Kunena backend
 *
 * @since 5.0
 * 
 * @deprecated Kunena 6.3 will be removed in Kunena 7.0 without replacement
 */
class HtmlView extends BaseHtmlView
{
    /**
     * @param   null  $tpl  tpl
     *
     * @return  void
     *
     * @since   Kunena 6.0
     *
     * @throws  Exception
     */
    public function displayDefault($tpl = null)
    {
        $state            = $this->get('state');
        $this->group      = $state->get('group');
        $this->items      = $this->get('items');
        $this->pagination = $this->get('Pagination');

        $document = Factory::getApplication()->getDocument();
        $document->setTitle(Text::_('Forum Labels'));

        $this->addToolbar();

        return parent::display($tpl);
    }

    /**
     * Set the toolbar on log manager
     *
     * @return  void
     *
     * @since   Kunena 6.0
     */
    protected function addToolbar(): void
    {
        // Get the toolbar object instance
        $this->bar = Factory::getContainer()->get(ToolbarFactoryInterface::class)->createToolbar('toolbar');

        // Set the title bar text
        ToolbarHelper::title(Text::_('COM_KUNENA') . ': ' . Text::_('COM_KUNENA_A_LABELS_MANAGER'));
    }
}
