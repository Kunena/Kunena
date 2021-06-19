<?php
/**
 * Kunena Plugin
 *
 * @package         Kunena.Plugins
 * @subpackage      Comprofiler
 *
 * @copyright       Copyright (C) 2008 - 2021 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/

namespace Kunena\Forum\Plugin\Kunena\Comprofiler;

defined('_JEXEC') or die();

use Exception;
use Joomla\CMS\Factory;
use Kunena\Forum\Libraries\Forum\Topic\KunenaTopic;
use Kunena\Forum\Libraries\Integration\KunenaActivity;
use function defined;

require_once __DIR__ . '/integration.php';

/**
 * Class KunenaActivityComprofiler
 *
 * @since   Kunena 6.0
 */
class KunenaActivityComprofiler extends KunenaActivity
{
	/**
	 * @var     null
	 * @since   Kunena 6.0
	 */
	protected $params = null;

	/**
	 * KunenaActivityComprofiler constructor.
	 *
	 * @param   object  $params  params
	 *
	 * @throws Exception
	 * @since   Kunena 6.0
	 */
	public function __construct(object $params)
	{
		$this->params = $params;

		parent::__construct();
	}

	/**
	 * @param   int  $userid  userid
	 *
	 * @return  null
	 *
	 * @throws Exception
	 * @since   Kunena 6.0
	 *
	 */
	public function getUserPoints(int $userid)
	{
		$points = null;
		$params = ['userid' => $userid, 'points' => &$points];
		KunenaIntegrationComprofiler::trigger('getUserPoints', $params);

		return $points;
	}

	/**
	 * @param   string  $message  message
	 *
	 * @return  void
	 *
	 * @throws Exception
	 * @since   Kunena 6.0
	 *
	 */
	public function onBeforePost(string $message): void
	{
		$params = ['actor' => $message->get('userid'), 'replyto' => 0, 'message' => $message];
		KunenaIntegrationComprofiler::trigger('onBeforePost', $params);
	}

	/**
	 * @param   string  $message  message
	 *
	 * @return  void
	 *
	 * @throws Exception
	 * @since   Kunena 6.0
	 *
	 */
	public function onBeforeReply(string $message): void
	{
		$params = ['actor' => $message->get('userid'), 'replyto' => (int) $message->getParent()->userid, 'message' => $message];
		KunenaIntegrationComprofiler::trigger('onBeforeReply', $params);
	}

	/**
	 * @param   string  $message  message
	 *
	 * @return  void
	 *
	 * @throws Exception
	 * @since   Kunena 6.0
	 *
	 */
	public function onBeforeEdit(string $message): void
	{
		$params = ['actor' => $message->get('modified_by'), 'message' => $message];
		KunenaIntegrationComprofiler::trigger('onBeforeEdit', $params);
	}

	/**
	 * @param   string  $message  message
	 *
	 * @return  void
	 *
	 * @throws Exception
	 * @since   Kunena 6.0
	 *
	 */
	public function onAfterPost(string $message): void
	{
		$params = ['actor' => $message->get('userid'), 'replyto' => 0, 'message' => $message];
		KunenaIntegrationComprofiler::trigger('onAfterPost', $params);
	}

	/**
	 * @param   string  $message  message
	 *
	 * @return  void
	 *
	 * @throws Exception
	 * @since   Kunena 6.0
	 *
	 */
	public function onAfterReply(string $message): void
	{
		$params = ['actor' => $message->get('userid'), 'replyto' => (int) $message->getParent()->userid, 'message' => $message];
		KunenaIntegrationComprofiler::trigger('onAfterReply', $params);
	}

	/**
	 * @param   string  $message  message
	 *
	 * @return  void
	 *
	 * @throws Exception
	 * @since   Kunena 6.0
	 *
	 */
	public function onAfterEdit(string $message): void
	{
		$params = ['actor' => $message->get('modified_by'), 'message' => $message];
		KunenaIntegrationComprofiler::trigger('onAfterEdit', $params);
	}

