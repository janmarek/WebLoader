<?php

namespace WebLoader;

use Nette\DI\Container;
use Nette\Http\Request;
use WebLoader\Nette\CssLoader;
use WebLoader\Nette\Extension;
use WebLoader\Nette\JavaScriptLoader;

class LoaderFactory
{
	/** @var \Nette\Http\Request */
	private $httpRequest;

	/** @var \Nette\DI\Container */
	private $serviceLocator;

	/** @var array */
	private $tempPaths;

	/**
	 * @param array $tempPaths
	 * @param Request $httpRequest
	 * @param \Nette\DI\Container $serviceLocator
	 */
	public function __construct(array $tempPaths, Request $httpRequest, Container $serviceLocator)
	{
		$this->httpRequest = $httpRequest;
		$this->serviceLocator = $serviceLocator;
		$this->tempPaths = $tempPaths;
	}

	/**
	 * @param string $name
	 * @return \WebLoader\Nette\CssLoader
	 */
	public function createCssLoader($name)
	{
		/** @var Compiler $compiler */
		$compiler = $this->serviceLocator->getService('webloader.css' . ucfirst($name) . 'Compiler');
		return new CssLoader($compiler, $this->formatTempPath($name));
	}

	/**
	 * @param string $name
	 * @return \WebLoader\Nette\JavaScriptLoader
	 */
	public function createJavaScriptLoader($name)
	{
		/** @var Compiler $compiler */
		$compiler = $this->serviceLocator->getService('webloader.js' . ucfirst($name) . 'Compiler');
		return new JavaScriptLoader($compiler, $this->formatTempPath($name));
	}

	/**
	 * @param string $name
	 * @return string
	 */
	private function formatTempPath($name)
	{
		$lName = strtolower($name);
		$tempPath = isset($this->tempPaths[$lName]) ? $this->tempPaths[$lName] : Extension::DEFAULT_TEMP_PATH;
		return rtrim($this->httpRequest->url->basePath, '/') . '/' . $tempPath;
	}

}
