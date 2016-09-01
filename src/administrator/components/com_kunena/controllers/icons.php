<?php
/**
 * Kunena Component
 * @package     Kunena.Administrator
 * @subpackage  Controllers
 *
 * @copyright   (C) 2008 - 2016 Kunena Team. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link        https://www.kunena.org
 **/
defined('_JEXEC') or die();

/**
 * Kunena Backend Icons Controller
 *
 * @since  5.1
 */
class KunenaAdminControllerIcons extends KunenaController
{
	/**
	 * @var null|string
	 *
	 * @since    5.1
	 */
	protected $baseurl = null;

	/**
	 * Construct
	 *
	 * @param   array  $config  config
	 *
	 * @since    5.1
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->baseurl  = 'administrator/index.php?option=com_kunena&view=icons';
	}
}
