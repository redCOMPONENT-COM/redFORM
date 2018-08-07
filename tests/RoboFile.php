<?php
/**
 * This is project's console commands configuration for Robo task runner.
 *
 * Download robo.phar from http://robo.li/robo.phar and type in the root of the repo: $ php robo.phar
 * Or do: $ composer update, and afterwards you will be able to execute robo like $ php vendor/bin/robo
 *
 * @see  http://robo.li/
 */
require_once 'vendor/autoload.php';

/**
 * Class RoboFile
 *
 * @since  1.6.14
 */
class RoboFile extends \Robo\Tasks
{
	// Load tasks from composer, see composer.json
	use Joomla\Testing\Robo\Tasks\loadTasks;

	/**
	 * Current root folder
	 */
	private $testsFolder = './';

	/**
	 * Sends Codeception errors to Slack
	 *
	 * @param   string  $slackChannel             The Slack Channel ID
	 * @param   string  $slackToken               Your Slack authentication token.
	 * @param   string  $codeceptionOutputFolder  Optional. By default tests/_output
	 *
	 * @return mixed
	 */
	public function sendCodeceptionOutputToSlack($slackChannel, $slackToken = null, $codeceptionOutputFolder = null)
	{
		if (is_null($slackToken))
		{
			$this->say('we are in Travis environment, getting token from ENV');

			// Remind to set the token in repo Travis settings,
			// see: http://docs.travis-ci.com/user/environment-variables/#Using-Settings
			$slackToken = getenv('SLACK_ENCRYPTED_TOKEN');
		}

		if (is_null($codeceptionOutputFolder))
		{
			$this->codeceptionOutputFolder = '_output';
		}

		$this->say($codeceptionOutputFolder);

		$result = $this
			->taskSendCodeceptionOutputToSlack(
				$slackChannel,
				$slackToken,
				$codeceptionOutputFolder
			)
			->run();

		return $result;
	}

	/**
	 * Downloads and prepares a Joomla CMS site for testing
	 *
	 * @return mixed
	 */
	public function prepareSiteForSystemTests()
	{
		// Get Joomla Clean Testing sites
		if (is_dir('joomla-cms3'))
		{
			$this->taskDeleteDir('joomla-cms3')->run();
		}

		$this->cloneJoomla();
	}

	/**
	 * Downloads and prepares a Joomla CMS site for testing
	 *
	 * @return mixed
	 */
	public function prepareSiteForUnitTests()
	{
		// Make sure we have joomla
		if (!is_dir('joomla-cms3'))
		{
			$this->cloneJoomla();
		}

		if (!is_dir('joomla-cms3/libraries/vendor/phpunit'))
		{
			$this->getComposer();
			$this->taskComposerInstall('../composer.phar')->dir('joomla-cms3')->run();
		}

		// Copy extension. No need to install, as we don't use mysql db for unit tests
		$joomlaPath = __DIR__ . '/joomla-cms3';
		$this->_exec("gulp copy --wwwDir=$joomlaPath --gulpfile ../build/gulpfile.js");
	}

	/**
	 * Executes Selenium System Tests in your machine
	 *
	 * @param   array  $options  Use -h to see available options
	 *
	 * @return mixed
	 */
	public function runTest($opts = [
		'test|t'	    => null,
		'suite|s'	    => 'acceptance'
	])
	{
		$this->getComposer();

		$this->taskComposerInstall()->run();

		$this->taskSeleniumStandaloneServer()

			->runSelenium()
			->waitForSelenium()

			->run()
			->stopOnFail();

		// Make sure to Run the Build Command to Generate AcceptanceTester
		$this->_exec("vendor/bin/codecept build");

		if (!$opts['test'])
		{
			$this->say('Available tests in the system:');

			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator(
						$this->testsFolder . $opts['suite'],
					RecursiveDirectoryIterator::SKIP_DOTS),
				RecursiveIteratorIterator::SELF_FIRST);

			$tests = array();

			$iterator->rewind();
			$i = 1;

			while ($iterator->valid())
			{
				if (strripos($iterator->getSubPathName(), 'cept.php')
					|| strripos($iterator->getSubPathName(), 'cest.php'))
				{
					$this->say('[' . $i . '] ' . $iterator->getSubPathName());
					$tests[$i] = $iterator->getSubPathName();
					$i++;
				}

				$iterator->next();
			}

			$this->say('');
			$testNumber	= $this->ask('Type the number of the test  in the list that you want to run...');
			$opts['test'] = $tests[$testNumber];
		}

		$pathToTestFile = './' . $opts['suite'] . '/' . $opts['test'];

		// loading the class to display the methods in the class
		require './' . $opts['suite'] . '/' . $opts['test'];

