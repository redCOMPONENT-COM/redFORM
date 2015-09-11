<?php
require_once "phing/Task.php";

/**
 * Read extension folder from install xml in folder
 *
 */
class ExtractVersionTask extends Task
{
	/**
	 * @var String
	 */
	private $path;

	/**
	 * Set path
	 *
	 * @param   string  $path  base path to xml
	 *
	 * @return void
	 */
	public function setPath($path)
	{
		$this->path = $path[0] == '/' ? $path : dirname(__DIR__) . '/' . $path;
	}

	/**
	 * The main entry point method.
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function main()
	{
		if (!file_exists($this->path))
		{
			throw new Exception('Path doesnt exists');
		}

		$files = scandir($this->path);
		$version = false;

		foreach ($files as $file)
		{
			if (!preg_match('/\.xml$/', $file))
			{
				continue;
			}

			$manifest = file_get_contents($this->path . '/' . $file);
			$doc = new SimpleXMLElement($manifest);

			if (strtolower($doc->getName()) !== 'extension' )
			{
				continue;
			}

			$version = (string) $doc->version;
			break;
		}

		if (!$version)
		{
			throw new Exception('No valid install xml file found');
		}

		$this->project->setProperty('extensionversion', $version);
	}
}
