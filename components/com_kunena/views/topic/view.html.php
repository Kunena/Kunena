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
 * Topic View
 */
class KunenaViewTopic extends KunenaView {
	var $topic_subscribe = null;
	var $topic_favorite = null;
	var $topic_reply = null;
	var $topic_new = null;
	var $topic_sticky = null;
	var $topic_lock = null;
	var $topic_delete = null;
	var $topic_moderate = null;
	var $poll = null;
	var $mmm = 0;
	var $cache = true;

	function displayDefault($tpl = null) {
		$this->me = KunenaUserHelper::getMyself();
		$this->layout = $this->state->get('layout');
		if ($this->layout == 'flat') $this->layout = 'default';
		$this->setLayout($this->layout);
		$this->assignRef ( 'category', $this->get ( 'Category' ) );
		$this->assignRef ( 'topic', $this->get ( 'Topic' ) );
		$channels = $this->category->getChannels();
		if ($this->category->id && ! $this->category->authorise('read')) {
			// User is not allowed to see the category
			$this->setError($this->category->getError());
		} elseif (! $this->topic) {
			// Moved topic loop detected (1 -> 2 -> 3 -> 2)
			$this->setError(JText::_('COM_KUNENA_VIEW_TOPIC_ERROR_LOOP'));
		} elseif (! $this->topic->authorise('read')) {
			// User is not allowed to see the topic
			$this->setError($this->topic->getError());
		} elseif ($this->state->get('item.id') != $this->topic->id || ($this->category->id != $this->topic->category_id && !isset($channels[$this->topic->category_id])) || ($this->state->get('layout') != 'threaded' && $this->state->get('item.mesid'))) {
			// Topic has been moved or it doesn't belong to the current category
			$db = JFactory::getDBO();
			$mesid = $this->state->get('item.mesid');
			if (!$mesid) {
				$mesid = $this->topic->first_post_id;
			}
			$message = KunenaForumMessageHelper::get($mesid);
			if ($message->exists()) JFactory::getApplication()->redirect($message->getUrl(null, false));
		}

		$errors = $this->getErrors();
		if ($errors) {
			return $this->displayNoAccess($errors);
		}

		$messages	=& $this->get ( 'Messages' ) ;
		$totals		= $this->get ( 'Total' );

		// Run events
		$params = new JParameter( '' );
		$params->set('ksource', 'kunena');
		$params->set('kunena_view', 'topic');
		$params->set('kunena_layout', 'default');

		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('kunena');

		$dispatcher->trigger('onKunenaContentPrepare', array ('kunena.topic', &$this->topic, &$params, 0));
		$dispatcher->trigger('onKunenaContentPrepare', array ('kunena.messages', &$messages, &$params, 0));

		// Assign variables to template
		$this->assignRef ( 'messages', $messages );
		$this->assignRef ( 'total', $totals );

		// If page does not exist, redirect to the last page
		if ($this->total <= $this->state->get('list.start')) {
			JFactory::getApplication()->redirect($this->topic->getUrl(null, false, (int)($this->total / $this->state->get('list.limit'))));
		}

		$this->assignRef ( 'moderators', $this->get ( 'Moderators' ) );
		$this->assignRef ( 'usertopic',$this->topic->getUserTopic());
		$this->headerText =  JText::_('COM_KUNENA_MENU_LATEST_DESC');
		$this->title = JText::_('COM_KUNENA_ALL_DISCUSSIONS');
		$this->pagination = $this->getPagination ( 5 );
		$this->me = KunenaUserHelper::getMyself();
		$this->config = KunenaFactory::getConfig();

		// Mark topic read
		$this->topic->markRead ();
		$this->topic->hit ();

		// Check is subscriptions were sent and reset the value
		if ($this->topic->authorise('subscribe')) {
			$usertopic = $this->topic->getUserTopic();
			if ($usertopic->subscribed == 2) {
				$usertopic->subscribed = 1;
				$usertopic->save();
			}
		}

		$this->keywords = $this->topic->getKeywords(false, ', ');

		$this->buttons();

		// Get captcha & quick reply
		$this->captcha = KunenaSpamRecaptcha::getInstance();
		$this->quickreply = ($this->topic->authorise('reply',null, false) && $this->me->exists() && !$this->captcha->enabled());

		//meta description and keywords
		$page = intval ( $this->state->get('list.start') / $this->state->get('list.limit') ) + 1;
		$pages = intval ( $this->total / $this->state->get('list.limit') ) + 1;

		// TODO: use real keywords, too
		$metaKeys = $this->escape ( "{$this->topic->subject}, {$this->category->getParent()->name}, {$this->config->board_title}, " . JText::_('COM_KUNENA_GEN_FORUM') . ', ' . JFactory::getapplication()->getCfg ( 'sitename' ) );

		// Create Meta Description form the content of the first message
		// better for search results display but NOT for search ranking!
		$metaDesc = KunenaHtmlParser::stripBBCode($this->topic->first_post_message);
		$metaDesc = preg_replace('/\s+/', ' ', $metaDesc); // remove newlines
		$metaDesc = preg_replace('/^[^\w0-9]+/', '', $metaDesc); // remove characters at the beginning that are not letters or numbers
		$metaDesc = trim($metaDesc); // Remove trailing spaces and beginning

		// remove multiple spaces
		while (strpos($metaDesc, '  ') !== false){
			$metaDesc = str_replace('  ', ' ', $metaDesc);
		}

		// limit to 185 characters - google will cut off at ~150
		if (strlen($metaDesc) > 185){
			$metaDesc = rtrim(JString::substr($metaDesc, 0, 182)).'...';
		}

		$this->document->setMetadata ( 'keywords', $metaKeys );
		$this->document->setDescription ( $this->escape($metaDesc) );

		$this->setTitle(JText::sprintf('COM_KUNENA_VIEW_TOPICS_DEFAULT', $this->topic->subject) . " ({$page}/{$pages})");

		$this->display($tpl);
	}

