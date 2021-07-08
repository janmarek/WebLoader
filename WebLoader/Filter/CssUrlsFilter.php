<?php

namespace WebLoader\Filter;

/**
 * Absolutize urls in CSS
 *
 * @author Jan Marek
 * @license MIT
 */
class CssUrlsFilter
{

	/**
	 * @var string
	 */
	private $docRoot;

	/**
	 * @var string
	 */
	protected $basePath;

	/**
	 * @param string $docRoot web document root
	 * @param string $basePath base path
	 * @throws \WebLoader\InvalidArgumentException
	 */
	public function __construct($docRoot, $basePath = '/')
	{
		$this->docRoot = \WebLoader\Path::normalize($docRoot);

		if (!is_dir($this->docRoot)) {
			throw new \WebLoader\InvalidArgumentException('Given document root is not directory.');
		}

		$this->basePath = $basePath;
	}

	/**
	 * @param string $basePath
	 */
	public function setBasePath($basePath)
	{
		$this->basePath = $basePath;
	}

	/**
	 * Make relative url absolute
	 * @param string $url image url
	 * @param string $quote single or double quote
	 * @param string $cssFile absolute css file path
	 * @return string
	 */
	public function absolutizeUrl($url, $quote, $cssFile)
	{
		// is already absolute
		if (preg_match('/^([a-z]+:|\/)/i', $url)) {
			return $url;
		}

		$cssFile = \WebLoader\Path::normalize($cssFile);

		// inside document root
		if (strncmp($cssFile, $this->docRoot, strlen($this->docRoot)) === 0) {
			$path = $this->basePath . substr(dirname($cssFile), strlen($this->docRoot)) . DIRECTORY_SEPARATOR . $url;
		} else {
			// outside document root we don't know
			return $url;
		}

		$path = $this->cannonicalizePath($path);

		return !$quote ? addslashes($path) : $path;
	}

	/**
	 * Cannonicalize path
	 * @param string $path
	 * @return string path
	 */
	public function cannonicalizePath($path)
	{
		$path = strtr($path, DIRECTORY_SEPARATOR, '/');

		$pathArr = array();
		foreach (explode('/', $path) as $i => $name) {
			if ($name === '.' || ($name === '' && $i > 0)) continue;

			if ($name === '..') {
				array_pop($pathArr);
				continue;
			}

			$pathArr[] = $name;
		}

		return implode('/', $pathArr);
	}

	/**
	 * Invoke filter
	 * @param string $code
	 * @param \WebLoader\Compiler $loader
	 * @param string $file
	 * @return string
	 */
	public function __invoke($code, \WebLoader\Compiler $loader, $file = null)
	{
		$regexp = '/
			url\(           ## url(
				\s*             ## optional space
				([\'"])?        ## optional quote
				([^\)]+)            ## anything up to ")" character
				(?(1)\1)        ## optional quote
				\s*             ## optional space
			\)              ## )
		/ix';

		return preg_replace_callback($regexp, function ($matches) use ($file)
		{
			$path = trim($matches[2]);	// Remove new lines, spaces, etc
			$path = trim($path, '\'"');	// Remove quotes on each end (if any)

			return "url('" . $this->absolutizeUrl($path, $matches[1], $file) . "')";
		}, $code);
	}

}
