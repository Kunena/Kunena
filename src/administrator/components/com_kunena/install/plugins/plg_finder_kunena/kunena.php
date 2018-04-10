<?php
/**
 * Kunena Plugin
 *
 * @package         Kunena.Plugins
 * @subpackage      Finder
 *
 * @copyright       Copyright (C) 2008 - 2018 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/
defined('_JEXEC') or die('');

jimport('joomla.application.component.helper');

// Load the base adapter.
require_once JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer/adapter.php';

/**
 * Finder adapter for com_kunena.
 * @since Kunena
 */
class plgFinderKunena extends FinderIndexerAdapter
{
	/**
	 * The plugin identifier.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $context = 'Kunena';

	/**
	 * The extension name.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $extension = 'com_kunena';

	/**
	 * The sublayout to use when rendering the results.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $layout = 'topic';

	/**
	 * The mime type of the content the adapter indexes.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $mime = 'txt';

	/**
	 * The type of content that the adapter indexes.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $type_title = 'Forum Post';

	/**
	 * The field the published state is stored in.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $state_field = 'published';

	/**
	 * Method to reindex the link information for an item that has been saved.
	 * This event is fired before the data is actually saved so we are going
	 * to queue the item to be indexed later.
	 *
	 * @param   string                  $context The context of the content passed to the plugin.
	 * @param   \Joomla\CMS\Table\Table $row     A \Joomla\CMS\Table\Table object
	 * @param   boolean                 $isNew   If the content is just about to be created
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	public function onFinderBeforeSave($context, $row, $isNew)
	{
		// If a category will be change, we want to see, if the accesstype and access level has changed
		if (($row instanceof TableKunenaCategories) && !$isNew)
		{
			$old_table = clone $row;
			$old_table->load();
			$this->old_cataccess     = $old_table->access;
			$this->old_cataccesstype = $old_table->accesstype;
		}

		return true;
	}

	/**
	 * Method to determine if the access level of an item changed.
	 *
	 * @param   string                  $context The context of the content passed to the plugin.
	 * @param   \Joomla\CMS\Table\Table $row     A \Joomla\CMS\Table\Table object
	 * @param   boolean                 $isNew   If the content has just been created
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 * @throws null
	 */
	public function onFinderAfterSave($context, $row, $isNew)
	{
		// If a category has been changed, we want to check if the access has been changed
		if (($row instanceof TableKunenaCategories) && !$isNew)
		{
			// Access type of Category is still not the joomla access level system.
			// We didn't show them before and we don't show them now. No reindex necessary
			if ($row->accesstype != 'joomla.level' && $this->old_cataccesstype != 'joomla.level')
			{
				return true;
			}

			// Access level did not change. We do not need to reindex
			if ($row->accesstype == 'joomla.level' && $this->old_cataccesstype == 'joomla.level' && $row->access == $this->old_cataccess)
			{
				return true;
			}

			// Well, seems like an access level change has occured. So we need to reindex all messages within this category
			$messages = $this->getMessagesByCategory($row->id);

			foreach ($messages as $message)
			{
				$this->reindex($message->id);
			}

			return true;
		}

		// We only want to handle Kunena messages in here
		if ($row instanceof TableKunenaMessages)
		{
			// Reindex the item.
			$this->reindex($row->id);
		}

		return true;
	}

	/**
	 * Method to remove the link information for items that have been deleted.
	 * Since Messages are getting deleted in process of deleting categories or messages, we
	 * delete the finderresults before those objects are deleted.
	 *
	 * @param   string                  $context The context of the action being performed.
	 * @param   \Joomla\CMS\Table\Table $table   A \Joomla\CMS\Table\Table object containing the record to be deleted
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 * @throws null
	 */
	public function onFinderBeforeDelete($context, $table)
	{
		if ($table instanceof TableKunenaCategories)
		{
			$messages = $this->getMessagesByCategory($table->id);

			foreach ($messages as $message)
			{
				$this->remove($message->id);
			}

			return true;
		}
		elseif ($table instanceof TableKunenaTopics)
		{
			$messages = $this->getMessagesByTopic($table->id);

			foreach ($messages as $message)
			{
				$this->remove($message->id);
			}

			return true;
		}

		return true;
	}

