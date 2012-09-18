<?php

namespace WebLoader\Test\Nette;

use Nette\Utils\Finder;

class ExtensionTest extends \PHPUnit_Framework_TestCase
{

	/** @var \Nette\DI\Container */
	private $container;

	protected function setUp()
	{
		$fixturesDir = __DIR__ . '/../fixtures';
		$tempDir = __DIR__ . '/../temp';
		Finder::findFiles('*')->exclude('.*')->in($tempDir . '/cache');

		$configurator = new \Nette\Config\Configurator();
		$configurator->setTempDirectory($tempDir);
		$configurator->addConfig($fixturesDir . '/extension.neon', FALSE);
		$configurator->addParameters(array(
			'fixturesDir' => $fixturesDir,
			'tempDir' => $tempDir,
		));

		$extension = new \WebLoader\Nette\Extension();
		$extension->install($configurator);

		$this->container = @$configurator->createContainer(); // @ - headers already sent
	}

	public function testJsCompilerService()
	{
		$this->assertInstanceOf('WebLoader\Compiler', $this->container->webloader->jsDefaultCompiler);
	}

	public function testExcludeFiles()
	{
		$files = $this->container->webloader->jsExcludeCompiler->getFileCollection()->getFiles();

		$this->assertTrue(in_array(realpath(__DIR__ . '/../fixtures/a.txt'), $files));
		$this->assertFalse(in_array(realpath(__DIR__ . '/../fixtures/dir/one.js'), $files));
	}

}