	function displayFlat($tpl = null) {
		$this->state->set('layout', 'default');
		KunenaUserHelper::getMyself()->setTopicLayout ( 'flat' );
		$this->displayDefault($tpl);
	}

	function displayThreaded($tpl = null) {
		$this->state->set('layout', 'threaded');
		KunenaUserHelper::getMyself()->setTopicLayout ( 'threaded' );
		$this->displayDefault($tpl);
	}

	function displayIndented($tpl = null) {
		$this->state->set('layout', 'indented');
		KunenaUserHelper::getMyself()->setTopicLayout ( 'indented' );
		$this->displayDefault($tpl);
	}

	protected function DisplayCreate($tpl = null) {
		$captcha = KunenaSpamRecaptcha::getInstance();
		if ($captcha->enabled()) {
			$this->captchaHtml = $captcha->getHtml();
			if ( !$this->captchaHtml ) {
				$app = JFactory::getApplication();
				$app->enqueueMessage ( $captcha->getError(), 'error' );
				$this->redirectBack ();
			}
		}

		$saved = $this->app->getUserState('com_kunena.postfields');

		$this->setLayout('edit');
		$this->catid = $this->state->get('item.catid');
		$this->my = JFactory::getUser();
		$this->me = KunenaUserHelper::getMyself();
		$this->config = KunenaFactory::getConfig();
		if ($this->config->topicicons) {
			$this->topicIcons = $this->template->getTopicIcons(false, $saved ? $saved['icon_id'] : 0);
		}

		$categories = KunenaForumCategoryHelper::getCategories();
		$arrayanynomousbox = array();
		$arraypollcatid = array();
		foreach ($categories as $category) {
			if (!$category->isSection() && $category->allow_anonymous) {
				$arrayanynomousbox[] = '"'.$category->id.'":'.$category->post_anonymous;
			}
			if (!$category->isSection() && $category->allow_polls) {
				$arraypollcatid[] = '"'.$category->id.'":1';
			}
		}
		$arrayanynomousbox = implode(',',$arrayanynomousbox);
		$arraypollcatid = implode(',',$arraypollcatid);
		$this->document->addScriptDeclaration('var arrayanynomousbox={'.$arrayanynomousbox.'}');
		$this->document->addScriptDeclaration('var pollcategoriesid = {'.$arraypollcatid.'};');

		$cat_params = array ();
		$cat_params['ordering'] = 'ordering';
		$cat_params['toplevel'] = 0;
		$cat_params['sections'] = 0;
		$cat_params['direction'] = 1;
		$cat_params['hide_lonely'] = 1;
		$cat_params['action'] = 'topic.create';

		$this->category = KunenaForumCategoryHelper::get($this->catid);
		list ($this->topic, $this->message) = $this->category->newTopic($saved);

		if (!$this->topic->category_id) {
			$msg = JText::sprintf ( 'COM_KUNENA_POST_NEW_TOPIC_NO_PERMISSIONS', $this->topic->getError());
			$app = JFactory::getApplication();
			$app->enqueueMessage ( $msg, 'notice' );
			return false;
		}

		$this->selectcatlist = JHTML::_('kunenaforum.categorylist', 'catid', $this->catid, null, $cat_params, 'class="inputbox"', 'value', 'text', $saved ? $saved['catid'] : $this->topic->category_id, 'postcatid');

		$this->title = JText::_ ( 'COM_KUNENA_POST_NEW_TOPIC' );
		$this->action = 'post';

		$this->allowedExtensions = KunenaForumMessageAttachmentHelper::getExtensions($this->category);

		if ($arraypollcatid) $this->poll = $this->topic->getPoll();

		$this->post_anonymous = $saved ? $saved['anonymous'] : ! empty ( $this->category->post_anonymous );
		$this->subscriptionschecked = $saved ? $saved['subscribe'] : $this->config->subscriptionschecked == 1;
		$this->app->setUserState('com_kunena.postfields', null);

		$this->display($tpl);
	}

	protected function DisplayReply($tpl = null) {
		$captcha = KunenaSpamRecaptcha::getInstance();
		if ($captcha->enabled()) {
			$this->captchaHtml = $captcha->getHtml();
			if ( !$this->captchaHtml ) {
				$app = JFactory::getApplication();
				$app->enqueueMessage ( $captcha->getError(), 'error' );
				$this->redirectBack ();
			}
		}

		$saved = $this->app->getUserState('com_kunena.postfields');

		$this->setLayout('edit');
		$this->catid = $this->state->get('item.catid');
		$this->my = JFactory::getUser();
		$this->me = KunenaUserHelper::getMyself();
		$this->config = KunenaFactory::getConfig();
		$mesid = $this->state->get('item.mesid');
		if (!$mesid) {
			$this->topic = KunenaForumTopicHelper::get($this->state->get('item.id'));
			$parent = KunenaForumMessageHelper::get($this->topic->first_post_id);
		} else {
			$parent = KunenaForumMessageHelper::get($mesid);
			$this->topic = $parent->getTopic();
		}

		// Run events
		$params = new JParameter( '' );
		$params->set('ksource', 'kunena');
		$params->set('kunena_view', 'topic');
		$params->set('kunena_layout', 'reply');

		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('kunena');

		$dispatcher->trigger('onKunenaContentPrepare', array ('kunena.topic', &$this->topic, &$params, 0));

		if (!$parent->authorise('reply')) {
			$app = JFactory::getApplication();
			$app->enqueueMessage ( $parent->getError(), 'notice' );
			return false;
		}
		$quote = JRequest::getBool ( 'quote', false );
		$this->category = $this->topic->getCategory();
		if ($this->config->topicicons && $this->topic->authorise('edit', null, false)) {
			$this->topicIcons = $this->template->getTopicIcons(false, $saved ? $saved['icon_id'] : 0);
		}
		list ($this->topic, $this->message) = $parent->newReply($quote, $saved);
		$this->title = JText::_ ( 'COM_KUNENA_POST_REPLY_TOPIC' ) . ' ' . $this->topic->subject;
		$this->action = 'post';

		$this->allowedExtensions = KunenaForumMessageAttachmentHelper::getExtensions($this->category);

		$this->post_anonymous = $saved ? $saved['anonymous'] : ! empty ( $this->category->post_anonymous );
		$this->subscriptionschecked = $saved ? $saved['subscribe'] : $this->config->subscriptionschecked == 1;
		$this->app->setUserState('com_kunena.postfields', null);

		$this->display($tpl);
	}

