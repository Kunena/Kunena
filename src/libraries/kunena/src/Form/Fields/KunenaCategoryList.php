<?php
/**
 * Kunena Component
 *
 * @package       Kunena.Framework
 * @subpackage    Form
 *
 * @copyright     Copyright (C) 2008 - 2021 Kunena Team. All rights reserved.
 * @license       https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link          https://www.kunena.org
 **/

namespace Kunena\Forum\Libraries\Form\Fields;

defined('_JEXEC') or die();

use Exception;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Kunena\Forum\Libraries\Factory\KunenaFactory;
use Kunena\Forum\Libraries\Forum\KunenaForum;
use function defined;

/**
 * Class Joomla\CMS\Form\FormField|KunenaCategoryList
 *
 * @since   Kunena 6.0
 */
class KunenaCategoryList extends FormField
{
	/**
	 * @var     string
	 * @since   Kunena 6.0s
	 */
	protected $type = 'KunenaCategoryList';

	/**
	 * @return  string
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws  Exception
	 */
	protected function getInput(): string
	{
		if (!class_exists('Kunena\Forum\Libraries\Forum\KunenaForum') || !KunenaForum::installed())
		{
			echo '<a href="' . Route::_('index.php?option=com_kunena') . '">PLEASE COMPLETE KUNENA INSTALLATION</a>';

			return '';
		}

		KunenaFactory::loadLanguage('com_kunena');

		$size  = $this->element['size'];
		$class = $this->element['class'];

		$attribs = ' ';

		if ($size)
		{
			$attribs .= 'size="' . $size . '"';
		}

		if ($class)
		{
			$attribs .= 'class="' . $class . '"';
		}
		else
		{
			$attribs .= 'class="inputbox form-control"';
		}

		if (!empty($this->element['multiple']))
		{
			$attribs .= ' multiple="multiple"';
		}

		// Get the field options.
		$options = $this->getOptions();

		return HTMLHelper::_('select.genericlist', $options, $this->element, 'class="input-block-level" multiple="multiple" size="5"', $this->value);
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions(): array
	{
		// Initialize variables.
		$options = [];

		foreach ($this->element->children() as $option)
		{
			// Only add <option /> elements.
			if ($option->getName() != 'option')
			{
				continue;
			}

			// Create a new option object based on the <option /> element.
			$tmp = HTMLHelper::_('select.option', (string) $option['value'], Text::alt(trim((string) $option), preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)), 'value', 'text', ((string) $option['disabled'] == 'true'));

			// Set some option attributes.
			$tmp->class = (string) $option['class'];

			// Set some JavaScript option attributes.
			$tmp->onclick = (string) $option['onclick'];

			// Add the option object to the result set.
			$options[] = $tmp;
		}

		reset($options);

		return $options;
	}
}
