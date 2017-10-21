<?php
/**
 * @package     Joomla.Site
 * @subpackage  RoboFile
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * This is joomla project's console command file for Robo.li task runner.
 *
 * Or do: $ composer install, and afterwards you will be able to execute robo like
 * $ ./libraries/vendor/bin/robo
 *
 * @see         http://robo.li/
 */
require_once __DIR__ . '/vendor/autoload.php';

if (!defined('JPATH_BASE'))
{
	define('JPATH_BASE', __DIR__);
}

/**
 * System Test (Codeception) test execution for Joomla!
 *
 * @package  RoboFile
 *
 * @since    Kunena 5.1
 */
class RoboFile extends \Robo\Tasks
{
	// Load tasks from composer, see composer.json
	use \Joomla\Jorobo\Tasks\loadTasks;
	use \Joomla\Testing\Robo\Tasks\LoadTasks;

	/**
	 * File extension for executables
	 *
	 * @var string
	 * @since  Kunena 5.1
	 */
	private $executableExtension = '';

	/**
	 * Path to the codeception tests folder
	 *
	 * @var   string
	 * @since  Kunena 5.1
	 */
	private $testsPath = 'tests/';

	/**
	 * Local configuration parameters
	 *
	 * @var    array
	 * @since  Kunena 5.1
	 */
	private $configuration = array();

	/**
	 * @var array | null
	 * @since  Kunena 5.1
	 */
	private $suiteConfig;

	/**
	 * Path to the local CMS test folder
	 *
	 * @var    string
	 * @since  Kunena 5.1
	 */
	protected $cmsPath = null;

	/**
	 * RoboFile constructor.
	 *
	 * @since   Kunena 5.1
	 */
	public function __construct()
	{
		$this->configuration       = $this->getConfiguration();
		$this->cmsPath             = $this->getTestingPath();
		$this->executableExtension = $this->getExecutableExtension();

		// Set default timezone (so no warnings are generated if it is not set)
		date_default_timezone_set('UTC');
	}

	/**
	 * Get the executable extension according to Operating System
	 *
	 * @return
	 */
	private function getExecutableExtension()
	{
		if ($this->isWindows())
		{
			return '.exe';
		}

		return '';
	}

	/**
	 * Get (optional) configuration from an external file
	 *
	 * @since   Kunena 5.1
	 *
	 * @return  \stdClass|null
	 */
	public function getConfiguration()
	{
		$configurationFile = __DIR__ . '/RoboFile.ini';

		if (!file_exists($configurationFile))
		{
			$this->say('No local configuration file');

			return null;
		}

		$configuration = parse_ini_file($configurationFile);

		if ($configuration === false)
		{
			$this->say('Local configuration file is empty or wrong (check is it in correct .ini format');

			return null;
		}

		return json_decode(json_encode($configuration));
	}

	/**
	 * Get the correct CMS root path
	 *
	 * @since   Kunena 5.1
	 *
	 * @return  string
	 */
	private function getTestingPath()
	{
		if (empty($this->configuration->cmsPath))
		{
			return $this->testsPath . 'joomla';
		}

		if (!file_exists(dirname($this->configuration->cmsPath)))
		{
			$this->say('CMS path written in local configuration does not exists or is not readable');

			return $this->testsPath . 'joomla';
		}

		return $this->configuration->cmsPath;
	}