	/**
	 * @param   string  $message  message
	 *
	 * @return  void
	 *
	 * @throws Exception
	 * @since   Kunena 6.0
	 *
	 */
	public function onAfterDelete(string $message): void
	{
		$my     = Factory::getApplication()->getIdentity();
		$params = ['actor' => $my->id, 'message' => $message];
		KunenaIntegrationComprofiler::trigger('onAfterDelete', $params);
	}

	/**
	 * @param   string  $message  message
	 *
	 * @return  void
	 *
	 * @throws Exception
	 * @since   Kunena 6.0
	 *
	 */
	public function onAfterUndelete(string $message): void
	{
		$my     = Factory::getApplication()->getIdentity();
		$params = ['actor' => $my->id, 'message' => $message];
		KunenaIntegrationComprofiler::trigger('onAfterUndelete', $params);
	}

	/**
	 * @param   int  $actor    actor
	 * @param   int  $target   target
	 * @param   int  $message  message
	 *
	 * @return  void
	 *
	 * @throws Exception
	 * @since   Kunena 6.0
	 *
	 */
	public function onAfterThankyou(int $actor, int $target, int $message): void
	{
		$params = ['actor' => $actor, 'target' => $target, 'message' => $message];
		KunenaIntegrationComprofiler::trigger('onAfterThankyou', $params);
	}

	/**
	 * @param   KunenaTopic  $topic   topic
	 * @param   string       $action  action
	 *
	 * @return  void
	 *
	 * @throws Exception
	 * @since   Kunena 6.0
	 *
	 */
	public function onAfterSubscribe(KunenaTopic $topic, string $action): void
	{
		$my     = Factory::getApplication()->getIdentity();
		$params = ['actor' => $my->id, 'topic' => $topic, 'action' => $action];
		KunenaIntegrationComprofiler::trigger('onAfterSubscribe', $params);
	}

	/**
	 * @param   KunenaTopic  $topic   topic
	 * @param   int          $action  action
	 *
	 * @return  void
	 *
	 * @throws Exception
	 * @since   Kunena 6.0
	 *
	 */
	public function onAfterFavorite(KunenaTopic $topic, int $action): void
	{
		$my     = Factory::getApplication()->getIdentity();
		$params = ['actor' => $my->id, 'topic' => $topic, 'action' => $action];
		KunenaIntegrationComprofiler::trigger('onAfterFavorite', $params);
	}

	/**
	 * @param   KunenaTopic  $topic   topic
	 * @param   int          $action  action
	 *
	 * @return  void
	 *
	 * @throws Exception
	 * @since   Kunena 6.0
	 *
	 */
	public function onAfterSticky(KunenaTopic $topic, int $action): void
	{
		$my     = Factory::getApplication()->getIdentity();
		$params = ['actor' => $my->id, 'topic' => $topic, 'action' => $action];
		KunenaIntegrationComprofiler::trigger('onAfterSticky', $params);
	}

	/**
	 * @param   KunenaTopic  $topic   topic
	 * @param   int          $action  action
	 *
	 * @return  void
	 *
	 * @throws Exception
	 * @since   Kunena 6.0
	 *
	 */
	public function onAfterLock(KunenaTopic $topic, int $action): void
	{
		$my     = Factory::getApplication()->getIdentity();
		$params = ['actor' => $my->id, 'topic' => $topic, 'action' => $action];
		KunenaIntegrationComprofiler::trigger('onAfterLock', $params);
	}

	/**
	 * @param   int  $target  target
	 * @param   int  $actor   actor
	 * @param   int  $delta   delta
	 *
	 * @return  void
	 *
	 * @throws Exception
	 * @since   Kunena 6.0
	 *
	 */
	public function onAfterKarma(int $target, int $actor, int $delta): void
	{
		$params = ['actor' => $actor, 'target' => $target, 'delta' => $delta];
		KunenaIntegrationComprofiler::trigger('onAfterKarma', $params);
	}
}