	protected function displayEdit($tpl = null) {
		$this->catid = $this->state->get('item.catid');
		$this->my = JFactory::getUser();
		$this->me = KunenaUserHelper::getMyself();
		$this->config = KunenaFactory::getConfig();
		$mesid = $this->state->get('item.mesid');
		$document = JFactory::getDocument();

		$saved = $this->app->getUserState('com_kunena.postfields');

		$this->message = KunenaForumMessageHelper::get($mesid);
		if (!$this->message->authorise('edit')) {
			$app = JFactory::getApplication();
			$app->enqueueMessage ( $this->message->getError(), 'notice' );
			return false;
		}
		$this->topic = $this->message->getTopic();
		$this->category = $this->topic->getCategory();
		if ($this->config->topicicons && $this->topic->authorise('edit', null, false)) {
			$this->topicIcons = $this->template->getTopicIcons(false, $saved ? $saved['icon_id'] : $this->topic->icon_id);
		}

		// Run events
		$params = new JParameter( '' );
		$params->set('ksource', 'kunena');
		$params->set('kunena_view', 'topic');
		$params->set('kunena_layout', 'reply');

		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('kunena');

		$dispatcher->trigger('onKunenaContentPrepare', array ('kunena.topic', &$this->topic, &$params, 0));

		$this->title = JText::_ ( 'COM_KUNENA_POST_EDIT' ) . ' ' . $this->topic->subject;
		$this->action = 'edit';

		// Get attachments
		$this->attachments = $this->message->getAttachments();

		// Get poll
		if ($this->message->parent == 0 && ($this->topic->authorise('poll.create', null, false) || $this->topic->authorise('poll.edit', null, false))) {
			$this->poll = $this->topic->getPoll();
		}

		$this->allowedExtensions = KunenaForumMessageAttachmentHelper::getExtensions($this->category);

		if ($saved) {
			// Update message contents
			$this->message->edit ( $saved );
		}
		$this->post_anonymous = $saved ? $saved['anonymous'] : ! empty ( $this->category->post_anonymous );
		$this->subscriptionschecked = $saved ? $saved['subscribe'] : $this->config->subscriptionschecked == 1;
		$this->modified_reason = $saved ? $saved['modified_reason'] : '';
		$this->app->setUserState('com_kunena.postfields', null);

		$this->display($tpl);
	}

	function displayVote($tpl = null) {
		// TODO: need to check if poll is allowed in this category
		// TODO: need to check if poll is still active
		$this->config = KunenaFactory::getConfig();
		$this->assignRef ( 'category', $this->get ( 'Category' ) );
		$this->assignRef ( 'topic', $this->get ( 'Topic' ) );
		if (!$this->config->pollenabled || !$this->topic->poll_id || !$this->category->allow_polls) {
			return '';
		}

		$this->poll = $this->get('Poll');
		$this->usercount = $this->get('PollUserCount');
		$this->usersvoted = $this->get('PollUsers');
		$this->voted = $this->get('MyVotes');

		$this->display($tpl);
	}

	protected function displayReport($tpl = null) {
		$this->catid = $this->state->get('item.catid');
		$this->id = $this->state->get('item.id');
		$this->mesid = $this->state->get('item.mesid');
		$app = JFactory::getApplication();
		$config = KunenaFactory::getConfig ();
		$me = KunenaUserHelper::getMyself();

		if (!$me->exists() || $config->reportmsg == 0) {
			// Deny access if report feature has been disabled or user is guest
			$app->enqueueMessage ( JText::_ ( 'COM_KUNENA_NO_ACCESS' ), 'notice' );
			return;
		}
		if (!$this->mesid) {
			$this->topic = KunenaForumTopicHelper::get($this->id);
			if (!$this->topic->authorise('read')) {
				$app->enqueueMessage ( $this->topic->getError(), 'notice' );
				return;
			}
		} else {
			$this->message = KunenaForumMessageHelper::get($this->mesid);
			if (!$this->message->authorise('read')) {
				$app->enqueueMessage ( $this->message->getError(), 'notice' );
				return;
			}
			$this->topic = $this->message->getTopic();
		}
		$this->display($tpl);
	}

