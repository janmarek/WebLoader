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
	/** @var CoffeeScriptCompiler */
	private $compiler;

	/**
	 * @param CoffeeScriptCompiler
	 */
	public function __construct(CoffeeScriptCompiler $compiler)
	{
		$this->compiler = $compiler;
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
			$code = $this->compiler->compile($code);
		}

		return $code;
	}
}