<?php

namespace WebLoader\Filter;

/**
 * Compile coffee script to javascript
 *
 * @author Patrik Votoček
 * @license MIT
 */
class CoffeeScriptCompiler
{

	/** @var path to coffee bin */
	private $bin;

	/** @var bool */
	public $bare = FALSE;

	/**
	 * @param string
	 */
	public function __construct($bin = 'coffee')
	{
		$this->bin = $bin;
	}

	/**
	 * @param string
	 * @param bool|NULL
	 * @return string
	 */
	public function compile($source, $bare = NULL)
	{
		if (is_null($bare)) {
			$bare = $this->bare;
		}

		$cmd = $this->bin . ' -p -s' . ($bare ? ' -b' : '');
		return Process::run($cmd, $source);
	}

}