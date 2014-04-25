<?php

namespace WebLoader\Filter;

/**
 * Scss CSS filter
 *
 * @author Roman Matěna
 * @license MIT
 */
class ScssFilter
{

	/**
	 * @var \Leafo\ScssPhp\Compiler
	 */
	private $sc;

	public function __construct(\Leafo\ScssPhp\Compiler $sc = NULL)
	{
		$this->sc = $sc;
	}

	/**
	 * @return \Leafo\ScssPhp\Compiler|\scssc
	 */
	private function getScssC()
	{
		// lazy loading
		if (empty($this->sc)) {
			$this->sc = new \Leafo\ScssPhp\Compiler();
		}

		return $this->sc;
	}

	/**
	 * Invoke filter
	 * @param string $code
	 * @param \WebLoader\Compiler $loader
	 * @param string $file
	 * @return string
	 */
	public function __invoke($code, \WebLoader\Compiler $loader, $file)
	{
		if (pathinfo($file, PATHINFO_EXTENSION) === 'scss') {
			$this->getScssC()->setImportPaths(pathinfo($file, PATHINFO_DIRNAME) . '/');
			return $this->getScssC()->compile($code);
		}

		return $code;
	}

}