	protected function displayModerate($tpl = null) {
		$this->mesid = JRequest::getInt('mesid', 0);
		$this->id = $this->state->get('item.id');
		$this->catid = $this->state->get('item.catid');
		$this->config = KunenaFactory::getConfig();
		$app = JFactory::getApplication();

		if (!$this->mesid) {
			$this->topic = KunenaForumTopicHelper::get($this->id);
			if (!$this->topic->authorise('move')) {
				$app->enqueueMessage ( $this->topic->getError(), 'notice' );
				return;
			}
		} else {
			$this->message = KunenaForumMessageHelper::get($this->mesid);
			if (!$this->message->authorise('move')) {
				$app->enqueueMessage ( $this->message->getError(), 'notice' );
				return;
			}
			$this->topic = $this->message->getTopic();
		}
		$this->category = $this->topic->getCategory();

		$options =array ();
		if (!$this->mesid) {
			$options [] = JHTML::_ ( 'select.option', 0, JText::_ ( 'COM_KUNENA_MODERATION_MOVE_TOPIC' ) );
		} else {
			$options [] = JHTML::_ ( 'select.option', 0, JText::_ ( 'COM_KUNENA_MODERATION_CREATE_TOPIC' ) );
		}
		$options [] = JHTML::_ ( 'select.option', -1, JText::_ ( 'COM_KUNENA_MODERATION_ENTER_TOPIC' ) );

		$db = JFactory::getDBO();
		$params = array(
			'orderby'=>'tt.last_post_time DESC',
			'where'=>" AND tt.id != {$db->Quote($this->topic->id)} ");
		list ($total, $topics) = KunenaForumTopicHelper::getLatestTopics($this->catid, 0, 30, $params);
		foreach ( $topics as $cur ) {
			$options [] = JHTML::_ ( 'select.option', $cur->id, $this->escape ( $cur->subject ) );
		}
		$this->topiclist = JHTML::_ ( 'select.genericlist', $options, 'targettopic', 'class="inputbox"', 'value', 'text', 0, 'kmod_topics' );

		$options = array ();
		$cat_params = array ('sections'=>0, 'catid'=>0);
		$this->assignRef ( 'categorylist', JHTML::_('kunenaforum.categorylist', 'targetcategory', 0, $options, $cat_params, 'class="inputbox kmove_selectbox"', 'value', 'text', $this->catid, 'kmod_categories'));
		if (isset($this->message)) $this->user = KunenaFactory::getUser($this->message->userid);

		if ($this->mesid) {
			// Get thread and reply count from current message:
			$query = "SELECT COUNT(mm.id) AS replies FROM #__kunena_messages AS m
				INNER JOIN #__kunena_messages AS t ON m.thread=t.id
				LEFT JOIN #__kunena_messages AS mm ON mm.thread=m.thread AND mm.time > m.time
				WHERE m.id={$db->Quote($this->mesid)}";
			$db->setQuery ( $query, 0, 1 );
			$this->replies = $db->loadResult ();
			if (KunenaError::checkDatabaseError()) return;
		}

		$this->display($tpl);
	}

	function buttons() {
		$catid = $this->state->get('item.catid');
		$id = $this->state->get('item.id');

		// Subscribe topic
		if ($this->usertopic->subscribed) {
			// this user is allowed to unsubscribe
			$this->topic_subscribe = CKunenaLink::GetTopicPostLink ( 'unsubscribe', $catid, $id, $this->getButton ( 'subscribe', JText::_('COM_KUNENA_BUTTON_UNSUBSCRIBE_TOPIC') ), 'nofollow', 'kicon-button kbuttonuser btn-left', JText::_('COM_KUNENA_BUTTON_UNSUBSCRIBE_TOPIC_LONG') );
		} elseif ($this->topic->authorise('subscribe')) {
			// this user is allowed to subscribe
			$this->topic_subscribe = CKunenaLink::GetTopicPostLink ( 'subscribe', $catid, $id, $this->getButton ( 'subscribe', JText::_('COM_KUNENA_BUTTON_SUBSCRIBE_TOPIC') ), 'nofollow', 'kicon-button kbuttonuser btn-left', JText::_('COM_KUNENA_BUTTON_SUBSCRIBE_TOPIC_LONG') );
		}

		// Favorite topic
		if ($this->usertopic->favorite) {
			// this user is allowed to unfavorite
			$this->topic_favorite = CKunenaLink::GetTopicPostLink ( 'unfavorite', $catid, $id, $this->getButton ( 'favorite', JText::_('COM_KUNENA_BUTTON_UNFAVORITE_TOPIC') ), 'nofollow', 'kicon-button kbuttonuser btn-left', JText::_('COM_KUNENA_BUTTON_UNFAVORITE_TOPIC_LONG') );
		} elseif ($this->topic->authorise('favorite')) {
			// this user is allowed to add a favorite
			$this->topic_favorite = CKunenaLink::GetTopicPostLink ( 'favorite', $catid, $id, $this->getButton ( 'favorite', JText::_('COM_KUNENA_BUTTON_FAVORITE_TOPIC') ), 'nofollow', 'kicon-button kbuttonuser btn-left', JText::_('COM_KUNENA_BUTTON_FAVORITE_TOPIC_LONG') );
		}

		// Reply topic
		if ($this->topic->authorise('reply')) {
			// this user is allowed to reply to this topic
			$this->topic_reply = CKunenaLink::GetTopicPostReplyLink ( 'reply', $catid, $this->topic->id, $this->getButton ( 'reply', JText::_('COM_KUNENA_BUTTON_REPLY_TOPIC') ), 'nofollow', 'kicon-button kbuttoncomm btn-left', JText::_('COM_KUNENA_BUTTON_REPLY_TOPIC_LONG') );
		}

		// New topic
		if ($this->category->authorise('topic.create')) {
			//this user is allowed to post a new topic
			$this->topic_new = CKunenaLink::GetPostNewTopicLink ( $catid, $this->getButton ( 'newtopic', JText::_('COM_KUNENA_BUTTON_NEW_TOPIC') ), 'nofollow', 'kicon-button kbuttoncomm btn-left', JText::_('COM_KUNENA_BUTTON_NEW_TOPIC_LONG') );
		}

		// Moderator specific stuff
		if ($this->category->authorise('moderate')) {
			if (!$this->topic->ordering) {
				$this->topic_sticky = CKunenaLink::GetTopicPostLink ( 'sticky', $catid, $id, $this->getButton ( 'sticky', JText::_('COM_KUNENA_BUTTON_STICKY_TOPIC') ), 'nofollow', 'kicon-button kbuttonmod btn-left', JText::_('COM_KUNENA_BUTTON_STICKY_TOPIC_LONG') );
			} else {
				$this->topic_sticky = CKunenaLink::GetTopicPostLink ( 'unsticky', $catid, $id, $this->getButton ( 'sticky', JText::_('COM_KUNENA_BUTTON_UNSTICKY_TOPIC') ), 'nofollow', 'kicon-button kbuttonmod btn-left', JText::_('COM_KUNENA_BUTTON_UNSTICKY_TOPIC_LONG') );
			}

			if (!$this->topic->locked) {
				$this->topic_lock = CKunenaLink::GetTopicPostLink ( 'lock', $catid, $id, $this->getButton ( 'lock', JText::_('COM_KUNENA_BUTTON_LOCK_TOPIC') ), 'nofollow', 'kicon-button kbuttonmod btn-left', JText::_('COM_KUNENA_BUTTON_LOCK_TOPIC_LONG') );
			} else {
				$this->topic_lock = CKunenaLink::GetTopicPostLink ( 'unlock', $catid, $id, $this->getButton ( 'lock', JText::_('COM_KUNENA_BUTTON_UNLOCK_TOPIC') ), 'nofollow', 'kicon-button kbuttonmod btn-left', JText::_('COM_KUNENA_BUTTON_UNLOCK_TOPIC_LONG') );
			}
			$this->topic_delete = CKunenaLink::GetTopicPostLink ( 'deletethread', $catid, $id, $this->getButton ( 'delete', JText::_('COM_KUNENA_BUTTON_DELETE_TOPIC') ), 'nofollow', 'kicon-button kbuttonmod btn-left', JText::_('COM_KUNENA_BUTTON_DELETE_TOPIC_LONG') );
			$this->topic_moderate = CKunenaLink::GetTopicPostReplyLink ( 'moderatethread', $catid, $id, $this->getButton ( 'moderate', JText::_('COM_KUNENA_BUTTON_MODERATE_TOPIC') ), 'nofollow', 'kicon-button kbuttonmod btn-left', JText::_('COM_KUNENA_BUTTON_MODERATE') );
		}
	}

