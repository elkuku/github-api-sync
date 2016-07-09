<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 09.07.16
 * Time: 13:42
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
