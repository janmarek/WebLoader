<?php

namespace WebLoader\Filter;

/**
 * Coffee script filter
 *
 * @author Patrik Votoček
 * @license MIT
 */
class CoffeeScriptFilter
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
	 * Invoke filter
	 *
	 * @param string
	 * @param \WebLoader\Compiler
	 * @param string
	 * @return string
	 */
	public function __invoke($code, \WebLoader\Compiler $loader, $file = NULL)
	{
		if (pathinfo($file, PATHINFO_EXTENSION) === 'coffee') {
			$code = $this->compileCoffee($code);
		}

		return $code;
	}

	/**
	 * @param string
	 * @param bool|NULL
	 * @return string
	 */
	public function compileCoffee($source, $bare = NULL)
	{
		if (is_null($bare)) {
			$bare = $this->bare;
		}

		$cmd = $this->bin . ' -p -s' . ($bare ? ' -b' : '');

		return Process::run($cmd, $source);
	}

}