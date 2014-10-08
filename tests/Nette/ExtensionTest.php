<?php

namespace WebLoader\Test\Nette;

use Nette\DI\CompilerExtension;
use Nette\DI\Config\Loader;
use Nette\DI\Statement;
use Nette\Utils\Finder;

if (!class_exists('Nette\DI\CompilerExtension')) {
	class_alias('Nette\Config\CompilerExtension', 'Nette\DI\CompilerExtension');
	class_alias('Nette\Config\Compiler', 'Nette\DI\Compiler');
	class_alias('Nette\Config\Helpers', 'Nette\DI\Config\Helpers');
	class_alias('Nette\Config\Configurator', 'Nette\Configurator');
}

class ExtensionTest extends \PHPUnit_Framework_TestCase
{

	/** @var \Nette\DI\Container */
	private $container;

	private function prepareContainer($class, $configFiles)
	{
		$tempDir = __DIR__ . '/../temp';
		foreach (Finder::findFiles('*')->exclude('.gitignore')->from($tempDir . '/cache') as $file) {
			unlink((string) $file);
		}

		$configurator = new \Nette\Configurator();
		$configurator->setTempDirectory($tempDir);

		$configurator->addConfig(__DIR__ . '/../fixtures/nette-reset.neon');
		foreach ($configFiles as $file) {
			$configurator->addConfig($file, FALSE);
		}

		$configurator->addParameters(array(
			'fixturesDir' =>  __DIR__ . '/../fixtures',
			'tempDir' => $tempDir,
			'container' => array(
				'class' => $class,
			),
		));

		$extension = new \WebLoader\Nette\Extension();
		$extension->install($configurator);

		$this->container = @$configurator->createContainer(); // sends header X-Powered-By, ...
	}

	public function testJsCompilerService()
	{
		$this->prepareContainer('JsCompilerServiceContainer', array(__DIR__ . '/../fixtures/extension.neon'));
		$this->assertInstanceOf('WebLoader\Compiler', $this->container->getService('webloader.jsDefaultCompiler'));
	}

	public function testExcludeFiles()
	{
		$this->prepareContainer('ExcludeFilesContainer', array(__DIR__ . '/../fixtures/extension.neon'));
		$files = $this->container->getService('webloader.jsExcludeCompiler')->getFileCollection()->getFiles();

		$this->assertTrue(in_array(realpath(__DIR__ . '/../fixtures/a.txt'), $files));
		$this->assertFalse(in_array(realpath(__DIR__ . '/../fixtures/dir/one.js'), $files));
	}

	public function testJoinFilesOn()
	{
		$this->prepareContainer('JoinFilesOnContainer', array(
			__DIR__ . '/../fixtures/extension.neon',
			__DIR__ . '/../fixtures/extensionJoinFilesTrue.neon',
		));
		$this->assertTrue($this->container->getService('webloader.jsDefaultCompiler')->getJoinFiles());
	}

	public function testJoinFilesOff()
	{
		$this->prepareContainer('JoinFilesOffContainer', array(
			__DIR__ . '/../fixtures/extension.neon',
			__DIR__ . '/../fixtures/extensionJoinFilesFalse.neon',
		));
		$this->assertFalse($this->container->getService('webloader.jsDefaultCompiler')->getJoinFiles());
	}

	public function testJoinFilesOffInOneService()
	{
		$this->prepareContainer('JoinFilesOffInOneServiceContainer', array(
			__DIR__ . '/../fixtures/extension.neon',
		));
		$this->assertFalse($this->container->getService('webloader.cssJoinOffCompiler')->getJoinFiles());
	}



	public function testSectionsInheritanceCss()
	{
		$config = new Loader;
		$builder = $this->loadConfig($webloader = new \WebLoader\Nette\Extension(), $config->load(__DIR__ . '/../fixtures/extensionInheritanceCss.neon'));

		$this->assertArrayHasKey('webloader.cssCommonFiles', $builder->getDefinitions());
		$this->assertArrayHasKey('webloader.cssCommonCompiler', $builder->getDefinitions());

		$cssCommonFiles = $builder->getDefinition('webloader.cssCommonFiles');
		$this->assertSame(__DIR__ . '/../fixtures', $cssCommonFiles->factory->arguments[0]);
		$this->assertEquals(new Statement('addFile', array(__DIR__ . '/../fixtures/dir/one.css')), $cssCommonFiles->setup[0]);
		$this->assertEquals(new Statement('addFile', array('dir/two.css')), $cssCommonFiles->setup[1]);
		$this->assertEquals(new Statement('addRemoteFiles', array(array('https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css'))), $cssCommonFiles->setup[2]);

		$this->assertArrayHasKey('webloader.cssFrontFiles', $builder->getDefinitions());
		$this->assertArrayHasKey('webloader.cssFrontCompiler', $builder->getDefinitions());

		$cssFrontFiles = $builder->getDefinition('webloader.cssFrontFiles');
		$this->assertSame(__DIR__ . '/../fixtures', $cssFrontFiles->factory->arguments[0]);
		$this->assertEquals(new Statement('addFile', array(__DIR__ . '/../fixtures/dir/one.css')), $cssFrontFiles->setup[0]);
		$this->assertEquals(new Statement('addFile', array('dir/two.css')), $cssFrontFiles->setup[1]);
		$this->assertEquals(new Statement('addFile', array('b.txt')), $cssFrontFiles->setup[2]);
		$this->assertEquals(new Statement('addRemoteFiles', array(array('https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css'))), $cssFrontFiles->setup[3]);

		$this->assertArrayHasKey('webloader.cssAdminFiles', $builder->getDefinitions());
		$this->assertArrayHasKey('webloader.cssAdminCompiler', $builder->getDefinitions());

		$cssAdminFiles = $builder->getDefinition('webloader.cssAdminFiles');
		$this->assertSame(__DIR__ . '/../fixtures', $cssAdminFiles->factory->arguments[0]);
		$this->assertEquals(new Statement('addFile', array(__DIR__ . '/../fixtures/dir/one.css')), $cssAdminFiles->setup[0]);
		$this->assertEquals(new Statement('addFile', array('dir/two.css')), $cssAdminFiles->setup[1]);
		$this->assertEquals(new Statement('addFile', array('a.txt')), $cssAdminFiles->setup[2]);
		$this->assertEquals(new Statement('addRemoteFiles', array(array('https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css'))), $cssAdminFiles->setup[3]);
	}



