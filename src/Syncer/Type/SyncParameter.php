<?php
/**
 * Joomla! GitHub API syncer.
 *
 * @copyright  Copyright (C) 2016 Nikolai Plath - elkuku.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
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
