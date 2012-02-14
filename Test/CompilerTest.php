<?php

namespace WebLoader\Test;

use WebLoader\Compiler;
use WebLoader\FileCollection;
use WebLoader\DefaultOutputNamingConvention;

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
		$fileCollection = new FileCollection(__DIR__ . '/fixtures');
		$convention = new DefaultOutputNamingConvention();

		$this->object = new Compiler($fileCollection, $convention, __DIR__ . '/temp');
	}

	public function testGenerate()
	{


	}

	public function testGenerateIfModified()
	{

	}

	//

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
	 * @expectedException \InvalidArgumentException
	 */
	public function testNonCallableFilter()
	{
		$this->object->addFilter(4);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testNonCallableFileFilter()
	{
		$this->object->addFileFilter(4);
	}

}
