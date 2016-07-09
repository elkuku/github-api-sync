<?php
/**
 * Joomla! GitHub API syncer.
 *
 * @copyright  Copyright (C) 2016 Nikolai Plath - elkuku.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace ElKuKu\Syncer;

use ElKuKu\Syncer\Reader\DocumentationReader;
use ElKuKu\Syncer\Reader\SourceCodeReader;

use Joomla\Application\AbstractCliApplication;

/**
 * Class Application
 *
 * @since  1
 */
class Application extends AbstractCliApplication
{
	public $docuPath = '';

	public $srcPath = '';

	private $quiet = false;

	/**
	 * Syncer constructor.
	 *
	 * @param   string  $srcPath   The src path.
	 * @param   string  $docuPath  The docu path.
	 */
	public function __construct($srcPath = '', $docuPath = '')
	{
		$this->srcPath = $srcPath ? : realpath('../' . 'vendor/joomla/github/src/Package');
		$this->docuPath = $docuPath ? : realpath('../' . 'developer.github.com/content/v3');

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

		$classes = (new SourceCodeReader($this->srcPath))->read();
		$docuClasses = (new DocumentationReader($this->docuPath))->read();

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

						// Check parameters
						foreach ($docuMethod->parameters as $parameter)
						{
							if (false === $method->hasParameter($parameter->name))
							{
								$this->out('Parameter not found: ' . $parameter);
							}
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
}
