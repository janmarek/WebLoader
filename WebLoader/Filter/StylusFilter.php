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

	/** @var bool */
	public $includeCss = FALSE;

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
			$path =
			$cmd = $this->bin . ($this->compress ? ' -c' : '') . ($this->includeCss ? ' --include-css' : '') . ' -I ' . pathinfo($file, PATHINFO_DIRNAME);
			try {
				$code = Process::run($cmd, $code);
			} catch (\RuntimeException $e) {
				throw new \WebLoader\WebLoaderException('Stylus Filter Error', 0, $e);
			}
		}

		return $code;
	}

}
