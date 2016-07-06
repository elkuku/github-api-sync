<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 05.07.16
 * Time: 13:44
 */

include '../vendor/autoload.php';

class Syncer
{
	public $basePath = '';

	public $docuPath = '';
	public $srcPath = '';

	public $ns = 'Joomla\\Github\\Package\\';

	public function __construct($srcPath = 'vendor/joomla/github/src/Package', $docuPath = 'developer.github.com/content/v3')
	{
		$this->basePath = realpath('../');

		$this->srcPath = $this->basePath . '/' . $srcPath;
		$this->docuPath = $this->basePath . '/' . $docuPath;
	}

	public function sync()
	{
		$classes = $this->readSrcClasses();
		$docuClasses = $this->readDocuClasses();

		foreach ($docuClasses as $docuClass)
		{
			if (false === array_key_exists($docuClass, $classes))
			{
				echo sprintf("** Class %s not found!\n", $docuClass);

				continue;
			}
			else
			{
				//echo sprintf("Class %s found!\n", $docuClass);
			}
		}

		echo "\n\nChecking docu\n\n";

		foreach ($classes as $class)
		{
			if (false === in_array($class->name, $docuClasses))
			{
				echo sprintf("** Class %s not found!\n", $class->name);

				continue;
			}
			else
			{
				//echo sprintf("Class %s found.\n", $class->name);
			}
		}

		sort($classes);

	}
	function readSrcClasses()
	{
		$classes = [];

		/* @type SplFileInfo $item */
		foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->srcPath)) as $item)
		{
			if ($item->isDir())
			{
				continue;
			}

			$class = new SyncClass;

			$class->name = str_replace('/', '\\', str_replace([$this->srcPath . '/', '.php'], '', $item->getPathname()));

			$rClass = new ReflectionClass($this->ns . $class->name);

			$class->comment = $rClass->getDocComment();

			foreach ($rClass->getMethods() as $method)
			{
				$a = $method->getDocComment();
			}

			$classes[$class->name] = $class;
		}

		return $classes;
	}

	function readDocuClasses()
	{
		$classes = [];

		/* @type SplFileInfo $item */
		foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->docuPath)) as $item)
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

			if (in_array($name, ['Troubleshooting', 'Changelog', 'Activity\Events\Types', 'Oauth', 'Versions', 'Media', 'Misc']))
			{
				continue;
			}

			$classes[] = $name;
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
}

class SyncMethod
{

}

/*
 * Main...
 */

(new Syncer)
	->sync();