	function displayPoll() {
		// need to check if poll is allowed in this category
		if (!$this->config->pollenabled || !$this->topic->poll_id || !$this->category->allow_polls) {
			return '';
		}
		if ($this->getLayout() == 'poll') {
			$this->assignRef ( 'category', $this->get ( 'Category' ) );
			$this->assignRef ( 'topic', $this->get ( 'Topic' ) );
		}
		$this->poll = $this->get('Poll');
		$this->usercount = $this->get('PollUserCount');
		$this->usersvoted = $this->get('PollUsers');
		$this->voted = $this->get('MyVotes');

		$this->users_voted_list = array();
		if($this->config->pollresultsuserslist && !empty($this->usersvoted)) {
			$i = 0;
			foreach($this->usersvoted as $userid=>$vote) {
				if ( $i <= '4' ) $this->users_voted_list[] = CKunenaLink::GetProfileLink($userid);
				else $this->users_voted_morelist[] = CKunenaLink::GetProfileLink($userid);
				$i++;
			}
		}

		if ($this->voted) echo $this->loadTemplateFile("pollresults");
		else echo $this->loadTemplateFile("poll");
	}

	function displayTopicActions($location=0) {
		static $locations = array('top', 'bottom');

		$catid = $this->state->get('item.catid');
		$id = $this->state->get('item.id');
		$mesid = $this->state->get('item.mesid');
		$limitstart = $this->state->get('list.start');
		$limit =  $this->state->get('list.limit');

		$this->layout_buttons = array();
		if ($this->config->enable_threaded_layouts) {
			if ($this->layout != 'default') {
				$this->layout_buttons[] = CKunenaLink::GetUserLayoutLink('flat', $this->getButton ( 'layout-flat', JText::_('COM_KUNENA_BUTTON_LAYOUT_FLAT') ), JText::_('COM_KUNENA_BUTTON_LAYOUT_FLAT_LONG'), 'nofollow', 'kicon-button kbuttonuser btn-left');
			}
			if ($this->layout != 'threaded') {
				$this->layout_buttons[] = CKunenaLink::GetUserLayoutLink('threaded', $this->getButton ( 'layout-threaded', JText::_('COM_KUNENA_BUTTON_LAYOUT_THREADED') ), JText::_('COM_KUNENA_BUTTON_LAYOUT_THREADED_LONG'), 'nofollow', 'kicon-button kbuttonuser btn-left');
			}
			if ($this->layout != 'indented') {
				$this->layout_buttons[] = CKunenaLink::GetUserLayoutLink('indented', $this->getButton ( 'layout-indented', JText::_('COM_KUNENA_BUTTON_LAYOUT_INDENTED') ), JText::_('COM_KUNENA_BUTTON_LAYOUT_INDENTED_LONG'), 'nofollow', 'kicon-button kbuttonuser btn-left');
			}
		}
		$location ^= 1;
		$this->goto = '<a name="forum'.$locations[$location].'"></a>';
		$this->goto .= CKunenaLink::GetSamePageAnkerLink ( 'forum'.$locations[$location], $this->getIcon ( 'kforum'.$locations[$location], JText::_('COM_KUNENA_GEN_GOTO'.$locations[$location] ) ), 'nofollow', 'kbuttongoto');
		echo $this->loadTemplateFile('actions');
	}

	function displayMessageProfile() {
		echo $this->getMessageProfileBox();
	}

