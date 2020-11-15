<?php
/**
 * Kunena Component
 * @package         Kunena.Site
 * @subpackage      Layout.widget
 *
 * @copyright       Copyright (C) 2008 - 2020 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * KunenaLayoutTopicEditEditor
 *
 * @since  K4.0
 */
class KunenaLayoutWidgetEditor extends KunenaLayout
{
	/**
	 * Get geshi code types.
	 *
	 * @return array|null
	 * @throws Exception
	 * @since Kunena
	 */
	public function getCodeTypes()
	{
		$templatesettings = KunenaFactory::getTemplate()->params;

		if (!$templatesettings->get('highlightcode'))
		{
			return;
		}

		$paths = array(
			JPATH_ROOT . '/plugins/content/geshiall/geshi/geshi',
			JPATH_ROOT . '/plugins/content/geshi/geshi/geshi',
		);

		foreach ($paths as $path)
		{
			if (!is_dir($path))
			{
				continue;
			}

			$files     = KunenaFolder::files($path, ".php");
			$options   = array();
			$options[] = HTMLHelper::_('select.option', '', Text::_('COM_KUNENA_EDITOR_CODE_TYPE'));

			foreach ($files as $file)
			{
				$options[] = HTMLHelper::_('select.option', substr($file, 0, -4), substr($file, 0, -4));
			}

			$list = HTMLHelper::_('select.genericlist', $options, 'kcodetype', 'class="kbutton form-control"', 'value', 'text', '-1');

			return $list;
		}

		return;
	}

