<?php

namespace WebLoader\Test\Nette;

use Nette\Utils\Finder;

class ExtensionTest extends \PHPUnit_Framework_TestCase
{

	/** @var \Nette\DI\Container */
	private $container;

	private function prepareContainer($configFiles)
	{
		$tempDir = __DIR__ . '/../temp';
		foreach (Finder::findFiles('*')->exclude('.gitignore')->from($tempDir . '/cache') as $file) {
			unlink((string) $file);
		}

		$configurator = new \Nette\Configurator();
		$configurator->setTempDirectory($tempDir);

		foreach ($configFiles as $file) {
			$configurator->addConfig($file, FALSE);
		}

		$configurator->addParameters(array(
			'wwwDir' =>  __DIR__ . '/..',
			'fixturesDir' =>  __DIR__ . '/../fixtures',
			'tempDir' => $tempDir,
		));

		$extension = new \WebLoader\Nette\Extension();
		$extension->install($configurator);

		$this->container = @$configurator->createContainer(); // sends header X-Powered-By, ...
	}

	public function testJsCompilerService()
	{
		$this->prepareContainer(array(__DIR__ . '/../fixtures/extension.neon'));
		$this->assertInstanceOf('WebLoader\Compiler', $this->container->getService('webloader.jsDefaultCompiler'));
	}

	public function testExcludeFiles()
	{
		$this->prepareContainer(array(__DIR__ . '/../fixtures/extension.neon'));
		$files = $this->container->getService('webloader.jsExcludeCompiler')->getFileCollection()->getFiles();

		$this->assertTrue(in_array(realpath(__DIR__ . '/../fixtures/a.txt'), $files));
		$this->assertFalse(in_array(realpath(__DIR__ . '/../fixtures/dir/one.js'), $files));
	}

	public function testJoinFilesOn()
	{
		$this->prepareContainer(array(
			__DIR__ . '/../fixtures/extension.neon',
			__DIR__ . '/../fixtures/extensionJoinFilesTrue.neon',
		));
		$this->assertTrue($this->container->getService('webloader.jsDefaultCompiler')->getJoinFiles());
	}

	public function testJoinFilesOff()
	{
		$this->prepareContainer(array(
			__DIR__ . '/../fixtures/extension.neon',
			__DIR__ . '/../fixtures/extensionJoinFilesFalse.neon',
		));
		$this->assertFalse($this->container->getService('webloader.jsDefaultCompiler')->getJoinFiles());
	}

	public function testJoinFilesOffInOneService()
	{
		$this->prepareContainer(array(
			__DIR__ . '/../fixtures/extension.neon',
		));
		$this->assertFalse($this->container->getService('webloader.cssJoinOffCompiler')->getJoinFiles());
	}

	public function testNonceSet()
	{
		$this->prepareContainer(array(
			__DIR__ . '/../fixtures/extension.neon',
			__DIR__ . '/../fixtures/extensionNonce.neon',
		));
		$this->assertEquals('rAnd0m123', $this->container->getService('webloader.jsDefaultCompiler')->getNonce());
	}

	public function testNonceNotSet()
	{
		$this->prepareContainer(array(
			__DIR__ . '/../fixtures/extension.neon',
		));
		$this->assertNull($this->container->getService('webloader.jsDefaultCompiler')->getNonce());
	}

	public function testExtensionName()
	{
		$tempDir = __DIR__ . '/../temp';
		$class = 'ExtensionNameServiceContainer';

		$configurator = new \Nette\Configurator();
		$configurator->setTempDirectory($tempDir);
		$configurator->addParameters(array('container' => array('class' => $class)));
		$configurator->onCompile[] = function ($configurator, \Nette\DI\Compiler $compiler) {
			$compiler->addExtension('Foo', new \WebLoader\Nette\Extension());
		};
		$configurator->addConfig(__DIR__ . '/../fixtures/extensionName.neon', false);
		$container = $configurator->createContainer();

		$this->assertInstanceOf('WebLoader\Compiler', $container->getService('Foo.cssDefaultCompiler'));
		$this->assertInstanceOf('WebLoader\Compiler', $container->getService('Foo.jsDefaultCompiler'));
	}

}
