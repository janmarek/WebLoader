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

	/** @var string */
	private $extensionName;

	/**
	 * @param array $tempPaths
	 * @param string $extensionName
	 * @param IRequest $httpRequest
	 * @param Container $serviceLocator
	 */
	public function __construct(array $tempPaths, $extensionName, IRequest $httpRequest, Container $serviceLocator)
	{
		$this->httpRequest = $httpRequest;
		$this->serviceLocator = $serviceLocator;
		$this->tempPaths = $tempPaths;
		$this->extensionName = $extensionName;
	}

	/**
	 * @param string $name
	 * @return \WebLoader\Nette\CssLoader
	 */
	public function createCssLoader($name)
	{
		/** @var Compiler $compiler */
		$compiler = $this->serviceLocator->getService($this->extensionName . '.css' . ucfirst($name) . 'Compiler');
		return new CssLoader($compiler, $this->formatTempPath('css', $name));
	}

	/**
	 * @param string $name
	 * @return \WebLoader\Nette\JavaScriptLoader
	 */
	public function createJavaScriptLoader($name)
	{
		/** @var Compiler $compiler */
		$compiler = $this->serviceLocator->getService($this->extensionName . '.js' . ucfirst($name) . 'Compiler');
		return new JavaScriptLoader($compiler, $this->formatTempPath('js', $name));
	}

	/**
	 * @param string $name
	 * @return string
	 */
	private function formatTempPath($type, $name)
	{
		$lName = strtolower($name);
		$tempPath = isset($this->tempPaths[$type][$lName]) ? $this->tempPaths[$type][$lName] : Extension::DEFAULT_TEMP_PATH;
		return rtrim($this->httpRequest->getUrl()->basePath, '/') . '/' . $tempPath;
	}

}
