<?php
/**
 * Kunena Component
 * @package Kunena.Site
 * @subpackage Views
 *
 * @copyright (C) 2008 - 2011 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined ( '_JEXEC' ) or die ();

/**
 * Search View
 */
class KunenaViewSearch extends KunenaView {
	function displayDefault($tpl = null) {
		$this->me = KunenaUserHelper::getMyself();
		$app = JFactory::getApplication ();

		$this->assignRef ( 'message_ordering', $this->me->getMessageOrdering() );

		$searchdatelist	= array();
		$searchdatelist[] 	= JHTML::_('select.option',  'lastvisit', JText::_('COM_KUNENA_SEARCH_DATE_LASTVISIT') );
		$searchdatelist[] 	= JHTML::_('select.option',  '1', JText::_('COM_KUNENA_SEARCH_DATE_YESTERDAY') );
		$searchdatelist[] 	= JHTML::_('select.option',  '7', JText::_('COM_KUNENA_SEARCH_DATE_WEEK') );
		$searchdatelist[] 	= JHTML::_('select.option',  '14',  JText::_('COM_KUNENA_SEARCH_DATE_2WEEKS') );
		$searchdatelist[] 	= JHTML::_('select.option',  '30', JText::_('COM_KUNENA_SEARCH_DATE_MONTH') );
		$searchdatelist[] 	= JHTML::_('select.option',  '90', JText::_('COM_KUNENA_SEARCH_DATE_3MONTHS') );
		$searchdatelist[] 	= JHTML::_('select.option',  '180', JText::_('COM_KUNENA_SEARCH_DATE_6MONTHS') );
		$searchdatelist[] 	= JHTML::_('select.option',  '365', JText::_('COM_KUNENA_SEARCH_DATE_YEAR') );
		$searchdatelist[] 	= JHTML::_('select.option',  'all', JText::_('COM_KUNENA_SEARCH_DATE_ANY') );
		$this->searchdatelist   = JHTML::_('select.genericlist',  $searchdatelist, 'searchdate', 'class="ks"', 'value', 'text',$this->state->get('query.searchdate') );

		$beforeafterlist	= array();
		$beforeafterlist[] 	= JHTML::_('select.option',  'after', JText::_('COM_KUNENA_SEARCH_DATE_NEWER') );
		$beforeafterlist[] 	= JHTML::_('select.option',  'before', JText::_('COM_KUNENA_SEARCH_DATE_OLDER') );
		$this->beforeafterlist= JHTML::_('select.genericlist',  $beforeafterlist, 'beforeafter', 'class="ks"', 'value', 'text',$this->state->get('query.beforeafter') );

		$sortbylist	= array();
		$sortbylist[] 	= JHTML::_('select.option',  'title', JText::_('COM_KUNENA_SEARCH_SORTBY_TITLE') );
		//$sortbylist[] 	= JHTML::_('select.option',  'replycount', JText::_('COM_KUNENA_SEARCH_SORTBY_POSTS') );
		$sortbylist[] 	= JHTML::_('select.option',  'views', JText::_('COM_KUNENA_SEARCH_SORTBY_VIEWS') );
		//$sortbylist[] 	= JHTML::_('select.option',  'threadstart', JText::_('COM_KUNENA_SEARCH_SORTBY_START') );
		$sortbylist[] 	= JHTML::_('select.option',  'lastpost', JText::_('COM_KUNENA_SEARCH_SORTBY_POST') );
		//$sortbylist[] 	= JHTML::_('select.option',  'postusername', JText::_('COM_KUNENA_SEARCH_SORTBY_USER') );
		$sortbylist[] 	= JHTML::_('select.option',  'forum', JText::_('COM_KUNENA_SEARCH_SORTBY_FORUM') );
		$this->sortbylist= JHTML::_('select.genericlist',  $sortbylist, 'sortby', 'class="ks"', 'value', 'text',$this->state->get('query.sortby') );

		// Limit value list
		$limitlist	= array();
		$limitlist[] 	= JHTML::_('select.option',  '5', JText::_('COM_KUNENA_SEARCH_LIMIT5') );
		$limitlist[] 	= JHTML::_('select.option',  '10', JText::_('COM_KUNENA_SEARCH_LIMIT10') );
		$limitlist[] 	= JHTML::_('select.option',  '15', JText::_('COM_KUNENA_SEARCH_LIMIT15') );
		$limitlist[] 	= JHTML::_('select.option',  '20', JText::_('COM_KUNENA_SEARCH_LIMIT20') );
		$this->limitlist= JHTML::_('select.genericlist',  $limitlist, 'limit', 'class="ks"', 'value', 'text',$this->state->get('list.limit') );

		//category select list
		$options = array ();
		$options [] = JHTML::_ ( 'select.option', '0', JText::_('COM_KUNENA_SEARCH_SEARCHIN_ALLCATS') );

		$cat_params = array ('sections'=>true);
		$selected = $this->state->get('query.catids');
		$this->categorylist = JHTML::_('kunenaforum.categorylist', 'catids[]', 0, $options, $cat_params, 'class="inputbox" size="8" multiple="multiple"', 'value', 'text', $selected);

		$this->searchwords = $this->get('SearchWords');

		$this->results = array ();
		$this->total = $this->get('Total');
		if ($this->total) {
			$this->results = $this->get('Results');
			$this->search_class = ' open';
			$this->search_style = ' style="display: none;"';
			$this->search_title = JText::_('COM_KUNENA_TOGGLER_EXPAND');
		} else {
			$this->search_class = ' close';
			$this->search_style = '';
			$this->search_title = JText::_('COM_KUNENA_TOGGLER_COLLAPSE');
		}

		$this->selected=' selected="selected"';
		$this->checked=' checked="checked"';
		$this->error = $this->get('Error');
		$this->display ();
	}