	/**
	 * Method to remove the link information for items that have been deleted.
	 *
	 * @param   string                  $context The context of the action being performed.
	 * @param   \Joomla\CMS\Table\Table $table   A \Joomla\CMS\Table\Table object containing the record to be deleted
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	public function onFinderAfterDelete($context, $table)
	{
		if ($context == 'com_finder.index')
		{
			return $this->remove($table->link_id);
		}
		elseif ($table instanceof TableKunenaMessages)
		{
			return $this->remove($table->id);
		}

		return true;
	}

	/**
	 * Method to index a batch of content items. This method can be called by
	 * the indexer many times throughout the indexing process depending on how
	 * much content is available for indexing. It is important to track the
	 * progress correctly so we can display it to the user.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 * @throws  Exception on error.
	 * @throws null
	 */
	public function onBuildIndex()
	{
		\Joomla\CMS\Log\Log::add('FinderIndexerAdapter::onBuildIndex', \Joomla\CMS\Log\Log::INFO);

		// Get the indexer and adapter state.
		$iState = FinderIndexer::getState();
		$aState = $iState->pluginState[$this->context];

		// Check the progress of the indexer and the adapter.
		if ($iState->batchOffset == $iState->batchSize || $aState['offset'] == $aState['total'])
		{
			return true;
		}

		// Get the batch offset and size.
		$offset = (int) $aState['offset'];
		$limit  = (int) ($iState->batchSize - $iState->batchOffset);

		// Get the content items to index.
		$items = $this->getItems($offset, $limit);

		// Iterate through the items and index them.
		$item = null;

		foreach ($items as $item)
		{
			$this->index($item);
		}

		if ($item)
		{
			// Adjust the offsets.
			$iState->batchOffset = $iState->batchSize;
			$iState->totalItems  -= $item->id - $offset;

			// Update the indexer state.
			$aState['offset']                    = $item->id;
			$iState->pluginState[$this->context] = $aState;
			FinderIndexer::setState($iState);
		}

		unset($items, $item);

		return true;
	}

	/**
	 * Method to index an item. The item must be a FinderIndexerResult object.
	 *
	 * @param   FinderIndexerResult $item The item to index as an FinderIndexerResult object.
	 *
	 * @return  void
	 *
	 * @throws  Exception on database error.
	 * @since Kunena
	 */
	protected function index(FinderIndexerResult $item)
	{
		// Check if the extension is enabled
		if (\Joomla\CMS\Component\ComponentHelper::isEnabled($this->extension) == false)
		{
			return;
		}

		// Add the meta-data processing instructions.
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'author');

		// Add the type taxonomy data.
		$item->addTaxonomy('Type', 'Forum Post');

		// Add the author taxonomy data.
		if (!empty($item->author))
		{
			$item->addTaxonomy('Author', $item->author);
		}

		// Add the category taxonomy data.
		// $item->addTaxonomy('Category', $item->category, $item->cat_state, $item->cat_access);

		// Add the language taxonomy data.
		$item->addTaxonomy('Language', $item->language);

		// Get content extras.
		FinderIndexerHelper::getContentExtras($item);

