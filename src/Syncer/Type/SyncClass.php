<?php
/**
 * Joomla! GitHub API syncer.
 *
 * @copyright  Copyright (C) 2016 Nikolai Plath - elkuku.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace ElKuKu\Syncer\Type;

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