	/**
	 * Creates a testing Joomla site for running the tests (use it before run:test)
	 *
	 * @param   bool $useHtaccess (1/0) Rename and enable embedded Joomla .htaccess file
	 *
	 * @since   Kunena 5.1
	 *
	 * @return  void
	 */
	public function createTestingSite($useHtaccess = false)
	{
		if (!empty($this->configuration->skipClone))
		{
			$this->say('Reusing Joomla CMS site already present at ' . $this->cmsPath);

			return;
		}

		// Caching cloned installations locally
		if (!is_dir($this->testsPath . 'cache') || (time() - filemtime($this->testsPath . 'cache') > 60 * 60 * 24))
		{
			if (file_exists($this->testsPath . 'cache'))
			{
				$this->taskDeleteDir($this->testsPath . 'cache')->run();
			}

			$this->_exec($this->buildGitCloneCommand());
		}

		// Get Joomla Clean Testing sites
		if (is_dir($this->cmsPath))
		{
			try
			{
				$this->taskDeleteDir($this->cmsPath)->run();
			}
			catch (Exception $e)
			{
				// Sorry, we tried :(
				$this->say('Sorry, you will have to delete ' . $this->cmsPath . ' manually. ');
				exit(1);
			}
		}

		$exclude = ['tests', 'tests-phpunit', '.run', '.github', '.git', '.drone', 'docs', 'src', 'cache'];

		$this->copyJoomla($this->cmsPath, $exclude);

		// Optionally change owner to fix permissions issues
		if (!empty($this->configuration->localUser))
		{
			$this->_exec('chown -R ' . $this->configuration->localUser . ' ' . $this->cmsPath);
		}

		$this->_copy('dist/pkg_kunena_v5.1-dev.zip', "tests/_data/pkg_kunena_v5.1-dev.zip");

		// Optionally uses Joomla default htaccess file. Used by TravisCI
		if ($useHtaccess == true)
		{
			$this->say('Renaming htaccess.txt to .htaccess');
			$this->_copy($this->cmsPath . '/htaccess.txt', $this->cmsPath . '/.htaccess');
			$this->_exec('sed -e "s,# RewriteBase /,RewriteBase /tests/joomla-cms,g" -in-place tests/joomla/.htaccess');
		}
	}

	/**
	 * Copy the Joomla installation excluding folders
	 *
	 * @param   string  $dst      Target folder
	 * @param   array   $exclude  Exclude list of folders
	 *
	 * @throws  Exception
	 *
	 * @since   Kunena 5.1
	 *
	 * @return  void
	 */
	protected function copyJoomla($dst, $exclude = array())
	{
		$dir = @opendir( "." . '/' . $this->testsPath . 'cache');

		if (false === $dir)
		{
			throw new Exception($this, "Cannot open source directory");
		}

		if (!is_dir($dst))
		{
			mkdir($dst, 0755, true);
		}

		while (false !== ($file = readdir($dir)))
		{
			if (in_array($file, $exclude))
			{
				continue;
			}

			if (($file !== '.') && ($file !== '..'))
			{
				$srcFile  = "." . '/' . $this->testsPath . 'cache/' . $file;
				$destFile = $dst . '/' . $file;

				if (is_dir($srcFile))
				{
					$this->_copyDir($srcFile, $destFile);
				}
				else
				{
					copy($srcFile, $destFile);
				}
			}
		}

		closedir($dir);
	}

	/**
	 * Downloads Composer
	 *
	 * @since   Kunena 5.1
	 *
	 * @return  void
	 */
	private function getComposer()
	{
		// Make sure we have Composer
		if (!file_exists('composer.phar'))
		{
			$this->_exec('curl -o ' . 'composer.phar  --retry 3 --retry-delay 5 -sS https://getcomposer.org/installer | php');
		}
	}

	/**
	 * Runs Selenium Standalone Server.
	 *
	 * @since   Kunena 5.1
	 *
	 * @return  void
	 */
	public function runSelenium()
	{
		$this->taskSeleniumStandaloneServer()
			->setBinary('vendor/bin/selenium-server-standalone')
			->setWebdriver($this->getWebDriver())
			->runSelenium()
			->waitForSelenium()
			->run()
			->stopOnFail();
	}

