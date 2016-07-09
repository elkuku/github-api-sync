<?php
/**
 * Joomla! GitHub API syncer.
 *
 * @copyright  Copyright (C) 2016 Nikolai Plath - elkuku.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

namespace ElKuKu\Syncer\Type;

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

	/**
	 * @var SyncResponse
	 */
	public $response = null;

	/**
	 * @var SyncParameter[]
	 */
	public $parameters = [];

	/**
	 * Add a parameter.
	 *
	 * @param   SyncParameter  $parameter  The Parameter.
	 *
	 * @return $this
	 */
	public function addParameter(SyncParameter $parameter)
	{
		$this->parameters[$parameter->name] = $parameter;

		return $this;
	}

	/**
	 * Check if the method has a specific parameter.
	 *
	 * @param   string  $name  The parameter name.
	 *
	 * @return boolean
	 */
	public function hasParameter($name)
	{
		return array_key_exists($name, $this->parameters);
	}
}
