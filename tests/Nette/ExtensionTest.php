<?php

namespace WebLoader\Test\Nette;

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

		$this->container = $configurator->createContainer();
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

}