	/**
	 * Executes all the Selenium System Tests in a suite on your machine
	 *
	 * @param   array $opts   Array of configuration options:
	 *                        - 'use-htaccess': renames and enable embedded Joomla .htaccess file
	 *                        - 'env': set a specific environment to get configuration from
	 *
	 * @since   Kunena 5.1
	 *
	 * @return  mixed
	 */
	public function runTests($opts = ['use-htaccess' => false, 'env' => 'desktop'])
	{
		$this->say("Running tests");

		$this->createTestingSite($opts['use-htaccess']);

		$this->getComposer();

		$this->taskComposerInstall('composer')->run();

		$this->runSelenium();

		// Make sure to run the build command to generate AcceptanceTester
		if ($this->isWindows())
		{
			$this->_exec('php ' . $this->getWindowsPath('vendor/bin/codecept') . ' build');
			$pathToCodeception = $this->getWindowsPath('vendor/bin/codecept');
		}
		else
		{
			$this->_exec('php ' . 'vendor/bin/codecept build');

			$pathToCodeception = 'vendor/bin/codecept';
		}

		$this->taskCodecept($pathToCodeception)
			->arg('--steps')
			->arg('--debug')
			->arg('--fail-fast')
			->env($opts['env'])
			->arg($this->testsPath . 'acceptance/install/')
			->run()
			->stopOnFail();

		$this->taskCodecept($pathToCodeception)
			->arg('--steps')
			->arg('--debug')
			->arg('--fail-fast')
			->env($opts['env'])
			->arg($this->testsPath . 'acceptance/administrator/')
			->run()
			->stopOnFail();

		$this->taskCodecept($pathToCodeception)
			->arg('--steps')
			->arg('--debug')
			->arg('--fail-fast')
			->env($opts['env'])
			->arg($this->testsPath . 'acceptance/frontend/')
			->run()
			->stopOnFail();

		$this->taskCodecept($pathToCodeception)
			->arg('--steps')
			->arg('--debug')
			->arg('--fail-fast')
			->env($opts['env'])
			->arg($this->testsPath . 'acceptance/libraries/')
			->run()
			->stopOnFail();

		$this->taskCodecept($pathToCodeception)
			->arg('--steps')
			->arg('--debug')
			->arg('--fail-fast')
			->env($opts['env'])
			->arg($this->testsPath . 'acceptance/plugins/')
			->run()
			->stopOnFail();
	}

	/**
	 * Executes a specific Selenium System Tests in your machine
	 *
	 * @param   string  $pathToTestFile  Optional name of the test to be run
	 * @param   string  $suite           Optional name of the suite containing the tests, Acceptance by default.
	 *
	 * @since   Kunena 5.1
	 *
	 * @return  void
	 */
	public function runTest($pathToTestFile = null, $suite = 'acceptance')
	{
		$this->runSelenium();

		// Make sure to run the build command to generate AcceptanceTester
		$path = 'vendor/bin/codecept';
		$this->_exec('php ' . $this->isWindows() ? $this->getWindowsPath($path) : $path . ' build');

		if (!$pathToTestFile)
		{
			$this->say('Available tests in the system:');

			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator(
					$this->testsPath . $suite,
					RecursiveDirectoryIterator::SKIP_DOTS
				),
				RecursiveIteratorIterator::SELF_FIRST
			);

			$tests = array();
			$i     = 1;

			$iterator->rewind();

			while ($iterator->valid())
			{
				if (strripos($iterator->getSubPathName(), 'cept.php')
					|| strripos($iterator->getSubPathName(), 'cest.php')
					|| strripos($iterator->getSubPathName(), '.feature')
				)
				{
					$this->say('[' . $i . '] ' . $iterator->getSubPathName());

					$tests[$i] = $iterator->getSubPathName();
					$i++;
				}

				$iterator->next();
			}

			$this->say('');
			$testNumber = $this->ask('Type the number of the test in the list that you want to run...');
			$test       = $tests[$testNumber];
		}

		$pathToTestFile = $this->testsPath . $suite . '/' . $test;

		// Loading the class to display the methods in the class

		// Logic to fetch the class name from the file name
		$fileName = explode("/", $test);

		// If the selected file is cest only then we will give the option to execute individual methods, we don't need this in cept or feature files
		$i = 1;

		if (isset($fileName[1]) && strripos($fileName[1], 'cest'))
		{
			require $this->testsPath . $suite . '/' . $test;

			$className     = explode(".", $fileName[1]);
			$class_methods = get_class_methods($className[0]);

			$this->say('[' . $i . '] ' . 'All');

			$methods[$i] = 'All';
			$i++;

			foreach ($class_methods as $method_name)
			{
				$reflect = new ReflectionMethod($className[0], $method_name);

				if (!$reflect->isConstructor() && $reflect->isPublic())
				{
					$this->say('[' . $i . '] ' . $method_name);

					$methods[$i] = $method_name;

					$i++;
				}
			}

			$this->say('');
			$methodNumber = $this->ask('Please choose the method in the test that you would want to run...');
			$method       = $methods[$methodNumber];
		}

		if (isset($method) && $method != 'All')
		{
			$pathToTestFile = $pathToTestFile . ':' . $method;
		}

		$testPathCodecept = 'vendor/bin/codecept';

