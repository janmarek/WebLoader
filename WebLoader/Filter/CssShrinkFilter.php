<?php

namespace WebLoader\Filter;

/**
 * Shrink CSS file
 *
 * @author Radovan Kepak
 * @author Gary Jones
 * @license MIT
 */
class CssShrinkFilter {
	/**
	 * Invoke filter
	 * @param string $code
	 * @param \WebLoader\Compiler $loader
	 * @param string $file
	 * @return string
	 */
	public function __invoke($code, \WebLoader\Compiler $loader, $file = null)
	{

		// Normalize whitespace
		$code = preg_replace('/\s+/', ' ', $code);

		// Remove spaces before and after comment
		$code = preg_replace('/(\s+)(\/\*(.*?)\*\/)(\s+)/', '$2', $code);

		// Remove comment blocks, everything between /* and */, unless
		// preserved with /*! ... */
		$code = preg_replace('/\/\*(?!\!)(.*?)\*\//', '', $code);

		// Remove ; before }
		$code = preg_replace('/;(?=\s*})/', '', $code);

		// Remove space after , : ; { } */ >
		$code = preg_replace('/(,|:|;|\{|}|\*\/|>) /', '$1', $code);

		// Remove space before , ; { } ( ) >
		$code = preg_replace('/ (,|;|\{|}|\(|\)|>)/', '$1', $code);

		// Strips leading 0 on decimal values (converts 0.5px into .5px)
		$code = preg_replace('/(:| )0\.([0-9]+)(%|em|ex|px|in|cm|mm|pt|pc)/i', '${1}.${2}${3}', $code);

		// Strips units if value is 0 (converts 0px to 0)
		$code = preg_replace('/(:| )(\.?)0(%|em|ex|px|in|cm|mm|pt|pc)/i', '${1}0', $code);

		// Converts all zeros value into short-hand
		$code = preg_replace('/0 0 0 0/', '0', $code);

		// Shortern 6-character hex color codes to 3-character where possible
		$code = preg_replace('/#([a-f0-9])\\1([a-f0-9])\\2([a-f0-9])\\3/i', '#\1\2\3', $code);

		// Return trimed string
		return trim($code);
	}
}
