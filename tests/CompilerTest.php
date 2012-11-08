<?php

namespace WebLoader\Test;

use Mockery;
use WebLoader\Compiler;

/**
 * CompilerTest
 *
 * @author Jan Marek
 */
class CompilerTest extends \PHPUnit_Framework_TestCase
{

	/** @var \WebLoader\Compiler */
	private $object;

	protected function setUp()
	{
		$fileCollection = Mockery::mock('WebLoader\IFileCollection');
		$fileCollection->shouldReceive('getFiles')->andReturn(array(
			__DIR__ . '/fixtures/a.txt',
			__DIR__ . '/fixtures/b.txt',
			__DIR__ . '/fixtures/c.txt',
		));

		$convention = Mockery::mock('WebLoader\IOutputNamingConvention');
		$convention->shouldReceive('getFilename')->andReturnUsing(function ($files, $compiler) {
			return 'webloader-' . md5(join(',', $files));
		});

		$this->object = new Compiler($fileCollection, $convention, __DIR__ . '/temp');

		foreach ($this->getTempFiles() as $file) {
			unlink($file);
		}
	}

	/**
	 * @return array
	 */
	private function getTempFiles()
	{
		return glob(__DIR__ . '/temp/webloader-*');
	}

	public function testJoinFiles()
	{
		$this->assertTrue($this->object->getJoinFiles());

		$ret = $this->object->generate();
		$this->assertEquals(1, count($ret), 'Multiple files are generated instead of join.');
		$this->assertEquals(1, count($this->getTempFiles()), 'Multiple files are generated instead of join.');
	}

	public function testNotJoinFiles()
	{
		$this->object->setJoinFiles(FALSE);
		$this->assertFalse($this->object->getJoinFiles());

		$ret = $this->object->generate();
		$this->assertEquals(3, count($ret), 'Wrong file count generated.');
		$this->assertEquals(3, count($this->getTempFiles()), 'Wrong file count generated.');
	}

	/**
	 * @expectedException \WebLoader\FileNotFoundException
	 */
	public function testSetOutDir()
	{
		$this->object->setOutputDir('blablabla');
	}

	public function testGeneratingAndFilters()
	{
		$this->object->addFileFilter(function ($code) {
			return strrev($code);
		});
		$this->object->addFileFilter(function ($code, Compiler $compiler, $file) {
			return pathinfo($file, PATHINFO_FILENAME) . ':' . $code . ',';
		});
		$this->object->addFilter(function ($code, Compiler $compiler) {
			return '-' . $code;
		});
		$this->object->addFilter(function ($code) {
			return $code . $code;
		});

		$expectedContent = '-' . PHP_EOL . 'a:cba,' . PHP_EOL . 'b:fed,' . PHP_EOL .
			'c:ihg,-' . PHP_EOL . 'a:cba,' . PHP_EOL . 'b:fed,' . PHP_EOL . 'c:ihg,';

		$files = $this->object->generate();

		$this->assertTrue(is_numeric($files[0]->lastModified), 'Generate does not provide last modified timestamp correctly.');

		$content = file_get_contents($this->object->getOutputDir() . '/' . $files[0]->file);

		$this->assertEquals($expectedContent, $content);
	}

	public function testGenerateReturnsSourceFilePaths()
	{
		$res = $this->object->generate();
		$this->assertInternalType('array', $res[0]->sourceFiles);
		$this->assertCount(3, $res[0]->sourceFiles);
		$this->assertFileExists($res[0]->sourceFiles[0]);
	}

	public function testFilters()
	{
		$filter = function ($code, \WebLoader\Compiler $loader) {
			return $code . $code;
		};
		$this->object->addFilter($filter);
		$this->object->addFilter($filter);
		$this->assertEquals(array($filter, $filter), $this->object->getFilters());
	}

	public function testFileFilters()
	{
		$filter = function ($code, \WebLoader\Compiler $loader, $file = null) {
			return $code . $code;
		};
		$this->object->addFileFilter($filter);
		$this->object->addFileFilter($filter);
		$this->assertEquals(array($filter, $filter), $this->object->getFileFilters());
	}

	/**
	 * @expectedException \WebLoader\InvalidArgumentException
	 */
	public function testNonCallableFilter()
	{
		$this->object->addFilter(4);
	}

	/**
	 * @expectedException \WebLoader\InvalidArgumentException
	 */
	public function testNonCallableFileFilter()
	{
		$this->object->addFileFilter(4);
	}

}
