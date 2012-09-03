<?php

namespace WebLoader\Filter;

/**
 * Stylus filter
 *
 * @author Patrik Votoček
 * @license MIT
 */
class StylusFilter
{

	/** @var string */
	private $bin;

	/** @var bool */
	public $compress = FALSE;

	/**
	 * @param string
	 */
	public function __construct($bin = 'stylus')
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
		if (pathinfo($file, PATHINFO_EXTENSION) === 'styl') {
			$cmd = $this->bin . ($this->compress ? ' -c' : '');
			$code = Process::run($cmd, $code);
		}

		return $code;
	}

}