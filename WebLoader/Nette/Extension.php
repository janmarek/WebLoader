<?php

namespace WebLoader\Nette;

// from Kdyby
if (!class_exists('Nette\DI\CompilerExtension')) {
	class_alias('Nette\Config\CompilerExtension', 'Nette\DI\CompilerExtension');
	class_alias('Nette\Config\Compiler', 'Nette\DI\Compiler');
	class_alias('Nette\Config\Helpers', 'Nette\DI\Config\Helpers');
}

if (isset(\Nette\Loaders\NetteLoader::getInstance()->renamed['Nette\Configurator']) || !class_exists('Nette\Configurator')) {
	unset(\Nette\Loaders\NetteLoader::getInstance()->renamed['Nette\Configurator']); // fuck you
	class_alias('Nette\Config\Configurator', 'Nette\Configurator');
}

/**
 * @author Jan Marek
 */
class Extension extends \Nette\DI\CompilerExtension
{

	const EXTENSION_NAME = 'webloader';

	public function getDefaultConfig()
	{
		return array(
			'jsDefaults' => array(
				'factory' => 'createJavaScriptLoader',
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
				'factory' => 'createCssLoader',
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

		$builder->addDefinition($this->prefix('factory'))
			->setClass('WebLoader\LoaderFactory');


		foreach (array('css', 'js') as $type) {
			foreach ($config[$type] as $name => $wlConfig) {
				$configDefault = $config[$type . 'Defaults'];
				$this->addWebLoader($builder, $type . ucfirst($name), array_merge($configDefault, $wlConfig));
			}
		}
	}

	private function addWebLoader(\Nette\DI\ContainerBuilder $builder, $name, $config)
	{
		$filesServiceName = $this->prefix($name . 'Files');

		$files = $builder->addDefinition($filesServiceName)
			->setClass('WebLoader\FileCollection')
			->setArguments(array($config['sourceDir']));

		foreach ($config['files'] as $file) {
			// finder support
			if (is_array($file) && isset($file['files']) && (isset($file['in']) || isset($file['from']))) {
				$finder = \Nette\Utils\Finder::findFiles($file['files']);

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

		$compiler->addSetup('setJoinFiles', array($config['joinFiles']) );

		foreach ($config['filters'] as $filter) {
			$compiler->addSetup('addFilter', array($filter) );
		}

		foreach ($config['fileFilters'] as $filter) {
			$compiler->addSetup('addFileFilter', array($filter) );
		}

		if (isset($config['factory']) ) {

			$builder->addDefinition($this->prefix($name . 'Loader'))
				->setFactory('@' . $this->prefix('factory') . '::' . $config['factory'])
				->setArguments(array(
					$builder->getDefinition( $this->prefix($name . 'Compiler') ),
					$config['tempPath']
				));
		}

		// todo css media
	}

	public function install(\Nette\Configurator $configurator)
	{
		$self = $this;
		$configurator->onCompile[] = function ($configurator, $compiler) use ($self) {
			$compiler->addExtension($self::EXTENSION_NAME, $self);
		};
	}

}