	function getMessageProfileBox() {
		static $profiles = array ();

		$key = $this->profile->userid.'.'.$this->profile->username;
		if (! isset ( $profiles [$key] )) {
			// Modify profile values by integration
			$triggerParams = array ('userid' => $this->profile->userid, 'userinfo' => &$this->profile );
			$integration = KunenaFactory::getProfile();
			$integration->trigger ( 'profileIntegration', $triggerParams );

			//karma points and buttons
			$me = KunenaUserHelper::getMyself();
			$this->userkarma_title = $this->userkarma_minus = $this->userkarma_plus = '';
			if ($this->config->showkarma && $this->profile->userid) {
				$this->userkarma_title = JText::_ ( 'COM_KUNENA_KARMA' ) . ": " . $this->profile->karma;
				if ($me->userid && $me->userid != $this->profile->userid) {
					$this->userkarma_minus = ' ' . CKunenaLink::GetKarmaLink ( 'decrease', $this->topic->category_id, $this->message->id, $this->profile->userid, '<span class="kkarma-minus" alt="Karma-" border="0" title="' . JText::_ ( 'COM_KUNENA_KARMA_SMITE' ) . '"> </span>' );
					$this->userkarma_plus = ' ' . CKunenaLink::GetKarmaLink ( 'increase', $this->topic->category_id, $this->message->id, $this->profile->userid, '<span class="kkarma-plus" alt="Karma+" border="0" title="' . JText::_ ( 'COM_KUNENA_KARMA_APPLAUD' ) . '"> </span>' );
				}
			}

			// FIXME: we need to change how profilebox integration works
			/*
			$integration = KunenaFactory::getProfile();
			$triggerParams = array(
				'username' => &$this->username,
				'messageobject' => &$this->msg,
				'subject' => &$this->subjectHtml,
				'messagetext' => &$this->messageHtml,
				'signature' => &$this->signatureHtml,
				'karma' => &$this->userkarma_title,
				'karmaplus' => &$this->userkarma_plus,
				'karmaminus' => &$this->userkarma_minus,
				'layout' => $direction
			);

			$profileHtml = $integration->showProfile($this->msg->userid, $triggerParams);
			*/
			$profileHtml = '';
			if ($profileHtml) {
				// Use integration
				$profiles [$key] = $profileHtml;
			} else {
				$usertype = $this->profile->getType($this->category->id, true);
				if ($me->exists() && $this->message->userid == $me->userid) $usertype = 'me';

				// TODO: add context (options, template) to caching
				$cache = JFactory::getCache('com_kunena', 'output');
				$cachekey = "profile.{$this->getTemplateMD5()}.{$this->profile->userid}.{$usertype}";
				$cachegroup = 'com_kunena.messages';

				$contents = $cache->get($cachekey, $cachegroup);
				if (!$contents) {
					$this->userkarma = "{$this->userkarma_title} {$this->userkarma_minus} {$this->userkarma_plus}";
					// Use kunena profile
					if ($this->config->showuserstats) {
						if ($this->config->userlist_usertype) {
							$this->usertype = $this->profile->getType ( $this->topic->category_id );
						} else {
							$this->usertype = null;
						}
						$this->userrankimage = $this->profile->getRank ( $this->topic->category_id, 'image' );
						$this->userranktitle = $this->profile->getRank ( $this->topic->category_id, 'title' );
						$this->userposts = $this->profile->posts;
						$activityIntegration = KunenaFactory::getActivityIntegration ();
						$this->thankyou = $this->profile->thankyou;
						$this->userpoints = $activityIntegration->getUserPoints ( $this->profile->userid );
						$this->usermedals = $activityIntegration->getUserMedals ( $this->profile->userid );
					} else {
						$this->usertype = null;
						$this->userrankimage = null;
						$this->userranktitle = null;
						$this->userposts = null;
						$this->thankyou = null;
						$this->userpoints = null;
						$this->usermedals = null;
					}
					$this->personalText = KunenaHtmlParser::parseText ( $this->profile->personalText );

					$contents = $this->loadTemplateFile('profile');
					if ($this->cache) $cache->store($contents, $cachekey, $cachegroup);
				}
				$profiles [$key] = $contents;
			}
		}
		return $profiles [$key];
	}

	function displayMessageContents() {
		echo $this->loadTemplateFile("message");
	}

