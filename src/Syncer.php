#!/usr/bin/env php
<?php
/**
 * Joomla! GitHub API syncer.
 *
 * @copyright  Copyright (C) 2016 Nikolai Plath - elkuku.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace Application;

use Joomla\Application\AbstractCliApplication;

include '../vendor/autoload.php';

/**
 * Class Syncer
 *
 * @since  1
 */
class Syncer extends AbstractCliApplication
{
	public $basePath = '';

	public $docuPath = '';

	public $srcPath = '';

	public $ns = 'Joomla\\Github\\Package\\';

	private $gitHubIgnoredClasses = [
		'Troubleshooting', 'Changelog', 'Activity\Events\Types', 'Oauth', 'Versions', 'Media', 'Misc'
	];

	private $quiet = false;

	/**
	 * Syncer constructor.
	 *
	 * @param   string  $srcPath   The src path.
	 * @param   string  $docuPath  The docu path.
	 */
	public function __construct($srcPath = 'vendor/joomla/github/src/Package', $docuPath = 'developer.github.com/content/v3')
	{
		$this->basePath = realpath('../');

		$this->srcPath = $this->basePath . '/' . $srcPath;
		$this->docuPath = $this->basePath . '/' . $docuPath;

		parent::__construct();
	}

	/**
	 * Method to run the application routines.  Most likely you will want to instantiate a controller
	 * and execute it, or perform some sort of task directly.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function doExecute()
	{
		$this->quiet = $this->input->get('quiet', $this->input->get('q'));

		$classes = $this->readSrcClasses();
		$docuClasses = $this->readDocuClasses();

		$this->out()
			->out('*************************')
			->out('*** GitHub API Syncer ***')
			->out('*************************')
			->out();

		$this->out('Checking docu => src')
			->out();

		foreach ($docuClasses as $docuClass)
		{
			if (false === array_key_exists($docuClass->name, $classes))
			{
				echo sprintf("** Class %s not found!\n", $docuClass->name);

				continue;
			}
			else
			{
				// @echo sprintf("Class %s found!\n", $docuClass);
			}
		}

		$this->out()
			->out('Checking src => docu')
			->out();

		foreach ($classes as $class)
		{
			if (false === array_key_exists($class->name, $docuClasses))
			{
				$this->out(sprintf('*** Class %s not found! ***', $class->name))
					->out('--------------------------------------');

				continue;
			}

			$this->out('Class: ' . $class->name);

			$nfs = [];

			foreach ($class->methods as $method)
			{
				foreach ($docuClasses[$class->name]->methods as $docuMethod)
				{
					if (trim($method->title, '.') == $docuMethod->title)
					{
						if (!$this->quiet)
						{
							$this->out(sprintf('Found: %s()', $method->name));
						}

						continue 2;
					}
				}

				$nfs[] = $method;
			}

			foreach ($nfs as $nf)
			{
				$this->out(sprintf("** Undocumented: %s()\n%s", $nf->name, $nf->title));
			}

			$this->out()
				->out('--- Docu');

			$nfs1 = [];

			foreach ($docuClasses[$class->name]->methods as $docuMethod)
			{
				foreach ($class->methods as $method)
				{
					if (trim($method->title, '.') == $docuMethod->title)
					{
						if (!$this->quiet)
						{
							$this->out(sprintf('Found: %s()', $method->name));
						}

						continue 2;
					}
				}

				$nfs1[] = $docuMethod;
			}

			foreach ($nfs1 as $nf)
			{
				$this->out('** Missing method for: ' . $nf->title);
			}

			$this->out();

			if (!$nfs && !$nfs1)
			{
				$this->out('OK');
			}

			$this->out('--------------------------------------');
		}
	}

	/**
	 * Read src classes.
	 *
	 * @return SyncClass[]
	 */
	private function readSrcClasses()
	{
		$classes = [];

		/* @type \SplFileInfo $item */
		foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->srcPath)) as $item)
		{
			if ($item->isDir())
			{
				continue;
			}

			$class = new SyncClass;

			$class->name = str_replace('/', '\\', str_replace([$this->srcPath . '/', '.php'], '', $item->getPathname()));

			include $item->getPathname();

			if (false == class_exists($this->ns . $class->name))
			{
				$u = $item->getPathname();
				include $item->getPathname();
			}
			else
			{
				//throw new \RuntimeException('class exists'.$this->ns . $class->name);
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

				$class->addMethod($m);
			}

			$classes[$class->name] = $class;
		}

		return $classes;
	}

	/**
	 * Read docu classes.
	 *
	 * @return SyncClass[]
	 */
	private function readDocuClasses()
	{
		$classes = [];

		/* @type \SplFileInfo $item */
		foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->docuPath)) as $item)
		{
			if ($item->isDir())
			{
				continue;
			}

			$name = str_replace('/', '\\', str_replace([$this->docuPath . '/', '.md'], '', $item->getPathname()));

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

			$lines = file($item->getPathname());

			foreach ($lines as $line)
			{
				if (0 === strpos($line, '## '))
				{
					$method = new SyncMethod;

					$method->title = trim(substr($line, 3));

					if (in_array($method->title, ['Custom media types']))
					{
						continue;
					}

					$method->title = str_replace('\'', '’', $method->title);

					$class->addMethod($method);
				}
			}

			$class->name = $name;

			$classes[$class->name] = $class;
		}

		return $classes;
	}
}

/**
 * Class SyncClass
 *
 * @since  1
 */
class SyncClass
{
	/**
	 * @var SyncMethod[]
	 */
	public $methods = [];

	public $name = '';

	public $comment = '';

	/**
	 * Add a method.
	 *
	 * @param   SyncMethod  $method  The method to add.
	 *
	 * @return $this
	 */
	public function addMethod(SyncMethod $method)
	{
		$this->methods[] = $method;

		return $this;
	}
}

/**
 * Class SyncMethod
 *
 * @since  1
 */
class SyncMethod
{
	public $name = '';

	public $title = '';

	public $docComment = '';
}

/*
 * Main...
 */

(new Syncer)
	->execute();
