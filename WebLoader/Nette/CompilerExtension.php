<?php

namespace WebLoader\Nette;

use Nette\Config\Configurator;

/**
 * WebLoader Compiler Extension for Nette
 *
 * @author Patrik Votoček
 * @license MIT
 */
class CompilerExtension extends \Nette\Config\CompilerExtension
{
	public function loadConfiguration()
	{
		$config = $this->getConfig(array(
			'js' => array(
				'files' => array(),
				'remoteFiles' => array(),
				'baseDir' => '%wwwDir%/js',
				'pubTempDir' => '%wwwDir%/assets',
				'filters' => array(
					'snockets' => array(
						'enable' => FALSE,
						'coffee' => NULL, // null == autodetect
					),
					'coffeescript' => array(
						'enable' => FALSE,
						'bin' => 'coffee',
						'bare' => FALSE,
					),
					'variables' => array(
						'enable' => FALSE,
						'variables' => array(),
						'delimiters' => array(
							'start' => '{{$',
							'end' => '}}',
						),
					),
				),
			),
			'css' => array(
				'files' => array(),
				'remoteFiles' => array(),
				'baseDir' => '%wwwDir%/css',
				'pubTempDir' => '%wwwDir%/assets',
				'filters' => array(
					'less' => array(
						'enable' => FALSE,
					),
					'stylus' => array(
						'enable' => FALSE,
						'bin' => 'stylus',
						'compress' => FALSE,
					),
					'variables' => array(
						'enable' => FALSE,
						'variables' => array(),
						'delimiters' => array(
							'start' => '{{$',
							'end' => '}}',
						),
					),
				),
			),
		));
		$builder = $this->getContainerBuilder();

		// JavaScript
		$jsFiles = $builder->addDefinition($this->prefix('jsFiles'))
			->setClass('WebLoader\FileCollection', isset($config['js']['baseDir']) ? array($config['js']['baseDir']) : array())
			->addSetup('addFiles', array($config['js']['files']))
			->addSetup('addRemoteFiles', array($config['js']['remoteFiles']));

		$jsCompiler = $builder->addDefinition($this->prefix('jsCompiler'))
			->setClass('WebLoader\Compiler')
			->setFactory('WebLoader\Compiler::createJsCompiler', array($jsFiles, $config['js']['pubTempDir']));

		// CSS
		$cssFiles = $builder->addDefinition($this->prefix('cssFiles'))
			->setClass('WebLoader\FileCollection', isset($config['css']['baseDir']) ? array($config['css']['baseDir']) : array())
			->addSetup('addFiles', array($config['css']['files']))
			->addSetup('addRemoteFiles', array($config['css']['remoteFiles']));

		$cssCompiler = $builder->addDefinition($this->prefix('cssCompiler'))
			->setClass('WebLoader\Compiler')
			->setFactory('WebLoader\Compiler::createCssCompiler', array($cssFiles, $config['css']['pubTempDir']));


		// JavaScript Filters
		if (isset($config['js']['filters'])) {
			$filters = $config['js']['filters'];

			// Coffee Script Compiler
			if (isset($filters['coffeescript']) && isset($filters['coffeescript']['enable']) && $filters['coffeescript']['enable']) {
				$coffee = $builder->addDefinition($this->prefix('jsCoffeeScriptCompiler'))
					->setClass('WebLoader\Filter\CoffeeScriptCompiler',
						isset($filters['coffeescript']['bin']) ? array($filters['coffeescript']['bin']) : array('coffee')
					);

				if (array_key_exists('bare', $filters['coffeescript'])) {
					$coffee->addSetup('@self::$bare', array($filters['coffeescript']['bare']));
				}
			}

			// Snockets
			if (isset($filters['snockets']) && isset($filters['snockets']['enable']) && $filters['snockets']['enable']) {
				$snockets = $builder->addDefinition($this->prefix('jsSnocketsFilter'))
					->setClass('WebLoader\Filter\SnocketsFilter');

				if (isset($filters['snockets']['coffee']) && $filters['snockets']['coffee']) {
					$snockets->addSetup('setCoffee', array($this->prefix('@jsCoffeeCompiler')));
				} elseif (array_key_exists('coffee', $filters['snockets']) && $filters['snockets']['coffee'] === NULL && isset($coffee)) {
					$snockets->addSetup('setCoffee', array($coffee));
				}

				$jsCompiler->addSetup('addFileFilter', array($snockets));
			}

			// Coffee Script
			if (isset($filters['coffeescript']) && isset($filters['coffeescript']['enable']) && $filters['coffeescript']['enable']) {
				$coffeescript = $builder->addDefinition($this->prefix('jsCoffeeScriptFilter'))
					->setClass('WebLoader\Filter\CoffeeScriptFilter', array($coffee));

				$jsCompiler->addSetup('addFileFilter', array($coffeescript));
			}

			// Variables
			if (isset($filters['variables']) && isset($filters['variables']['enable']) && $filters['variables']['enable']) {
				$variables = $builder->addDefinition($this->prefix('jsVariablesFilter'))
					->setClass('WebLoader\Filter\VariablesFilter',
						isset($filters['variables']['variables']) && array($filters['variables']['variables']), array(array())
					);

				if (isset($filters['variables']['delimiters'])
				 && isset($filters['variables']['delimiters']['start']) && isset($filters['variables']['delimiters']['end'])) {
					$variables->addSetup('setDelimiter', array(
						$filters['variables']['delimiters']['start'],
						$filters['variables']['delimiters']['end']
					));
				}

				$jsCompiler->addSetup('addFilter', array($variables));
			}
		}

		// CSS Filters
		if (isset($config['css']['filters'])) {
			$filters = $config['css']['filters'];

			// Less
			if (isset($filters['less']) && isset($filters['less']['enable']) && $filters['less']['enable']) {
				$less = $builder->addDefinition($this->prefix('cssLessFilter'))
					->setClass('WebLoader\Filter\LessFilter');

				$cssCompiler->addSetup('addFileFilter', array($less));
			}

			// Stylus
			if (isset($filters['stylus']) && isset($filters['stylus']['enable']) && $filters['stylus']['enable']) {
				$stylus = $builder->addDefinition($this->prefix('cssStylusCompiler'))
					->setClass('WebLoader\Filter\StylusFilter',
						isset($filters['stylus']['bin']) ? array($filters['stylus']['bin']) : array('stylus')
					);

				if (array_key_exists('compress', $filters['stylus'])) {
					$stylus->addSetup('@self::$compress', array($filters['stylus']['compress']));
				}

				$cssCompiler->addSetup('addFileFilter', array($stylus));
			}

			// Variables
			if (isset($filters['variables']) && isset($filters['variables']['enable']) && $filters['variables']['enable']) {
				$variables = $builder->addDefinition($this->prefix('cssVariablesFilter'))
					->setClass('WebLoader\Filter\VariablesFilter',
						isset($filters['variables']['variables']) && array($filters['variables']['variables']), array(array())
					);

				if (isset($filters['variables']['delimiters'])
				 && isset($filters['variables']['delimiters']['start']) && isset($filters['variables']['delimiters']['end'])) {
					$variables->addSetup('setDelimiter', array(
						$filters['variables']['delimiters']['start'],
						$filters['variables']['delimiters']['end']
					));
				}

				$cssCompiler->addSetup('addFilter', array($variables));
			}
		}
	}

	/**
	 * Register extension to compiler.
	 *
	 * @param \Nette\Config\Configurator
	 * @param string
	 */
	public static function register(Configurator $configurator, $name = 'webloader')
	{
		$class = get_called_class();
		$configurator->onCompile[] = function(Configurator $configurator, \Nette\Config\Compiler $compiler) use($class, $name) {
			$compiler->addExtension($name, new $class);
		};
	}
}
