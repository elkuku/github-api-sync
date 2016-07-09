<?php
/**
 * Joomla! GitHub API syncer.
 *
 * @copyright  Copyright (C) 2016 Nikolai Plath - elkuku.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace ElKuKu\Syncer\Reader;

use ElKuKu\Syncer\Type\SyncClass;

/**
 * Class AbstractReader
 *
 * @since  1
 */
abstract class AbstractReader
{
	protected $basePath = '';

	public $ns = 'Joomla\\Github\\Package\\';

	/**
	 * AbstractReader constructor.
	 *
	 * @param   string  $basePath  The base path.
	 */
	public function __construct($basePath)
	{
		$this->basePath = $basePath;
	}

	/**
	 * Perform the read action.
	 *
	 * @return SyncClass[]
	 */
	abstract public function read();
}