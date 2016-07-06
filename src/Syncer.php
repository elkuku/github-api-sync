<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 05.07.16
 * Time: 13:44
 */

namespace Application;

use Joomla\Application\AbstractCliApplication;

include '../vendor/autoload.php';

class Syncer extends AbstractCliApplication
{
	public $basePath = '';

	public $docuPath = '';
	public $srcPath = '';

	public $ns = 'Joomla\\Github\\Package\\';

	private $gitHubIgnoredClasses = [
		'Troubleshooting', 'Changelog', 'Activity\Events\Types', 'Oauth', 'Versions', 'Media', 'Misc'
	];

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
				//echo sprintf("Class %s found!\n", $docuClass);
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
			else
			{
				$this->out('Class: ' . $class->name)
					->out();

				$nfs = [];

				foreach ($class->methods as $method)
				{
					foreach ($docuClasses[$class->name]->methods as $docuMethod)
					{
						if (trim($method->title, '.') == $docuMethod->title)
						{
							$this->out(sprintf('Found: %s()', $method->name));

							continue 2;
						}
					}

					$nfs[] = $method;
				}

				foreach ($nfs as $nf)
				{
					$this->out(sprintf("** Not Found: %s()\n%s", $nf->name, $nf->title));
				}

				$this->out()
					->out('--- Recheck docu');

				$nfs = [];

				foreach ($docuClasses[$class->name]->methods as $docuMethod)
				{
					foreach ($class->methods as $method)
					{
						if (trim($method->title, '.') == $docuMethod->title)
						{
							$this->out(sprintf('Found: %s()', $method->name));

							continue 2;
						}
					}

					$nfs[] = $docuMethod;
				}

				foreach ($nfs as $nf)
				{
					$this->out(sprintf("** Not Found: %s()\n%s", $nf->name, $nf->title));
				}

				$this->out()
					->out('--------------------------------------');
			}
		}
	}

	/**
	 * @return SyncClass[]
	 */
	function readSrcClasses()
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
	 * @return SyncClass[]
	 */
	function readDocuClasses()
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


class SyncClass
{
	/**
	 * @var SyncMethod[]
	 */
	public $methods = [];

	public $name = '';

	public $comment = '';

	public function addMethod(SyncMethod $method)
	{
		$this->methods[] = $method;

		return $this;
	}
}

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
