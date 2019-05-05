<?php
/**
 * Kunena Component
 * @package         Kunena.Template.Crypsis
 * @subpackage      Layout.Widget
 *
 * @copyright       Copyright (C) 2008 - 2019 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$this->addStyleSheet('assets/css/bootstrap.datepicker.css');
$this->addScript('assets/js/bootstrap.datepicker.js');
$this->addScriptDeclaration(';(function($){

		$.fn.datepicker.dates[\'kunena\'] = {
			days: ["' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_DAYS_SUNDAY') . '", "' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_DAYS_MONDAY') . '", "' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_DAYS_TUESDAY') . '", "' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_DAYS_WEDNESDAY') . '", "' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_DAYS_THURSDAY') . '", "' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_DAYS_FRIDAY') . '", "' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_DAYS_SATURDAY') . '"],
			daysShort: ["' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_DAYSSHORT_SUNDAY') . '", "' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_DAYSSHORT_MONDAY') . '", "' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_DAYSSHORT_TUESDAY') . '", "' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_DAYSSHORT_WEDNESDAY') . '", "' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_DAYSSHORT_THURSDAY') . '", "' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_DAYSSHORT_FRIDAY') . '", "' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_DAYSSHORT_SATURDAY') . '"],
			daysMin: ["' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_DAYSMIN_SUNDAY') . '", "' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_DAYSMIN_MONDAY') . '", "' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_DAYSMIN_TUESDAY') . '", "' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_DAYSMIN_WEDNESDAY') . '", "' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_DAYSMIN_THURSDAY') . '", "' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_DAYSMIN_FRIDAY') . '", "' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_DAYSMIN_SATURDAY') . '"],
			months: ["' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_MONTHS_JANUARY') . '", "' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_MONTHS_FEBRUARY') . '", "' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_MONTHS_MARCH') . '", "' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_MONTHS_APRIL') . '", "' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_MONTHS_MAY'). '", "' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_MONTHS_JUNE') . '", "' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_MONTHS_JULY') . '", "' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_MONTHS_AUGUST') . '", "' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_MONTHS_SEPTEMBER') . '", "' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_MONTHS_OCTOBER') . '", "' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_MONTHS_NOVEMBER') . '", "' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_MONTHS_DECEMBER') . '"],
			SHORT: ["' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_SHORT_JANUARY') . '", "' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_SHORT_FEBRUARY') . '", "' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_SHORT_MARCH') . '", "' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_SHORT_APRIL') . '", "' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_SHORT_MAY') . '", "' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_SHORT_JUNE') . '", "' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_SHORT_JULY') . '", "' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_SHORT_AUGUST') . '", "' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_SHORT_SEPTEMBER') . '", "' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_SHORT_OCTOBER') . '", "' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_SHORT_NOVEMBER') . '", "' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_SHORT_DECEMBER') . '"],
			today: "' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_TODAY') . '",
			monthsTitle: "' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_MONTHS_TITLE') . '",
			clear: "' . Text::_('COM_KUNENA_BOOTSTRAP_DATEPICKER_CLEAR') . '",
			weekStart: 1,
			format: "dd/mm/yyyy"
		};   
	
}(jQuery));');

