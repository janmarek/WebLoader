<?php

namespace WebLoader\Filter;

/**
 * SCSS CSS filter
 *
 * @author Martin Petercak
 * @license MIT
 */
class ScssFilter
{

	private $sc;

	/** @var bool */
	public $useCompass = TRUE;

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
			$this->getScssC()->setImportPaths(pathinfo($file, PATHINFO_DIRNAME) . '/');
			if ($this->useCompass === TRUE) {
				new \scss_compass($this->getScssC());
			}
			return $this->getScssC()->compile($code);
		}

		return $code;
	}

}