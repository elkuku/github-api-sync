<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 09.07.16
 * Time: 13:42
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
