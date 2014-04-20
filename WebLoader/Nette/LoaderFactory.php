<?php

namespace WebLoader\Nette;

use Nette\DI\Container;
use Nette\Http\IRequest;
use WebLoader\Compiler;

class LoaderFactory
{

	/** @var IRequest */
	private $httpRequest;

	/** @var Container */
	private $serviceLocator;

	/** @var array */
	private $tempPaths;

	/**
	 * @param array $tempPaths
	 * @param IRequest $httpRequest
	 * @param Container $serviceLocator
	 */
	public function __construct(array $tempPaths, IRequest $httpRequest, Container $serviceLocator)
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
		return rtrim($this->httpRequest->getUrl()->basePath, '/') . '/' . $tempPath;
	}

}
