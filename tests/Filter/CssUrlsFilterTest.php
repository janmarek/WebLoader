<?php

namespace WebLoader\Test\Filter;

use WebLoader\Compiler;
use WebLoader\DefaultOutputNamingConvention;
use WebLoader\FileCollection;
use WebLoader\Filter\CssUrlsFilter;

class CssUrlsFilterTest extends \PHPUnit_Framework_TestCase
{

	/** @var CssUrlsFilter */
	private $object;

	protected function setUp()
	{
		$this->filter = new CssUrlsFilter(__DIR__ . '/..', '/');

		$files = new FileCollection(__DIR__ . '/../fixtures');
		@mkdir($outputDir = __DIR__ . '/../temp/');
		$this->compiler = new Compiler($files, new DefaultOutputNamingConvention(), $outputDir);
	}

	public function testCannonicalizePath()
	{
		$path = $this->filter->cannonicalizePath('/prase/./dobytek/../ale/nic.jpg');
		$this->assertEquals('/prase/ale/nic.jpg', $path);
	}

	public function testAbsolutizeAbsolutized()
	{
		$cssPath = __DIR__ . '/../fixtures/style.css';

		$url = 'http://image.com/image.jpg';
		$this->assertEquals($url, $this->filter->absolutizeUrl($url, '\'', $cssPath));

		$abs = '/images/img.png';
		$this->assertEquals($abs, $this->filter->absolutizeUrl($abs, '\'', $cssPath));

		$abs = 'https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic';
		$this->assertEquals($abs, $this->filter->absolutizeUrl($abs, '\'', $cssPath));

		$abs = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUAAAAFCAYAAACNbyblAAAAHElEQVQI12P4//8/w38GIAXDIBKE0DHxgljNBAAO9TXL0Y4OHwAAAABJRU5ErkJggg==';
		$this->assertEquals($abs, $this->filter->absolutizeUrl($abs, '\'', $cssPath));
	}

	public function testAbsolutize()
	{
		$cssPath = __DIR__ . '/../fixtures/style.css';

		$this->assertEquals(
			'/images/image.png',
			$this->filter->absolutizeUrl('./../images/image.png', '\'', $cssPath)
		);

		$this->assertEquals(
			'/images/path/to/image.png',
			$this->filter->absolutizeUrl('./../images/path/./to/image.png', '\'', $cssPath)
		);
	}

	public function testAbsolutizeOutsideOfDocRoot()
	{
		$path = './../images/image.png';
		$existingPath = __DIR__ . '/../../Compiler.php';
		$this->assertEquals($path, $this->filter->absolutizeUrl($path, '\'', $existingPath));
	}

	public function testInvoke()
	{
		$cssPath = __DIR__ . '/../fixtures/style.css';
		$code = file_get_contents($cssPath);

		$css = $this->filter->__invoke($code, $this->compiler, $cssPath);

		$this->assertSame(file_get_contents(__DIR__ . '/../fixtures/style.css.expected'), $css);
	}
}