	function displayMessageActions() {
		echo $this->getMessageActions();
	}
	function getMessageActions() {
		$me = KunenaUserHelper::getMyself();
		$catid = $this->state->get('item.catid');

		//Thankyou info and buttons
		$this->message_thankyou = '';
		$this->message_thankyou_delete = '';
		if ($this->config->showthankyou && $this->profile->userid) {
			$thankyou = $this->message->getThankyou();
			$this->thankyou = array();
			//TODO: for normal users, show only limited number of thankyou (config->thankyou_max)
			foreach( $thankyou->getList() as $userid=>$time){
				if ( $me->userid  && $me->isModerator()  ) {
					$this->message_thankyou_delete = '<a title="'.JText::_('COM_KUNENA_BUTTON_THANKYOU_REMOVE_LONG').'" href="'.KunenaRoute::_("index.php?option=com_kunena&view=topic&catid={$this->topic->category_id}&mesid={$this->message->id}&task=unthankyou&userid={$userid}&".JUtility::getToken() .'=1').'"><img src="'.$this->template->getImagePath('icons/publish_x.png').'" title="" alt="" /></a>';
				}
				$this->thankyou[] = CKunenaLink::GetProfileLink($userid).' '.$this->message_thankyou_delete;
			}
			if($me->userid && !$thankyou->exists($me->userid) && $me->userid != $this->profile->userid) {
				$this->message_thankyou = CKunenaLink::GetThankyouLink ( 'thankyou', $catid, $this->message->id, $this->profile->userid , $this->getButton ( 'thankyou', JText::_('COM_KUNENA_BUTTON_THANKYOU') ), JText::_('COM_KUNENA_BUTTON_THANKYOU_LONG'), 'kicon-button kbuttonuser btn-left');
			}

		}
		if ($this->config->reportmsg && KunenaUserHelper::getMyself()->exists()) {
			$this->message_report = CKunenaLink::GetReportMessageLink ( $catid, $this->message->id, $this->getButton ( 'report', JText::_('COM_KUNENA_BUTTON_REPORT') ), 'nofollow', 'kicon-button kbuttonuser btn-left', JText::_('COM_KUNENA_BUTTON_REPORT') );
		}

		$this->message_quickreply = $this->message_reply = $this->message_quote = '';
		if ($this->topic->authorise('reply')) {
			//user is allowed to reply/quote
			if ($this->quickreply) {
				$this->message_quickreply = CKunenaLink::GetTopicPostReplyLink ( 'reply', $catid, $this->message->id, $this->getButton ( 'reply', JText::_('COM_KUNENA_BUTTON_QUICKREPLY') ), 'nofollow', 'kicon-button kbuttoncomm btn-left kqreply', JText::_('COM_KUNENA_BUTTON_QUICKREPLY_LONG'), ' id="kreply'.$this->message->id.'"' );
			}
			$this->message_reply = CKunenaLink::GetTopicPostReplyLink ( 'reply', $catid, $this->message->id, $this->getButton ( 'reply', JText::_('COM_KUNENA_BUTTON_REPLY') ), 'nofollow', 'kicon-button kbuttoncomm btn-left', JText::_('COM_KUNENA_BUTTON_REPLY_LONG') );
			$this->message_quote = CKunenaLink::GetTopicPostReplyLink ( 'quote', $catid, $this->message->id, $this->getButton ( 'quote', JText::_('COM_KUNENA_BUTTON_QUOTE') ), 'nofollow', 'kicon-button kbuttoncomm btn-left', JText::_('COM_KUNENA_BUTTON_QUOTE_LONG') );
		} else {
			//user is not allowed to write a post
			if ($this->topic->locked) {
				$this->message_closed = JText::_('COM_KUNENA_POST_LOCK_SET');
			} else {
				$this->message_closed = JText::_('COM_KUNENA_VIEW_DISABLED');
			}
		}

		//Offer an moderator a few tools
		$this->message_edit = $this->message_moderate = '';
		$this->message_delete = $this->message_undelete = $this->message_permdelete = $this->message_publish = '';
		if ($me->isModerator ( $this->topic->category_id )) {
			unset($this->message_closed);
			$this->message_edit = CKunenaLink::GetTopicPostReplyLink ( 'edit', $catid, $this->message->id, $this->getButton ( 'edit', JText::_('COM_KUNENA_BUTTON_EDIT') ), 'nofollow', 'kicon-button kbuttonmod btn-left', JText::_('COM_KUNENA_BUTTON_EDIT_LONG') );
			$this->message_moderate = CKunenaLink::GetTopicPostReplyLink ( 'moderate', $catid, $this->message->id, $this->getButton ( 'moderate', JText::_('COM_KUNENA_BUTTON_MODERATE') ), 'nofollow', 'kicon-button kbuttonmod btn-left', JText::_('COM_KUNENA_BUTTON_MODERATE_LONG') );
			if ($this->message->hold == 1) {
				$this->message_publish = CKunenaLink::GetTopicPostLink ( 'approve', $catid, $this->message->id, $this->getButton ( 'approve', JText::_('COM_KUNENA_BUTTON_APPROVE') ), 'nofollow', 'kicon-button kbuttonmod btn-left', JText::_('COM_KUNENA_BUTTON_APPROVE_LONG') );
			}
			if ($this->message->hold == 2 || $this->message->hold == 3) {
				$this->message_undelete = CKunenaLink::GetTopicPostLink ( 'undelete', $catid, $this->message->id, $this->getButton ( 'undelete', JText::_('COM_KUNENA_BUTTON_UNDELETE') ), 'nofollow', 'kicon-button kbuttonmod btn-left', JText::_('COM_KUNENA_BUTTON_UNDELETE_LONG') );
				$this->message_permdelete = CKunenaLink::GetTopicPostLink ( 'permdelete', $catid, $this->message->id, $this->getButton ( 'permdelete', JText::_('COM_KUNENA_BUTTON_PERMDELETE') ), 'nofollow', 'kicon-button kbuttonmod btn-left', JText::_('COM_KUNENA_BUTTON_PERMDELETE_LONG') );
			} else {
				$this->message_delete = CKunenaLink::GetTopicPostLink ( 'delete', $catid, $this->message->id, $this->getButton ( 'delete', JText::_('COM_KUNENA_BUTTON_DELETE') ), 'nofollow', 'kicon-button kbuttonmod btn-left', JText::_('COM_KUNENA_BUTTON_DELETE_LONG') );
			}
		}
		else if ($this->message->authorise('edit')) {
			$this->message_edit = CKunenaLink::GetTopicPostReplyLink ( 'edit', $catid, $this->message->id, $this->getButton ( 'edit', JText::_('COM_KUNENA_BUTTON_EDIT') ), 'nofollow', 'kicon-button kbuttonmod btn-left', JText::_('COM_KUNENA_BUTTON_EDIT_LONG') );
			if ( $this->config->userdeletetmessage == '1' ) {
				if ($this->message->id == $this->topic->last_post_id) $this->message_delete = CKunenaLink::GetTopicPostLink ( 'delete', $catid, $this->message->id, $this->getButton ( 'delete', JText::_('COM_KUNENA_BUTTON_DELETE') ), 'nofollow', 'kicon-button kbuttonmod btn-left', JText::_('COM_KUNENA_BUTTON_DELETE_LONG') );
			} else if ( $this->config->userdeletetmessage == '2' ) {
				$this->message_delete = CKunenaLink::GetTopicPostLink ( 'delete', $catid, $this->message->id, $this->getButton ( 'delete', JText::_('COM_KUNENA_BUTTON_DELETE') ), 'nofollow', 'kicon-button kbuttonmod btn-left', JText::_('COM_KUNENA_BUTTON_DELETE_LONG') );
			}
		}
		return $this->loadTemplateFile("message_actions");
	}

