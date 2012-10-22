<?php

namespace WebLoader\Test;

use WebLoader\DefaultOutputNamingConvention;

/**
 * DefaultOutputNamingConvention test
 *
 * @author Jan Marek
 */
class DefaultOutputNamingConventionTest extends \PHPUnit_Framework_TestCase
{

	/** @var DefaultOutputNamingConvention */
	private $object;

	private $compiler;

	protected function setUp()
	{
		$this->object = new DefaultOutputNamingConvention();
		$this->compiler = \Mockery::mock('Webloader\Compiler');
	}

	public function testMultipleFiles()
	{
		$files = array(
			__DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'a.txt',
			__DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'b.txt',
		);

		$name = $this->object->getFilename($files, $this->compiler);
		$this->assertRegExp('/^webloader-[0-9a-f]{12}$/', $name);

		// another hash
		$files[] = __DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'c.txt';
		$name2 = $this->object->getFilename($files, $this->compiler);
		$this->assertNotEquals($name, $name2, 'Different file lists results to same filename.');
	}

	public function testOneFile()
	{
		$files = array(
			__DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'a.txt',
		);

		$name = $this->object->getFilename($files, $this->compiler);
		$this->assertRegExp('/^webloader-[0-9a-f]{12}-a$/', $name);
	}

	public function testCssConvention()
	{
		$files = array(
			__DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'a.txt',
		);

		$name = DefaultOutputNamingConvention::createCssConvention()->getFilename($files, $this->compiler);
		$this->assertRegExp('/^cssloader-[0-9a-f]{12}-a.css$/', $name);
	}

	public function testJsConvention()
	{
		$files = array(
			__DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'a.txt',
		);

		$name = DefaultOutputNamingConvention::createJsConvention()->getFilename($files, $this->compiler);
		$this->assertRegExp('/^jsloader-[0-9a-f]{12}-a.js$/', $name);
	}

}
