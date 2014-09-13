<?php

namespace WebLoader\Filter;
use CoffeeScript;

/**
 * Coffee script filter using a PHP-based compiler
 *
 * @author Jan Buchar
 * @license MIT
 */
class CoffeeScriptPhpFilter
{
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
			$code = $this->compileCoffee($code, $file);
		}

		return $code;
	}

	/**
	 * @param string
	 * @return string
	 */
	protected function compileCoffee($source, $file)
	{
		return CoffeeScript\Compiler::compile($source, array('filename' => $file));
	}

}