	function displayMessage($id, $message, $template=null) {
		$layout = $this->getLayout();
		if (!$template) {
			$template = $this->state->get('profile.location');
			$this->setLayout('default');
		}

		$this->mmm ++;
		$this->message = $message;
		$this->profile = $this->message->getAuthor();
		$this->replynum = $id;
		$usertype = $this->me->getType($this->category->id, true);
		if ($usertype == 'user' && $this->message->userid == $this->profile->userid) $usertype = 'owner';

		// TODO: add context (options, template) to caching
		$cache = JFactory::getCache('com_kunena', 'output');
		$cachekey = "message.{$this->getTemplateMD5()}.{$layout}.{$template}.{$usertype}.c{$this->category->id}.m{$this->message->id}.{$this->message->modified_time}";
		$cachegroup = 'com_kunena.messages';

		$contents = $cache->get($cachekey, $cachegroup);
		if (!$contents) {

			//Show admins the IP address of the user:
			if ($this->message->ip && ($this->category->authorise('admin') || ($this->category->authorise('moderate') && !$this->config->hide_ip))) {
				$this->ipLink = CKunenaLink::GetMessageIPLink ( $this->message->ip );
			}
			$this->signatureHtml = KunenaHtmlParser::parseBBCode ( $this->profile->signature );
			$this->attachments = $this->message->getAttachments();

			// Link to individual message
			if ($this->config->ordering_system == 'replyid') {
				$this->numLink = CKunenaLink::GetSamePageAnkerLink( $message->id, '#[K=REPLYNO]' );
			} else {
				$this->numLink = CKunenaLink::GetSamePageAnkerLink ( $message->id, '#' . $message->id );
			}

			if ($this->message->hold == 0) {
				$this->class = 'kmsg';
			} elseif ($this->message->hold == 1) {
				$this->class = 'kmsg kunapproved';
			} else if ($this->message->hold == 2 || $this->message->hold == 3) {
				$this->class = 'kmsg kdeleted';
			}

			// New post suffix for class
			$this->msgsuffix = '';
			if ($this->message->isNew()) {
				$this->msgsuffix = '-new';
			}

			$contents = $this->loadTemplateFile($template);
			if ($usertype == 'guest') $contents = preg_replace_callback('|\[K=(\w+)(?:\:(\w+))?\]|', array($this, 'fillMessageInfo'), $contents);
			if ($this->cache) $cache->store($contents, $cachekey, $cachegroup);
		} elseif ($usertype == 'guest') {
			echo $contents;
			$this->setLayout($layout);
			return;
		}
		$contents = preg_replace_callback('|\[K=(\w+)(?:\:(\w+))?\]|', array($this, 'fillMessageInfo'), $contents);
		echo $contents;
		$this->setLayout($layout);
	}

	function fillMessageInfo($matches) {
		switch ($matches[1]) {
			case 'ROW':
				return $this->mmm & 1 ? 'odd' : 'even';
			case 'DATE':
				$date = new KunenaDate($matches[2]);
				return $date->toSpan('config_post_dateformat', 'config_post_dateformat_hover');
			case 'NEW':
				return $this->message->isNew() ? 'new' : 'old';
			case 'REPLYNO':
				return $this->replynum;
			case 'MESSAGE_PROFILE':
				return $this->getMessageProfileBox();
			case 'MESSAGE_ACTIONS':
				return $this->getMessageActions();
		}
	}

	function displayMessages() {
		foreach ( $this->messages as $id=>$message ) {
			$this->displayMessage($id, $message);
		}
	}

	function getPagination($maxpages) {
		$uri = KunenaRoute::normalize(null, true);
		$uri->delVar('mesid');
		$pagination = new KunenaHtmlPagination ( $this->total, $this->state->get('list.start'), $this->state->get('list.limit') );
		$pagination->setDisplay($maxpages, $uri);
		return $pagination->getPagesLinks();
	}
	// Helper functions

	function hasThreadHistory() {
		if (! $this->config->showhistory || !$this->topic->exists())
			return false;
		return true;
	}

	function displayThreadHistory() {
		if (! $this->hasThreadHistory())
			return;

		$db = JFactory::getDBO();
		$this->history = KunenaForumMessageHelper::getMessagesByTopic($this->topic, 0, (int) $this->config->historylimit, $ordering='DESC');
		$this->historycount = count ( $this->history );
		KunenaForumMessageAttachmentHelper::getByMessage($this->history);
		$userlist = array();
		foreach ($this->history as $message) {
			$userlist[(int) $message->userid] = (int) $message->userid;
		}
		KunenaUserHelper::loadUsers($userlist);

		// Run events
		$params = new JParameter( '' );
		$params->set('ksource', 'kunena');
		$params->set('kunena_view', 'topic');
		$params->set('kunena_layout', 'history');

		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('kunena');

		$dispatcher->trigger('onKunenaContentPrepare', array ('kunena.messages', &$this->history, &$params, 0));

		echo $this->loadTemplateFile ( 'history' );
	}

	function redirectBack() {
		$httpReferer = JRequest::getVar ( 'HTTP_REFERER', JURI::base ( true ), 'server' );
		$app = JFactory::getApplication();
		$app->redirect ( $httpReferer );
	}

	public function getNumLink($mesid, $replycnt) {
		if ($this->config->ordering_system == 'replyid') {
			$this->numLink = CKunenaLink::GetSamePageAnkerLink ( $mesid, '#' . $replycnt );
		} else {
			$this->numLink = CKunenaLink::GetSamePageAnkerLink ( $mesid, '#' . $mesid );
		}

		return $this->numLink;
	}

	function displayAttachments($message=null) {
		if ($message instanceof KunenaForumMessage) {
			$this->attachments = $message->getAttachments();
			if (!empty($this->attachments)) echo $this->loadTemplateFile ( 'attachments' );
		} else {
			echo JText::_('COM_KUNENA_ATTACHMENTS_ERROR_NO_MESSAGE');
		}
	}

	function canSubscribe() {
		if (! $this->my->id || ! $this->config->allowsubscriptions || $this->config->topic_subscriptions == 'disabled')
			return false;
		$usertopic = $this->topic->getUserTopic ();
		return ! $usertopic->subscribed;
	}
}