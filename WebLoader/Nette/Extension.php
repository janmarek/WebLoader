<?php

namespace WebLoader\Nette;

use Nette\Configurator;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Nette\DI\Config\Helpers;
use Nette\DI\ContainerBuilder;
use Nette\Utils\Finder;

if (!class_exists('Nette\DI\CompilerExtension')) {
	class_alias('Nette\Config\CompilerExtension', 'Nette\DI\CompilerExtension');
	class_alias('Nette\Config\Compiler', 'Nette\DI\Compiler');
	class_alias('Nette\Config\Helpers', 'Nette\DI\Config\Helpers');
	class_alias('Nette\Config\Configurator', 'Nette\Configurator');
}


/**
 * @author Jan Marek
 */
class Extension extends CompilerExtension
{

	const EXTENSION_NAME = 'webloader';

	public function getDefaultConfig()
	{
		return array(
			'jsDefaults' => array(
				'sourceDir' => '%wwwDir%/js',
				'tempDir' => '%wwwDir%/webtemp',
				'tempPath' => 'webtemp',
				'files' => array(),
				'remoteFiles' => array(),
				'filters' => array(),
				'fileFilters' => array(),
				'joinFiles' => TRUE,
				'namingConvention' => '@' . $this->prefix('jsNamingConvention'),
			),
			'cssDefaults' => array(
				'sourceDir' => '%wwwDir%/css',
				'tempDir' => '%wwwDir%/webtemp',
				'tempPath' => 'webtemp',
				'files' => array(),
				'remoteFiles' => array(),
				'filters' => array(),
				'fileFilters' => array(),
				'joinFiles' => TRUE,
				'namingConvention' => '@' . $this->prefix('cssNamingConvention'),
			),
			'js' => array(

			),
			'css' => array(

			),
		);
	}

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig($this->getDefaultConfig());

		$builder->addDefinition($this->prefix('cssNamingConvention'))
			->setFactory('WebLoader\DefaultOutputNamingConvention::createCssConvention');

		$builder->addDefinition($this->prefix('jsNamingConvention'))
			->setFactory('WebLoader\DefaultOutputNamingConvention::createJsConvention');

		$builder->parameters['webloader'] = $config;

		foreach (array('css', 'js') as $type) {
			foreach ($config[$type] as $name => $wlConfig) {
				$configDefault = $config[$type . 'Defaults'];
				$this->addWebLoader($builder, $type . ucfirst($name), array_merge($configDefault, $wlConfig));
			}
		}
	}

	private function addWebLoader(ContainerBuilder $builder, $name, $config)
	{
		$filesServiceName = $this->prefix($name . 'Files');

		$files = $builder->addDefinition($filesServiceName)
			->setClass('WebLoader\FileCollection')
			->setArguments(array($config['sourceDir']));

		foreach ($config['files'] as $file) {
			// finder support
			if (is_array($file) && isset($file['files']) && (isset($file['in']) || isset($file['from']))) {
				$finder = Finder::findFiles($file['files']);

				if (isset($file['exclude'])) {
					$finder->exclude($file['exclude']);
				}

				if (isset($file['in'])) {
					$finder->in($file['in']);
				} else {
					$finder->from($file['from']);
				}

				foreach ($finder as $foundFile) {
					$files->addSetup('addFile', array((string) $foundFile));
				}
			} else {
				$files->addSetup('addFile', array($file));
			}
		}

		$files->addSetup('addRemoteFiles', array($config['remoteFiles']));

		$compiler = $builder->addDefinition($this->prefix($name . 'Compiler'))
			->setClass('WebLoader\Compiler')
			->setArguments(array(
				'@' . $filesServiceName,
				$config['namingConvention'],
				$config['tempDir'],
			));

		$compiler->addSetup('setJoinFiles', array($config['joinFiles']));

		foreach ($config['filters'] as $filter) {
			$compiler->addSetup('addFilter', array($filter));
		}

		foreach ($config['fileFilters'] as $filter) {
			$compiler->addSetup('addFileFilter', array($filter));
		}

		// todo css media
	}

	public function install(Configurator $configurator)
	{
		$self = $this;
		$configurator->onCompile[] = function ($configurator, Compiler $compiler) use ($self) {
			$compiler->addExtension($self::EXTENSION_NAME, $self);
		};
	}

}
