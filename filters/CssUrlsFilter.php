<?php

namespace WebLoader;

use Nette\Utils\Strings;


/**
 * Absolutize urls in CSS
 *
 * @author Jan Marek
 * @license MIT
 */
class CssUrlsFilter extends \Nette\Object
{
	/** @var \Nette\DI\IContainer */
	private $context;



	public function __construct(\Nette\DI\IContainer $container)
	{
		$this->context = $container;
	}



	/**
	 * Make relative url absolute
	 * @param string image url
	 * @param string single or double quote
	 * @param string absolute css file path
	 * @param string source path
	 * @return string
	 */
	public function absolutizeUrl($url, $quote, $cssFile, $sourcePath)
	{
		// is already absolute
		if (preg_match('~^([a-z]+:/)?/~', $url)) return $url;

		$docroot = realpath($this->context->params['wwwDir']);
		$basePath = $this->context->httpRequest->url->basePath;

		// inside document root
		if (Strings::startsWith($cssFile, $docroot)) {
			$path = $basePath . substr(dirname($cssFile), strlen($docroot)) . '/' . $url;

		// outside document root
		} else {
			$path = $basePath . substr($sourcePath, strlen($docroot)) . '/' . $url;
		}

		$path = self::cannonicalizePath($path);

		return $quote === '"' ? addslashes($path) : $path;
	}



	/**
	 * Cannonicalize path
	 * @param string path
	 * @return path
	 */
	private static function cannonicalizePath($path)
	{
		$path = str_replace('\\', '/', $path);
		$path = str_replace('//', '/', $path);

		foreach (explode('/', $path) as $i => $name) {
			if ($name === "." || ($name === "" && $i > 0)) continue;

			if ($name === "..") {
				array_pop($pathArr);
				continue;
			}

			$pathArr[] = $name;
		}

		return implode("/", $pathArr);
	}



	/**
	 * Invoke filter
	 * @param string code
	 * @param WebLoader loader
	 * @param string file
	 * @return string
	 */
	public function __invoke($code, WebLoader $loader, $file = NULL)
	{
		// thanks to kravco
		$regexp = '~
			(?<![a-z])
			url\(                                     ## url(
				\s*                                   ##   optional whitespace
				([\'"])?                              ##   optional single/double quote
				(   (?: (?:\\\\.)+                    ##     escape sequences
					|   [^\'"\\\\,()\s]+              ##     safe characters
					|   (?(1)   (?!\1)[\'"\\\\,() \t] ##       allowed special characters
						|       ^                     ##       (none, if not quoted)
						)
					)*                                ##     (greedy match)
				)
				(?(1)\1)                              ##   optional single/double quote
				\s*                                   ##   optional whitespace
			\)                                        ## )
		~xs';

		$that = $this;

		return Strings::replace($code, $regexp, function ($matches) use ($that, $loader, $file) {
			return "url('" . $that->absolutizeUrl($matches[2], $matches[1], $file, $loader->sourcePath) . "')";
		});
	}

}