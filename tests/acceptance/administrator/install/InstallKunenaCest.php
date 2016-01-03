<?php
/**
 * Kunena Package
 *
 * @package    Kunena.Package
 *
 * @copyright  (C) 2008 - 2015 Kunena Team. All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.kunena.org
 **/

class InstallKunenaCest
{
	/**
	 * Install Kunena
	 *
	 * @param AcceptanceTester $I
	 *
	 * @return InstallKunenaCest
	 */
	public function installKunena(\AcceptanceTester $I)
	{
		$I->doAdministratorLogin();
		$I->comment('Im going to install kunena by the url installer');
		$url = $I->getConfiguration('url');
		$I->installExtensionFromUrl($url . "/pkg-kunena-5.0.0.zip");
		$I->comment('Bug on install, use the kunena installer');
		$I->amOnPage('administrator/index.php?option=com_kunena');
		$I->wait(10);
		$I->waitForText('Kunena has been successfully installed!', 10, 'h2');
		$I->comment('Close the installer');
		$I->amOnPage('administrator/index.php?option=com_kunena');
		$I->wait(1);
		$I->doAdministratorLogout();
	}
}
