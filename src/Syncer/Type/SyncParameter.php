<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 09.07.16
 * Time: 13:41
 */

namespace ElKuKu\Syncer\Type;

/**
 * Class SyncParameter
 *
 * @since  1
 */
class SyncParameter
{
	public $type = '';

	public $name = '';

	public $description = '';

	/**
	 * SyncParameter constructor.
	 *
	 * @param   string  $type         Parameter type.
	 * @param   string  $name         Parameter name.
	 * @param   string  $description  Description text.
	 */
	public function __construct($type, $name, $description)
	{
		$this->type = $type;
		$this->name = $name;
		$this->description = $description;
	}

	/**
	 * To string.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->type . '   ' . $this->name . '  ' . $this->description;
	}
}