		$this->taskCodecept($this->isWindows() ? $this->getWindowsPath($testPathCodecept) : $testPathCodecept)
			->test($pathToTestFile)
			->arg('--steps')
			->arg('--debug')
			->run()
			->stopOnFail();
	}

	/**
	 * Check if local OS is Windows
	 *
	 * @return  bool
	 *
	 * @since   Kunena 5.1
	 */
	private function isWindows()
	{
		return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
	}

	/**
	 * Return the correct path for Windows (needed by CMD)
	 *
	 * @param   string  $path  Linux path
	 *
	 * @return  string
	 *
	 * @since   Kunena 5.1
	 */
	private function getWindowsPath($path)
	{
		return str_replace('/', DIRECTORY_SEPARATOR, $path);
	}

	/**
	 * Detect the correct driver for selenium
	 *
	 * @return  string the webdriver string to use with selenium
	 *
	 * @since   Kunena 5.1
	 */
	public function getWebdriver()
	{
		$suiteConfig        = $this->getSuiteConfig();
		$codeceptMainConfig = \Codeception\Configuration::config();
		$browser            = $suiteConfig['modules']['config']['JoomlaBrowser']['browser'];

		if ($browser == 'chrome')
		{
			$driver['type'] = 'webdriver.chrome.driver';
		}
		elseif ($browser == 'firefox')
		{
			$driver['type'] = 'webdriver.gecko.driver';
		}
		elseif ($browser == 'MicrosoftEdge')
		{
			$driver['type'] = 'webdriver.edge.driver';

			// Check if we are using Windows Insider builds
			if ($suiteConfig['modules']['config']['AcceptanceHelper']['MicrosoftEdgeInsiders'])
			{
				$browser = 'MicrosoftEdgeInsiders';
			}
		}
		elseif ($browser == 'internet explorer')
		{
			$driver['type'] = 'webdriver.ie.driver';
		}

		// Check if we have a path for this browser and OS in the codeception settings
		if (isset($codeceptMainConfig['webdrivers'][$browser][$this->getOs()]))
		{
			$driverPath = $codeceptMainConfig['webdrivers'][$browser][$this->getOs()];
		}
		else
		{
			$this->yell('No driver for your browser. Check your browser in acceptance.suite.yml and the webDrivers in codeception.yml');

			// We can't do anything without a driver, exit
			exit(1);
		}

		$driver['path'] = $driverPath;

		return '-D' . implode('=', $driver);
	}

	/**
	 * Return the os name
	 *
	 * @return string
	 *
	 * @since   Kunena 5.1
	 */
	private function getOs()
	{
		$os = php_uname('s');

		if (strpos(strtolower($os), 'windows') !== false)
		{
			return  'windows';
		}

		if (strpos(strtolower($os), 'darwin') !== false)
		{
			return 'mac';
		}

		return 'linux';
	}

	/**
	 * Get the suite configuration
	 *
	 * @param   string  $suite  Name of the test suite
	 *
	 * @return  array
	 *
	 * @since   Kunena 5.1
	 */
	private function getSuiteConfig($suite = 'acceptance')
	{
		if (!$this->suiteConfig)
		{
			$this->suiteConfig = Symfony\Component\Yaml\Yaml::parse(file_get_contents("tests/{$suite}.suite.yml"));
		}

		return $this->suiteConfig;
	}

	/**
	 * Build correct git clone command according to local configuration and OS
	 *
	 * @return string
	 */
	private function buildGitCloneCommand()
	{
		$branch = empty($this->configuration->branch) ? 'staging' : $this->configuration->branch;

		return "git clone -b $branch --single-branch --depth 1 https://github.com/joomla/joomla-cms.git " . $this->testsPath . "cache";
	}

	/**
	 * Update Version __DEPLOY_VERSION__ in Weblinks. (Set the version up in the jorobo.ini)
	 *
	 * @return  void
	 */
	public function bump()
	{
		(new \Joomla\Jorobo\Tasks\BumpVersion())->run();
	}

	/**
	 * Build the joomla extension package
	 *
	 * @param   array  $params  Additional params
	 *
	 * @return  void
	 */
	public function build($params = ['dev' => true])
	{
		(new \Joomla\Jorobo\Tasks\Build($params))->run();
	}
}