	/**
	 * Define javascript variables to show or disable some bbcode buttons
	 *
	 * @return void
	 * @throws Exception
	 * @since Kunena
	 */
	public function getBBcodesEnabled()
	{
		$this->ktemplate  = KunenaFactory::getTemplate();
		$templatesettings = $this->ktemplate->params;
		$config           = KunenaConfig::getInstance();

		if ($config->showvideotag && $templatesettings->get('video'))
		{
			$this->addScriptDeclaration("kunena_showvideotag=1;");
		}
		else
		{
			$this->addScriptDeclaration("kunena_showvideotag=0;");
		}

		if (!$config->disemoticons && $templatesettings->get('emoticons'))
		{
			$this->addScriptDeclaration("kunena_disemoticons=1;");
		}
		else
		{
			$this->addScriptDeclaration("kunena_disemoticons=0;");
		}

		if ($config->showebaytag && $templatesettings->get('ebay'))
		{
			$this->addScriptDeclaration("kunena_showebaytag=1;");
		}
		else
		{
			$this->addScriptDeclaration("kunena_showebaytag=0;");
		}

		if ($templatesettings->get('spoiler'))
		{
			$this->addScriptDeclaration("kunena_showspoilertag=1;");
		}
		else
		{
			$this->addScriptDeclaration("kunena_showspoilertag=0;");
		}

		if ($templatesettings->get('maps'))
		{
			$this->addScriptDeclaration("kunena_showmapstag=1;");
		}
		else
		{
			$this->addScriptDeclaration("kunena_showmapstag=0;");
		}

		if ($templatesettings->get('twitter'))
		{
			$this->addScriptDeclaration("kunena_showtwittertag=1;");
		}
		else
		{
			$this->addScriptDeclaration("kunena_showtwittertag=0;");
		}

		if ($templatesettings->get('link'))
		{
			$this->addScriptDeclaration("kunena_showlinktag=1;");
		}
		else
		{
			$this->addScriptDeclaration("kunena_showlinktag=0;");
		}

		if ($templatesettings->get('picture'))
		{
			$this->addScriptDeclaration("kunena_showpicturetag=1;");
		}
		else
		{
			$this->addScriptDeclaration("kunena_showpicturetag=0;");
		}

		if ($templatesettings->get('hide'))
		{
			$this->addScriptDeclaration("kunena_showhidetag=1;");
		}
		else
		{
			$this->addScriptDeclaration("kunena_showhidetag=0;");
		}

		if ($templatesettings->get('table'))
		{
			$this->addScriptDeclaration("kunena_showtabletag=1;");
		}
		else
		{
			$this->addScriptDeclaration("kunena_showtabletag=0;");
		}

		if ($templatesettings->get('code'))
		{
			$this->addScriptDeclaration("kunena_showcodetag=1;");
		}
		else
		{
			$this->addScriptDeclaration("kunena_showcodetag=0;");
		}

		if ($templatesettings->get('quote'))
		{
			$this->addScriptDeclaration("kunena_showquotetag=1;");
		}
		else
		{
			$this->addScriptDeclaration("kunena_showquotetag=0;");
		}

		if ($templatesettings->get('divider'))
		{
			$this->addScriptDeclaration("kunena_showdividertag=1;");
		}
		else
		{
			$this->addScriptDeclaration("kunena_showdividertag=0;");
		}

		if ($templatesettings->get('instagram'))
		{
			$this->addScriptDeclaration("kunena_showinstagramtag=1;");
		}
		else
		{
			$this->addScriptDeclaration("kunena_showinstagramtag=0;");
		}

		if ($templatesettings->get('soundcloud'))
		{
			$this->addScriptDeclaration("kunena_showsoundcloudtag=1;");
		}
		else
		{
			$this->addScriptDeclaration("kunena_showsoundcloudtag=0;");
		}

		if ($templatesettings->get('confidential'))
		{
			$this->addScriptDeclaration("kunena_showconfidentialtag=1;");
		}
		else
		{
			$this->addScriptDeclaration("kunena_showconfidentialtag=0;");
		}

		if ($templatesettings->get('hr'))
		{
			$this->addScriptDeclaration("kunena_showhrtag=1;");
		}
		else
		{
			$this->addScriptDeclaration("kunena_showhrtag=0;");
		}

		if ($templatesettings->get('listitem'))
		{
			$this->addScriptDeclaration("kunena_showlistitemtag=1;");
		}
		else
		{
			$this->addScriptDeclaration("kunena_showlistitemtag=0;");
		}

		if ($templatesettings->get('supscript'))
		{
			$this->addScriptDeclaration("kunena_showsupscripttag=1;");
		}
		else
		{
			$this->addScriptDeclaration("kunena_showsupscripttag=0;");
		}

		if ($templatesettings->get('subscript'))
		{
			$this->addScriptDeclaration("kunena_showsubscripttag=1;");
		}
		else
		{
			$this->addScriptDeclaration("kunena_showsubscripttag=0;");
		}

		if ($templatesettings->get('numericlist'))
		{
			$this->addScriptDeclaration("kunena_shownumericlisttag=1;");
		}
		else
		{
			$this->addScriptDeclaration("kunena_shownumericlisttag=0;");
		}

		if ($templatesettings->get('bulletedlist'))
		{
			$this->addScriptDeclaration("kunena_showbulletedlisttag=1;");
		}
		else
		{
			$this->addScriptDeclaration("kunena_showbulletedlisttag=0;");
		}

		if ($templatesettings->get('alignright'))
		{
			$this->addScriptDeclaration("kunena_showalignrighttag=1;");
		}
		else
		{
			$this->addScriptDeclaration("kunena_showalignrighttag=0;");
		}

		if ($templatesettings->get('alignleft'))
		{
			$this->addScriptDeclaration("kunena_showalignlefttag=1;");
		}
		else
		{
			$this->addScriptDeclaration("kunena_showalignlefttag=0;");
		}

		if ($templatesettings->get('center'))
		{
			$this->addScriptDeclaration("kunena_showcentertag=1;");
		}
		else
		{
			$this->addScriptDeclaration("kunena_showcentertag=0;");
		}

		if ($templatesettings->get('underline'))
		{
			$this->addScriptDeclaration("kunena_showunderlinetag=1;");
		}
		else
		{
			$this->addScriptDeclaration("kunena_showunderlinetag=0;");
		}

		if ($templatesettings->get('italic'))
		{
			$this->addScriptDeclaration("kunena_showitalictag=1;");
		}
		else
		{
			$this->addScriptDeclaration("kunena_showitalictag=0;");
		}

		if ($templatesettings->get('bold'))
		{
			$this->addScriptDeclaration("kunena_showboldtag=1;");
		}
		else
		{
			$this->addScriptDeclaration("kunena_showboldtag=0;");
		}

		if ($templatesettings->get('strikethrough'))
		{
			$this->addScriptDeclaration("kunena_showstrikethroughtag=1;");
		}
		else
		{
			$this->addScriptDeclaration("kunena_showstrikethroughtag=0;");
		}

		if ($templatesettings->get('colors'))
		{
			$this->addScriptDeclaration("kunena_showcolorstag=1;");
		}
		else
		{
			$this->addScriptDeclaration("kunena_showcolorstag=0;");
		}

		if ($templatesettings->get('size'))
		{
			$this->addScriptDeclaration("kunena_showsizetag=1;");
		}
		else
		{
			$this->addScriptDeclaration("kunena_showsizetag=0;");
		}
	}

	/**
	 * Check if user is able to have images and links buttons in the editor
	 *
	 * @return void
	 * @since Kunena 5.2.0
	 */
	public function getAllowedtoUseLinksImages()
	{
		$this->ktemplate  = KunenaFactory::getTemplate();
		$templatesettings = $this->ktemplate->params;
		$config           = KunenaConfig::getInstance();
		$me = KunenaUserHelper::getMyself();

		if ($me->checkUserAllowedLinksImages())
		{
			$this->addScriptOptions('com_kunena.ckeditor_remove_buttons_url_image', $config->new_users_prevent_post_url_images);
			$editorbuttons = $templatesettings->get('editorButtons');

			if (empty($editorbuttons))
			{
				$templatesettings->set('editorButtons', 'Image,Link,Unlink');
			}
			else
			{
				if(strstr($editorbuttons, 'Image')!==false && strstr($editorbuttons, 'Link,Unlink')===false)
				{
					$editorbuttons .= ',Link,Unlink';
					$templatesettings->set('editorButtons', $editorbuttons);
				}
				elseif(strstr($editorbuttons, 'Link,Unlink')!==false && strstr($editorbuttons, 'Image')==false)
				{
					$editorbuttons .= ',Image';
					$templatesettings->set('editorButtons', $editorbuttons);
				}
				else
				{
					$editorbuttons .= ',Link,Unlink,Image';
					$templatesettings->set('editorButtons', $editorbuttons);
				}
			}
		}
	}
}
