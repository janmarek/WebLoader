<?php

namespace WebLoader\Nette;

/**
 * @author Jan Marek
 */
class Extension extends \Nette\Config\CompilerExtension
{

	const EXTENSION_NAME = 'webloader';

	public function getDefaultConfig()
	{
		return array(
			'jsDefaults' => array(
				'sourceDir' => '%wwwDir%/js',
				'tempDir' => '%wwwDir%/webtemp',
				'tempPath' => './webtemp',
				'files' => array(),
				'remoteFiles' => array(),
				'filters' => array(),
				'fileFilters' => array(),
				'namingConvention' => '@' . $this->prefix('jsNamingConvention'),
			),
			'cssDefaults' => array(
				'sourceDir' => '%wwwDir%/css',
				'tempDir' => '%wwwDir%/webtemp',
				'tempPath' => './webtemp',
				'files' => array(),
				'remoteFiles' => array(),
				'filters' => array(),
				'fileFilters' => array(),
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
				$this->addWebLoader($builder, $type . '_' . $name, array_merge($configDefault, $wlConfig));
			}
		}
	}

	private function addWebLoader(\Nette\DI\ContainerBuilder $builder, $name, $config)
	{
		$filesServiceName = $this->prefix($name . '_files');

		$files = $builder->addDefinition($filesServiceName)
			->setClass('WebLoader\FileCollection')
			->setArguments(array($config['sourceDir']));

		foreach ($config['files'] as $file) {
			// finder support
			if (is_array($file) && isset($file['files']) && (isset($file['in']) || isset($file['from']))) {
				$finder = \Nette\Utils\Finder::findFiles($file['files']);

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

		$compiler = $builder->addDefinition($this->prefix($name . '_compiler'))
			->setClass('WebLoader\Compiler')
			->setArguments(array(
				'@' . $filesServiceName,
				$config['namingConvention'],
				$config['tempDir'],
			));

		foreach ($config['filters'] as $filter) {
			$compiler->addSetup('addFilter', $filter);
		}

		foreach ($config['fileFilters'] as $filter) {
			$compiler->addSetup('addFileFilter', $filter);
		}

		// todo css media
	}

	public function install(\Nette\Config\Configurator $configurator)
	{
		$self = $this;
		$configurator->onCompile[] = function ($configurator, $compiler) use ($self) {
		    $compiler->addExtension($self::EXTENSION_NAME, $self);
		};
	}

}
