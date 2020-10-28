/**
 * Kunena Component
 * @package Kunena.Media
 *
 * @copyright     Copyright (C) 2008 - 2020 Kunena Team. All rights reserved.
 * @license https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link https://www.kunena.org
 **/

CKEDITOR.plugins.add( 'confidential', {
	icons: 'confidential',
	init: function( editor ) {
		editor.addCommand( 'insertConfidential', {
			exec: function( editor ) {
				editor.insertHtml( '[confidential][/confidential]' );
			}
		});
		editor.ui.addButton( 'Confidential', {
			label: Joomla.JText._('COM_KUNENA_EDITOR_CONFIDENTIAL'),
			command: 'insertConfidential',
			toolbar: 'insert'
		});
	}
});