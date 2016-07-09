<?php
/**
 * Joomla! GitHub API syncer.
 *
 * @copyright  Copyright (C) 2016 Nikolai Plath - elkuku.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace ElKuKu\Syncer\Reader;

use ElKuKu\Syncer\Type\SyncClass;
use ElKuKu\Syncer\Type\SyncMethod;
use ElKuKu\Syncer\Type\SyncParameter;

/**
 * Class DocumentationReader
 *
 * @since  1
 */
class DocumentationReader extends AbstractReader
{
	private $gitHubIgnoredClasses = [
		'Troubleshooting', 'Changelog', 'Activity\Events\Types', 'Oauth', 'Versions', 'Media', 'Misc'
	];

	/**
	 * Read docu classes.
	 *
	 * @return SyncClass[]
	 */
	public function read()
	{
		$classes = [];

		/* @type \SplFileInfo $item */
		foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->basePath)) as $item)
		{
			if ($item->isDir())
			{
				continue;
			}

			$name = str_replace('/', '\\', str_replace([$this->basePath . '/', '.md'], '', $item->getPathname()));

			$name = implode('\\', array_map('ucfirst', explode('\\', $name)));

			$name = str_replace('Repos', 'Repositories', $name);
			$name = str_replace('Auth', 'Authorization', $name);
			$name = str_replace('Git\\', 'Data\\', $name);

			if ('Git' == $name)
			{
				$name = 'Data';
			}

			if (in_array($name, $this->gitHubIgnoredClasses))
			{
				continue;
			}

			$class = new SyncClass;

			$class->name = $name;

			$lines = file($item->getPathname());

			$parseMode = 0;
			$method = null;

			foreach ($lines as $i => $line)
			{
				$test = trim($line);

				if (!$test)
				{
					continue;
				}

				if (0 === strpos($test, '## '))
				{
					if ($method)
					{
						$class->addMethod($method);
					}

					$method = new SyncMethod;

					$method->title = trim(substr($test, 3));

					if (in_array($method->title, ['Custom media types']))
					{
						continue;
					}

					$method->title = str_replace('\'', '’', $method->title);

					$parseMode = 0;
				}
				elseif (
					0 === strpos($test, '### Parameters') ||
					0 === strpos($test, '### Input') ||
					1 === $parseMode
				)
				{
					switch ($parseMode)
					{
						case 0:
							$parseMode = 1;
							break;
						case 1:
							if (0 === strpos($test, '`') && trim($test, '`'))
							{
								// Found a parameter.
								$parts = explode('|', trim($test, '|'));

								if (3 != count($parts))
								{
									if (in_array($class->name, ['Repositories\\Deployments']))
									{
										// Those are strange exceptions...

										continue;
									}

									echo $class->name . "\n";
									echo $i . "\n";
									echo $line . "\n";

									//throw new \RuntimeException('Could not parse: ' . $test);
								}

								if (!$method)
								{
									throw new \UnexpectedValueException('Not in any method...');
								}

								$method->addParameter(
									new SyncParameter(
										trim($parts[1], '` '),
										trim($parts[0], '`'),
										trim($parts[2])
									)
								);
							}
							break;
						default:
							throw new \RuntimeException('Bad stuff');
					}
				}
				elseif (
					0 === strpos($test, '### Response') ||
					2 === $parseMode
				)
				{
					switch ($parseMode)
					{
						case 0:
							$parseMode = 2;
							break;
						case 1:
							$parseMode = 2;
							break;
						case 2:
							if (0 === strpos($test, '<%= headers'))
							{
							}
							break;
						default:
							throw new \RuntimeException('Bad stuff');
					}
				}
				else
				{
					$parseMode = 0;
				}
			}

			if ($method)
			{
				$class->addMethod($method);
			}

			$classes[$class->name] = $class;
		}

		return $classes;
	}
}
