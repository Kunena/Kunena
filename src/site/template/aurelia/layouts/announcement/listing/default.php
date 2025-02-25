<?php

/**
 * Kunena Component
 *
 * @package         Kunena.Template.Aurelia
 * @subpackage      Layout.Announcement
 *
 * @copyright       Copyright (C) 2008 - @currentyear@ Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/

defined('_JEXEC') or die();

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Kunena\Forum\Libraries\Route\KunenaRoute;

$options = $this->getOptions();
HTMLHelper::_('behavior.core');
?>

<form action="<?php echo KunenaRoute::_('index.php?option=com_kunena&view=announcement'); ?>" method="post"
      id="adminForm" name="adminForm">
    <input type="hidden" name="boxchecked" value="0"/>
    <?php echo HTMLHelper::_('form.token'); ?>

    <div class="row">
        <div class="col-md-7">
            <h2>
                <?php echo Text::_('COM_KUNENA_ANN_ANNOUNCEMENTS'); ?>
            </h2>
        </div>
        <div class="col-md-5 float-end">
            <?php if (!empty($options)) : ?>
                <div class="form-group">
                    <div class="input-group" role="group">
                        <?php echo HTMLHelper::_('select.genericlist', $options, 'task', 'class="form-select"', 'value', 'text', 0, 'kchecktask'); ?>
                        <input type="submit" name="kcheckgo" class="btn btn-outline-primary border"
                                value="<?php echo Text::_('COM_KUNENA_GO') ?>"/>
                        <a class="btn btn-outline-primary border"
                            href="<?php echo KunenaRoute::_('index.php?option=com_kunena&view=announcement&layout=create'); ?>">
                            <?php echo Text::_('COM_KUNENA_ANNOUNCEMENT_ACTIONS_LABEL_ADD'); ?>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <table class="table table-striped table-bordered">
        <thead>
        <tr>
            <th class="col-md-1">
                <?php echo Text::_('COM_KUNENA_ANN_DATE'); ?>
            </th>
            <th class="col-md-5">
                <?php echo Text::_('COM_KUNENA_ANN_TITLE'); ?>
            </th>

            <?php if ($options) :
                ?>
                <th class="col-md-1 center">
                    <?php echo Text::_('COM_KUNENA_ANN_PUBLISH'); ?>
                </th>
                <th class="col-md-1 center">
                    <?php echo Text::_('COM_KUNENA_ANN_EDIT'); ?>
                </th>
                <th class="col-md-1 center">
                    <?php echo Text::_('COM_KUNENA_ANN_DELETE'); ?>
                </th>
                <th class="col-md-1">
                    <?php echo Text::_('COM_KUNENA_ANNOUNCEMENT_AUTHOR'); ?>
                </th>
            <?php endif; ?>

            <th class="col-md-1 center">
                <?php echo Text::_('COM_KUNENA_ANN_ID'); ?>
            </th>

            <?php if ($options) :
                ?>
                <th class="col-md-1 center">
                    <input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);"/>
                </th>
            <?php endif; ?>

        </tr>
        </thead>

        <?php if ($this->pagination->pagesTotal > 1) :
            ?>
            <tfoot>
            <tr>
                <td colspan="<?php echo $options ? 8 : 3; ?>">
                    <div class="float-end">
                        <?php echo $this->subLayout('Widget/Pagination/List')->set('pagination', $this->pagination); ?>
                    </div>
                </td>
            </tr>
            </tfoot>
        <?php endif; ?>

        <tbody>
        <?php foreach ($this->announcements as $row => $announcement) {
            echo $this->subLayout('Announcement/Listing/Row')
                ->set('announcement', $announcement)
                ->set('row', $row)
                ->set('checkbox', !empty($options))
                ->set('config', $this->config);
        }
        ?>
        </tbody>
    </table>
</form>