		// Index the item.
		$this->indexer->index($item);
	}

	/**
	 * Method to setup the indexer to be run.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 * @throws Exception
	 */
	protected function setup()
	{
		// Initialize CLI
		$api = JPATH_ADMINISTRATOR . '/components/com_kunena/api.php';

		if (is_file($api))
		{
			require_once $api;
		}

		// Check if Kunena has been installed.
		if (!class_exists('KunenaForum') || !KunenaForum::isCompatible('4.0') || !KunenaForum::installed())
		{
			return false;
		}

		KunenaForum::setup();

		return true;
	}

	/**
	 * Method to get the number of content items available to index.
	 *
	 * @return  integer  The number of content items available to index.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	protected function getContentCount()
	{
		\Joomla\CMS\Log\Log::add('FinderIndexerAdapter::getContentCount', \Joomla\CMS\Log\Log::INFO);

		// Get the list query.
		$sql = $this->db->getQuery(true);
		$sql->select('MAX(id)')->from('#__kunena_messages');

		// Get the total number of content items to index.
		$this->db->setQuery($sql);

		try
		{
			$return = (int) $this->db->loadResult();
		}
		catch (Exception $e)
		{
			KunenaError::displayDatabaseError($e);
		}

		return $return;
	}

	/**
	 * Method to get a content item to index.
	 *
	 * @param   integer $id The id of the content item.
	 *
	 * @return  FinderIndexerResult  A FinderIndexerResult object.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 * @throws null
	 */
	protected function getItem($id)
	{
		\Joomla\CMS\Log\Log::add('FinderIndexerAdapter::getItem', \Joomla\CMS\Log\Log::INFO);

		$message = KunenaForumMessageHelper::get($id);

		// Convert the item to a result object.
		$item = $this->createIndexerResult($message);
		unset($message);

		// Why should we cleanup here? Maybe we need the instances later on?!
		// KunenaForumMessageHelper::cleanup();

		return $item;
	}

	/**
	 * Method to get a list of content items to index.
	 *
	 * @param   integer        $offset The list offset.
	 * @param   integer        $limit  The list limit.
	 * @param   JDatabaseQuery $sql    A JDatabaseQuery object. [optional]
	 *
	 * @return  array  An array of FinderIndexerResult objects.
	 *
	 * @throws Exception on database error.
	 * @throws null
	 * @since   2.5
	 */
	protected function getItems($offset, $limit, $sql = null)
	{
		\Joomla\CMS\Log\Log::add("FinderIndexerAdapter::getItems({$offset}, {$limit})", \Joomla\CMS\Log\Log::INFO);

		// Get the list query.
		$sql = $this->db->getQuery(true);
		$sql->select('id')->from('#__kunena_messages')->where('id>' . $this->db->quote($offset));

		// Get the content items to index.
		$this->db->setQuery($sql, 0, $limit);

		try
		{
			$ids = $this->db->loadColumn();
		}
		catch (Exception $e)
		{
			KunenaError::displayDatabaseError($e);
		}

		// Convert the items to result objects.
		$messages = KunenaForumMessageHelper::getMessages($ids, 'none');
		$items    = array();

		foreach ($messages as &$message)
		{
			$items[] = $this->createIndexerResult($message);
		}

		KunenaForumMessageHelper::cleanup();
		KunenaRoute::cleanup();

		return $items;
	}

	/**
	 * @param $message
	 *
	 * @return FinderIndexerResult
	 * @throws Exception
	 * @since Kunena
	 * @throws null
	 */
	protected function createIndexerResult($message)
	{
		// Convert the item to a result object.
		$item        = new FinderIndexerResult;
		$item->id    = $message->id;
		$item->catid = $message->catid;

		// Set title context.
		$item->title = $message->subject;

		// Build the necessary url, route, path and alias information.
		$item->url   = $this->getUrl($message->id, $this->extension, $this->layout);
		$item->route = $item->url . '&Itemid=' . KunenaRoute::getItemId($item->url);
		$item->path  = FinderIndexerHelper::getContentPath($item->url);
		$item->alias = KunenaRoute::stringURLSafe($message->subject);

		// Set body context.
		$item->body    = KunenaHtmlParser::stripBBCode($message->message);
		$item->summary = $item->body;

		// Set other information.
		$item->published = intval($message->hold == 0);
		$item->state     = intval($message->getCategory()->published == 1);
		$item->language  = '*';

		// TODO: add access control
		$item->access = $this->getAccessLevel($item->catid);

		// Set the item type.
		$item->type_id = $this->type_id;

		// Set the mime type.
		$item->mime = $this->mime;

		// Set the item layout.
		$item->layout = $this->layout;

		return $item;
	}

	/**
	 * Method to get the URL for the item. The URL is how we look up the link
	 * in the Finder index.
	 *
	 * @param   mixed  $id        The id of the item.
	 * @param   mixed  $extension Unused.
	 * @param   string $view      View name.
	 *
	 * @return    string        The URL of the item.
	 * @since Kunena
	 */
	protected function getUrl($id, $extension, $view)
	{
		$item = KunenaForumMessageHelper::get($id);

		return "index.php?option=com_kunena&view={$view}&catid={$item->catid}&id={$item->thread}&mesid={$item->id}";
	}

	/**
	 * Method to translate the native content states into states that the
	 * indexer can use.
	 *
	 * @param   integer $item     The item state.
	 * @param   integer $category The category state. [optional]
	 *
	 * @return  integer  The translated indexer state.
	 *
	 * @since   2.5
	 */
	protected function translateState($item, $category = null)
	{
		// If category is present, factor in its states as well
		if ($category !== null)
		{
			if ($category != 1)
			{
				$item = 0;
			}
		}

		// Translate the state
		return intval($item == 1);
	}

	/**
	 * @param $cat_id
	 *
	 * @return mixed
	 * @throws Exception
	 * @throws null
	 * @since Kunena
	 */
	protected function getMessagesByCategory($cat_id)
	{
		static $messages = array();

		if (!$messages[$cat_id])
		{
			$db    = \Joomla\CMS\Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('m.id');
			$query->from('#__kunena_messages as m');
			$query->join('INNER', '#__kunena_categories as c on m.catid = c.id');
			$query->where('c.id = ' . $db->quote($cat_id));
			$db->setQuery($query);
			$ids               = $db->loadColumn();
			$messages[$cat_id] = KunenaForumMessageHelper::getMessages($ids);
		}

		return $messages[$cat_id];
	}

	/**
	 * @param $topic_id
	 *
	 * @return mixed
	 * @since Kunena
	 */
	protected function getMessagesByTopic($topic_id)
	{
		static $messages = array();

		if (!$messages[$topic_id])
		{
			$db    = \Joomla\CMS\Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('m.*, t.message');
			$query->from('#__kunena_messages AS m');
			$query->join('INNER', '#__kunena_messages_text as t ON m.id = t.mesid');
			$query->where('m.thread = ' . $db->quote($topic_id));
			$db->setQuery($query);
			$results = $db->loadAssocList();
			$list    = array();

			foreach ($results as $result)
			{
				$list[] = new KunenaForumMessage($result);
			}

			$messages[$topic_id] = $list;
		}

		return $messages[$topic_id];
	}

	/**
	 * @param $item
	 *
	 * @return integer
	 * @since Kunena
	 */
	protected function getAccessLevel($item)
	{
		$category = KunenaForumCategoryHelper::get($item);
		$user     = \Joomla\CMS\Factory::getUser(0);

		// WORKAROUND: Joomla! 2.5.6 bug returning NULL if $userid = 0 and session is corrupted.
		if (!($user instanceof \Joomla\CMS\User\User))
		{
			$user = \Joomla\CMS\User\User::getInstance();
		}

		$accesslevels = (array) $user->getAuthorisedViewLevels();
		$groups_r     = (array) \Joomla\CMS\Access\Access::getGroupsByUser($user->id, true);
		$groups       = (array) \Joomla\CMS\Access\Access::getGroupsByUser($user->id, false);

		$catlist = array();

		// Check against Joomla access level
		if ($category->accesstype == 'joomla.level')
		{
			if (in_array($category->access, $accesslevels))
			{
				return 1;
			}
		}
		// Check against Joomla user group
		elseif ($category->accesstype == 'joomla.group')
		{
			$pub_access = in_array($category->pub_access, $category->pub_recurse ? $groups_r : $groups);

			if ($pub_access)
			{
				return 1;
			}
		}

		return 0;
	}
}