		$classes = Nette\Reflection\AnnotationsParser::parsePhp(file_get_contents($pathToTestFile));
		$className = array_keys($classes)[0];

		// If test is Cest, give the option to execute individual methods
		if (strripos($className, 'cest'))
		{
			$testFile = new Nette\Reflection\ClassType($className);
			$testMethods = $testFile->getMethods(ReflectionMethod::IS_PUBLIC);

			foreach ($testMethods as $key => $method)
			{
				$this->say('[' . $key . '] ' . $method->name);
			}

			$this->say('');
			$methodNumber = $this->askDefault('Choose the method in the test to run (hit ENTER for All)', 'All');

			if($methodNumber != 'All')
			{
				$method = $testMethods[$methodNumber]->name;
				$pathToTestFile = $pathToTestFile . ':' . $method;
			}
		}

		$this->taskCodecept()
			->test($pathToTestFile)
			->arg('--steps')
			->arg('--debug')
			->arg('--fail-fast')
			->run()
			->stopOnFail();
	}

	/**
	 * Preparation for running manual tests after installing Joomla/Extension and some basic configuration
	 *
	 * @return void
	 */
	public function runTestPreparation()
	{
		$this->prepareSiteForSystemTests();

		$this->getComposer();

		$this->taskComposerInstall()->run();

		$this->taskSeleniumStandaloneServer()

			->runSelenium()
			->waitForSelenium()

			->run()
			->stopOnFail();

		// Make sure to Run the Build Command to Generate AcceptanceTester
		$this->_exec("vendor/bin/codecept build");

		$this->taskCodecept()
			->arg('--steps')
			->arg('--debug')
			->arg('--tap')
			->arg('--fail-fast')
			->arg($this->testsFolder . 'acceptance/install/')
			->run()
			->stopOnFail();
	}

	/**
	 * Function to Run tests in a Group
	 *
	 * @return void
	 */
	public function runTests()
	{
		$this->prepareSiteForSystemTests();

		$this->prepareReleasePackages();

		$this->getComposer();

		$this->taskComposerInstall()->run();

		$this->taskSeleniumStandaloneServer()

			->runSelenium()
			->waitForSelenium()

			->run()
			->stopOnFail();

		// Make sure to Run the Build Command to Generate AcceptanceTester
		$this->_exec("vendor/bin/codecept build");

		$this->taskCodecept()
			->arg('--steps')
			->arg('--debug')
			->arg('--tap')
			->arg('--fail-fast')
			->arg($this->testsFolder . 'acceptance/install/')
			->run()
			->stopOnFail();

		$this->taskCodecept()
			->arg('--steps')
			->arg('--debug')
			->arg('--tap')
			->arg('--fail-fast')
			->arg($this->testsFolder . 'acceptance/administrator/')
			->run()
			->stopOnFail();

		$this->taskCodecept()
			->arg('--steps')
			->arg('--debug')
			->arg('--tap')
			->arg('--fail-fast')
			->arg($this->testsFolder . 'acceptance/uninstall/')
			->run()
			->stopOnFail();

		$this->killSelenium();
	}

	/**
	 * Function to run unit tests
	 *
	 * @return void
	 */
	public function runUnitTests()
	{
		$this->prepareSiteForUnitTests();
		$this->_exec("joomla-cms3/libraries/vendor/phpunit/phpunit/phpunit")
			->stopOnFail();
	}

	/**
	 * Stops Selenium Standalone Server
	 *
	 * @return void
	 */
	public function killSelenium()
	{
		$this->_exec('curl http://localhost:4444/selenium-server/driver/?cmd=shutDownSeleniumServer');
	}

	/**
	 * Downloads Composer
	 *
	 * @return void
	 */
	private function getComposer()
	{
		// Make sure we have Composer
		if (!file_exists('./composer.phar'))
		{
			$this->_exec('curl --retry 3 --retry-delay 5 -sS https://getcomposer.org/installer | php');
		}
	}

	/**
	 * Prepares the .zip packages of the extension to be installed in Joomla
	 */
	public function prepareReleasePackages()
	{
		$this->_exec("gulp release --skip-version --testRelease --gulpfile ../build/gulpfile.js");
	}

	/**
	 * Looks for PHP Parse errors in core
	 */
	public function checkForParseErrors()
	{
		$this->_exec('php checkers/phppec.php ../component/ ../plugins/');
	}

	/**
	 * Looks for missed debug code like var_dump or console.log
	 */
	public function checkForMissedDebugCode()
	{
		$this->_exec('php checkers/misseddebugcodechecker.php');
	}

	/**
	 * Check the code style of the project against a passed sniffers
	 */
	public function checkCodestyle()
	{
		if (!is_dir('checkers/phpcs/Joomla'))
		{
			$this->say('Downloading Joomla Coding Standards Sniffers');
			$this->_exec("git clone -b master --single-branch --depth 1 https://github.com/joomla/coding-standards.git checkers/phpcs/Joomla");
		}

		$this->taskExec('php checkers/phpcs.php')
				->printed(true)
				->run();
	}

	/**
	 * Sends a message to Github with the Error found in tests and a Image attached. Require Github and Cloudinary tokens
	 *
	 * @param $cloudName
	 * @param $apiKey
	 * @param $apiSecret
	 * @param $GithubToken
	 * @param $repoOwner
	 * @param $repo
	 * @param $pull
	 */
	public function sendScreenshotFromTravisToGithub($cloudName, $apiKey, $apiSecret, $GithubToken, $repoOwner, $repo, $pull = null)
	{
		$errorSelenium = true;
		$reportError = false;
		$reportFile = 'selenium.log';
		$body = 'Selenium log:' . chr(10). chr(10);

		$this->say("checking _output");

		// Loop throught Codeception snapshots
		if (file_exists(__DIR__ . '/_output') && $handler = opendir(__DIR__ . '/_output'))
		{
			$reportFile = __DIR__ . '/_output/report.tap.log';
			$body = 'Codeception tap log:' . chr(10). chr(10);
			$errorSelenium = false;
			$this->say("reportFile: $reportFile");
		}

		if (file_exists($reportFile))
		{
			if ($reportFile)
			{
				$body .= file_get_contents($reportFile, null, null, 15);
			}

			if (!$errorSelenium)
			{
				$handler = opendir(__DIR__ . '/_output');

				while (false !== ($errorSnapshot = readdir($handler)))
				{
					$this->say("errorSnapshot: $errorSnapshot");
					// Avoid sending system files or html files
					if (!('png' === pathinfo($errorSnapshot, PATHINFO_EXTENSION)))
					{
						continue;
					}

					$reportError = true;
					$this->say("Uploading screenshots: $errorSnapshot");

					Cloudinary::config(
						array(
							'cloud_name' => $cloudName,
							'api_key'    => $apiKey,
							'api_secret' => $apiSecret
						)
					);

					$result = \Cloudinary\Uploader::upload(realpath(__DIR__ . '/_output/' . $errorSnapshot));
					$this->say($errorSnapshot . 'Image sent');
					$body .= '![Screenshot](' . $result['secure_url'] . ')';
				}
			}

			// If it's a Selenium error log, it prints it in the regular output
			if ($errorSelenium)
			{
				$this->say($body);
			}

			if (!$reportError)
			{
				$this->say("nothing to report");

				return;
			}

			if (is_numeric($pull))
			{
				// Creates the error log in a Github comment
				$this->say('Creating Github issue');
				$client = new \Github\Client;
				$client->authenticate($GithubToken, \Github\Client::AUTH_HTTP_TOKEN);
				$client
					->api('issue')
					->comments()->create(
						$repoOwner, $repo, $pull,
						array(
							'body' => $body
						)
					);
			}
			else
			{
				// Not a pull request, so just output in console
				$this->say($body);
			}
		}
		else
		{
			$this->say("reportFile not found");
		}
	}

	/**
	 * Clone joomla from official repo
	 *
	 * @return void
	 */
	private function cloneJoomla()
	{
		$version = 'staging';

		/*
		 * When joomla Staging branch has a bug you can uncomment the following line as a tmp fix for the tests layer.
		 * Use as $version value the latest tagged stable version at: https://github.com/joomla/joomla-cms/releases
		 */
		$version = '3.7.2';

		$this->_exec("git clone -b $version --single-branch --depth 1 https://github.com/joomla/joomla-cms.git joomla-cms3");

		$this->say("Joomla CMS ($version) site created at joomla-cms3/");
	}
	
	/**
	 * @param int $use_htaccess
	 */
	public function prepareSiteForSystemTesting($use_htaccess = 1)
	{
		// Get Joomla Clean Testing sites
		if (is_dir('joomla-cms'))
		{
			$this->taskDeleteDir('joomla-cms')->run();
		}
		
		$version = 'staging';
		
		/*
		 * When joomla Staging branch has a bug you can uncomment the following line as a tmp fix for the tests layer.
		 * Use as $version value the latest tagged stable version at: https://github.com/joomla/joomla-cms/releases
		 */
		$version = '3.8.11';
		
		$this->_exec("git clone -b $version --single-branch --depth 1 https://github.com/joomla/joomla-cms.git joomla-cms");
		
		$this->say("Joomla CMS ($version) site created at tests/joomla-cms");
		
		// Optionally uses Joomla default htaccess file
		if ($use_htaccess == 1)
		{
			$this->_copy('joomla-cms/htaccess.txt', 'joomla-cms/.htaccess');
			$this->_exec('sed -e "s,# RewriteBase /,RewriteBase /tests/joomla-cms/,g" --in-place joomla-cms/.htaccess');
		}
	}
	
	/**
	 * @param $githubToken
	 * @param $repoOwner
	 * @param $repo
	 * @param $pull
	 */
	public function uploadPatchFromJenkinsToTestServer($githubToken, $repoOwner, $repo, $pull)
	{
		$body = 'Please Download the Patch Package for testing from the following Path: http://test.redcomponent.com/vanir/PR/' . $pull . '/redform.zip';
		
		$this->say('Creating Github Comment');
		$client = new \Github\Client;
		$client->authenticate($githubToken, \Github\Client::AUTH_HTTP_TOKEN);
		$client
			->api('issue')
			->comments()->create(
				$repoOwner, $repo, $pull,
				array(
					'body' => $body
				)
			);
	}
	
	/**
	 *
	 */
	public function runTestSetupJenkins()
	{
		$this->taskSeleniumStandaloneServer()
			->setURL("http://localhost:4444")
			->runSelenium()
			->waitForSelenium()
			->run()
			->stopOnFail();
		
		$this->_exec("vendor/bin/codecept build");
		
		$this->taskCodecept()
			->arg('--tap')
			->arg('--fail-fast')
			->arg('acceptance/install/')
			->run()
			->stopOnFail();
	}
	
	public function runJenkins($folder)
	{
		$this->taskSeleniumStandaloneServer()
			->setURL("http://localhost:4444")
			->runSelenium()
			->waitForSelenium()
			->run()
			->stopOnFail();
		$this->_exec("vendor/bin/codecept build");
		
		$this->taskCodecept()
			->arg('--tap')
			->arg('--fail-fast')
			->arg($folder . '/')
			->run()
			->stopOnFail();
	}
	
	/**
	 * Sends the build report error back to Slack
	 *
	 * @param   string $cloudinaryName      Cloudinary cloud name
	 * @param   string $cloudinaryApiKey    Cloudinary API key
	 * @param   string $cloudinaryApiSecret Cloudinary API secret
	 * @param   string $githubRepository    GitHub repository (owner/repo)
	 * @param   string $githubPRNo          GitHub PR #
	 * @param   string $slackWebhook        Slack Webhook URL
	 * @param   string $slackChannel        Slack channel
	 * @param   string $buildURL            Build URL
	 *
	 * @return  void
	 *
	 * @since   5.1
	 */
	public function sendBuildReportErrorSlack($cloudinaryName, $cloudinaryApiKey, $cloudinaryApiSecret, $githubRepository, $githubPRNo, $slackWebhook, $slackChannel, $buildURL)
	{
		$errorSelenium = true;
		$reportError   = false;
		$reportFile    = 'tests/selenium.log';
		$errorLog      = 'Selenium log:' . chr(10) . chr(10);
		
		// Loop through Codeception snapshots
		if (file_exists('tests/_output') && $handler = opendir('tests/_output'))
		{
			$reportFile    = 'tests/_output/report.tap.log';
			$errorLog      = 'Codeception tap log:' . chr(10) . chr(10);
			$errorSelenium = false;
		}
		
		if (file_exists($reportFile))
		{
			if ($reportFile)
			{
				$errorLog .= file_get_contents($reportFile, null, null, 15);
			}
			
			if (!$errorSelenium)
			{
				$handler    = opendir('tests/_output');
				$errorImage = '';
				
				while (!$reportError && false !== ($errorSnapshot = readdir($handler)))
				{
					// Avoid sending system files or html files
					if (!('png' === pathinfo($errorSnapshot, PATHINFO_EXTENSION)))
					{
						continue;
					}
					
					$reportError = true;
					$errorImage  = __DIR__ . '/tests/_output/' . $errorSnapshot;
				}
			}
			
			echo $errorImage;
			
			if ($reportError || $errorSelenium)
			{
				// Sends the error report to Slack
				$reportingTask = $this->taskReporting()
					->setCloudinaryCloudName($cloudinaryName)
					->setCloudinaryApiKey($cloudinaryApiKey)
					->setCloudinaryApiSecret($cloudinaryApiSecret)
					->setGithubRepo($githubRepository)
					->setGithubPR($githubPRNo)
					->setBuildURL($buildURL . 'display/redirect')
					->setSlackWebhook($slackWebhook)
					->setSlackChannel($slackChannel)
					->setTapLog($errorLog);
				
				if (!empty($errorImage))
				{
					$reportingTask->setImagesToUpload($errorImage)
						->publishCloudinaryImages();
				}
				
				$reportingTask->publishBuildReportToSlack()
					->run()
					->stopOnFail();
			}
		}
	}
	
}
