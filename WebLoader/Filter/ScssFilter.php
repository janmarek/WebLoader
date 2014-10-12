<?php

namespace WebLoader\Filter;

/**
 * Scss CSS filter
 *
 * @author Jan Marek
 * @license MIT
 */
class ScssFilter
{

	private $sc;

	public function __construct(\scssc $sc = NULL)
	{
		$this->sc = $sc;
	}

	/**
	 * @return \scssc
	 */
	private function getScssC()
	{
		// lazy loading
		if (empty($this->sc)) {
			$this->sc = new \scssc();
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
			$this->getScssC()->importDir = pathinfo($file, PATHINFO_DIRNAME) . '/';
			return $this->getScssC()->parse($code);
		}

		return $code;
	}

}
