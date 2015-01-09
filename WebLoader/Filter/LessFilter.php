<?php

namespace WebLoader\Filter;

/**
 * Less CSS filter
 *
 * @author Jan Marek
 * @license MIT
 */
class LessFilter
{

	private $lc;

	public function __construct(\lessc $lc = NULL)
	{
		$this->lc = $lc;
	}

	/**
	 * @return \lessc
	 */
	private function getLessC()
	{
		// lazy loading
		if (empty($this->lc)) {
			$this->lc = new \lessc();
		}

		return clone $this->lc;
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
		if (pathinfo($file, PATHINFO_EXTENSION) === 'less') {
			$lessc = $this->getLessC();
			$lessc->importDir = pathinfo($file, PATHINFO_DIRNAME) . '/';
			return $lessc->compile($code);
		}

		return $code;
	}

}
