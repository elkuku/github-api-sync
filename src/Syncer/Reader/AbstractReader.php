<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 09.07.16
 * Time: 15:01
 */

namespace ElKuKu\Syncer\Reader;

abstract class AbstractReader
{
	protected $basePath = '';

	public $ns = 'Joomla\\Github\\Package\\';

	/**
	 * AbstractReader constructor.
	 *
	 * @param $basePath
	 */
	public function __construct($basePath)
	{

		$this->basePath = $basePath;
	}

	abstract public function read();
}