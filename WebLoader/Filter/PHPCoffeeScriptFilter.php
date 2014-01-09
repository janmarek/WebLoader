<?php

namespace WebLoader\Filter;

use CoffeeScript\Compiler;



/**
 * Coffee script filter implements with composer php compiler
 *
 * @author Jan Svantner
 */
class PHPCoffeeScriptFilter
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
	 * @param $source $string
	 * @param $file bool|NULL
	 * @throws \WebLoader\WebLoaderException
	 * @return string
	 */
	public function compileCoffee($source, $file)
	{
		try {
			return Compiler::compile($source, array('filename' => $file));
		} catch (\Exception $e) {
			throw new \WebLoader\WebLoaderException('CoffeeScript Filter Error: ' . $e->getMessage(), 0, $e);
		}
	}
}
