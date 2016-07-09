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
 * Class SourceCodeReader
 *
 * @since  1
 */
class SourceCodeReader extends AbstractReader
{
	/**
	 * Read src classes.
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

			$class = new SyncClass;

			$class->name = str_replace('/', '\\', str_replace([$this->basePath . '/', '.php'], '', $item->getPathname()));

			include $item->getPathname();

			if (false == class_exists($this->ns . $class->name))
			{
				throw new \RuntimeException('Class does not exists:' . $this->ns . $class->name);
			}

			$rClass = new \ReflectionClass($this->ns . $class->name);

			$class->comment = $rClass->getDocComment();

			foreach ($rClass->getMethods() as $method)
			{
				if (in_array($method->getName(), ['__construct', '__get', 'fetchUrl', 'processResponse']))
				{
					continue;
				}

				$m = new SyncMethod;

				$m->name = $method->getName();
				$m->docComment = $method->getDocComment();

				$lines = explode("\n", $m->docComment);

				$m->title = trim(str_replace('* ', '', $lines[1]));

				foreach ($lines as $line)
				{
					if (preg_match("/\t \* @param   ([a-z]+)[\s]+\\$([a-z]+)[\s]+([\s\S]+)/", $line, $matches))
					{
						$m->addParameter(new SyncParameter($matches[1], $matches[2], $matches[3]));
					}
				}

				$class->addMethod($m);
			}

			$classes[$class->name] = $class;
		}

		return $classes;
	}
}
