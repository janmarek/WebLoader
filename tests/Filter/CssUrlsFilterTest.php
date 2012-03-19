<?php

namespace WebLoader\Test\Filter;

use WebLoader\Filter\CssUrlsFilter;

/**
 * CompilerTest
 *
 * @author Jan Marek
 */
class CssUrlsFilterTest extends \PHPUnit_Framework_TestCase
{

	/** @var CssUrlsFilter */
	private $object;

	protected function setUp()
	{
		$this->object = new CssUrlsFilter(__DIR__ . '/..', '/');
	}

	public function testCannonicalizePath()
	{
		$path = $this->object->cannonicalizePath('/prase/./dobytek/../ale/nic.jpg');
		$this->assertEquals('/prase/ale/nic.jpg', $path);
	}

	public function testAbsolutizeAbsolutized()
	{
		$cssPath = __DIR__ . '/../fixtures/style.css';

		$url = 'http://image.com/image.jpg';
		$this->assertEquals($url, $this->object->absolutizeUrl($url, '\'', $cssPath));

		$abs = '/images/img.png';
		$this->assertEquals($abs, $this->object->absolutizeUrl($abs, '\'', $cssPath));
	}

	public function testAbsolutize()
	{
		$cssPath = __DIR__ . '/../fixtures/style.css';

		$this->assertEquals(
			'/images/image.png',
			$this->object->absolutizeUrl('./../images/image.png', '\'', $cssPath)
		);

		$this->assertEquals(
			'/images/path/to/image.png',
			$this->object->absolutizeUrl('./../images/path/./to/image.png', '\'', $cssPath)
		);
	}

	public function testAbsolutizeOutsideOfDocRoot()
	{
		$path = './../images/image.png';
		$existingPath = __DIR__ . '/../../Compiler.php';
		$this->assertEquals($path, $this->object->absolutizeUrl($path, '\'', $existingPath));
	}

}