	function displaySearchResults() {
		if($this->results) {
			echo $this->loadTemplateFile('results');
		}
	}

	function displayRows() {
		$this->row(true);
		foreach ($this->results as $this->message) {
			$this->topic = $this->message->getTopic();
			$this->category = $this->message->getCategory();
			$this->categoryLink = $this->getCategoryLink($this->category->getParent()) . ' / ' . $this->getCategoryLink($this->category);
			$ressubject = KunenaHtmlParser::parseText ($this->message->subject);
			$resmessage = $this->parse ($this->message->message, 500);

			foreach ( $this->searchwords as $searchword ) {
				if (empty ( $searchword )) continue;
				$ressubject = preg_replace ( "/" . preg_quote ( $searchword, '/' ) . "/iu", '<span  class="searchword" >' . $searchword . '</span>', $ressubject );
				// FIXME: enable highlighting, but only after we can be sure that we do not break html
				//$resmessage = preg_replace ( "/" . preg_quote ( $searchword, '/' ) . "/iu", '<span  class="searchword" >' . $searchword . '</span>', $resmessage );
			}
			$this->author = $this->message->getAuthor();
			$this->topicAuthor = $this->topic->getAuthor();
			$this->topicTime = $this->topic->first_post_time;
			$this->subjectHtml = $ressubject;
			$this->messageHtml = $resmessage;

			$contents = $this->loadTemplateFile('row');
			$contents = preg_replace_callback('|\[K=(\w+)(?:\:([\w-_]+))?\]|', array($this, 'fillTopicInfo'), $contents);
			echo $contents;
		}
	}

	function fillTopicInfo($matches) {
		switch ($matches[1]) {
			case 'ROW':
				return $matches[2].$this->row().($this->topic->ordering ? " {$matches[2]}sticky" : '');
			case 'TOPIC_ICON':
				return $this->getTopicLink ( $this->topic, 'last', $this->topic->getIcon() );
			case 'DATE':
				$date = new KunenaDate($matches[2]);
				return $date->toSpan('config_post_dateformat', 'config_post_dateformat_hover');
		}
	}

	function getPagination($maxpages) {
		$pagination = new KunenaHtmlPagination ( $this->total, $this->state->get('list.start'), $this->state->get('list.limit') );
		$pagination->setDisplay($maxpages);
		return $pagination->getPagesLinks();
	}
}