	public function testSectionsInheritanceJs()
	{
		$config = new Loader;
		$builder = $this->loadConfig($webloader = new \WebLoader\Nette\Extension(), $config->load(__DIR__ . '/../fixtures/extensionInheritanceJs.neon'));

		$this->assertArrayHasKey('webloader.jsCommonFiles', $builder->getDefinitions());
		$this->assertArrayHasKey('webloader.jsCommonCompiler', $builder->getDefinitions());

		$jsCommonFiles = $builder->getDefinition('webloader.jsCommonFiles');
		$this->assertSame(__DIR__ . '/../fixtures', $jsCommonFiles->factory->arguments[0]);
		$this->assertEquals(new Statement('addFile', array(__DIR__ . '/../fixtures/dir/one.js')), $jsCommonFiles->setup[0]);
		$this->assertEquals(new Statement('addFile', array('dir/two.js')), $jsCommonFiles->setup[1]);
		$this->assertEquals(new Statement('addRemoteFiles', array(array(
			'https://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js',
			'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js',
		))), $jsCommonFiles->setup[2]);

		$this->assertArrayHasKey('webloader.jsFrontFiles', $builder->getDefinitions());
		$this->assertArrayHasKey('webloader.jsFrontCompiler', $builder->getDefinitions());

		$jsFrontFiles = $builder->getDefinition('webloader.jsFrontFiles');
		$this->assertSame(__DIR__ . '/../fixtures', $jsFrontFiles->factory->arguments[0]);
		$this->assertEquals(new Statement('addFile', array(__DIR__ . '/../fixtures/dir/one.js')), $jsFrontFiles->setup[0]);
		$this->assertEquals(new Statement('addFile', array('dir/two.js')), $jsFrontFiles->setup[1]);
		$this->assertEquals(new Statement('addFile', array('a.txt')), $jsFrontFiles->setup[2]);
		$this->assertEquals(new Statement('addRemoteFiles', array(
			array(
				'https://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js',
				'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js',
			)
		)), $jsFrontFiles->setup[3]);

		$this->assertArrayHasKey('webloader.jsAdminFiles', $builder->getDefinitions());
		$this->assertArrayHasKey('webloader.jsAdminCompiler', $builder->getDefinitions());

		$jsAdminFiles = $builder->getDefinition('webloader.jsAdminFiles');
		$this->assertSame(__DIR__ . '/../fixtures', $jsAdminFiles->factory->arguments[0]);
		$this->assertEquals(new Statement('addFile', array(__DIR__ . '/../fixtures/dir/one.js')), $jsAdminFiles->setup[0]);
		$this->assertEquals(new Statement('addFile', array('dir/two.js')), $jsAdminFiles->setup[1]);
		$this->assertEquals(new Statement('addFile', array('b.txt')), $jsAdminFiles->setup[2]);
		$this->assertEquals(new Statement('addRemoteFiles', array(
			array(
				'https://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js',
				'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js',
			)
		)), $jsAdminFiles->setup[3]);
	}



	/**
	 * @param CompilerExtension $extension
	 * @param array $config
	 * @return \Nette\DI\ContainerBuilder
	 */
	protected function loadConfig(CompilerExtension $extension, array $config)
	{
		$compiler = new CompilerMock();
		$compiler->addExtension('webloader', $extension);

		$compiler->config = $compiler->getContainerBuilder()->expand($config);

		$extension->loadConfiguration();

		return $compiler->getContainerBuilder();
	}

}



class CompilerMock extends \Nette\DI\Compiler
{

	/**
	 * @var \Nette\DI\ContainerBuilder
	 */
	public $containerBuilder;

	/**
	 * @var array
	 */
	public $config = array();



	public function __construct()
	{
		$this->containerBuilder = new \Nette\DI\ContainerBuilder();
		$this->containerBuilder->parameters = array(
			'appDir' => __DIR__ . '/../..',
			'wwwDir' => __DIR__ . '/../..',
			'tempDir' => __DIR__ . '/../temp',
			'fixturesDir' => __DIR__ . '/../fixtures',
			'debugMode' => FALSE,
			'productionMode' => TRUE,
		);
	}



	/**
	 * @return array
	 */
	public function getConfig()
	{
		return $this->config;
	}



	/**
	 * @return \Nette\DI\ContainerBuilder
	 */
	public function getContainerBuilder()
	{
		return $this->containerBuilder;
	}

}
