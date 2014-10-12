<?php

namespace WebLoader\Test\Filter;

use WebLoader\Compiler;
use WebLoader\DefaultOutputNamingConvention;
use WebLoader\FileCollection;
use WebLoader\Filter\LessFilter;

class LessFilterTest extends \PHPUnit_Framework_TestCase
{
	/** @var LessFilter */
	private $filter;

	/** @var Compiler */
	private $compiler;

	protected function setUp()
	{
		$this->filter = new LessFilter(new \lessc());

		$files = new FileCollection(__DIR__ . '/../fixtures');
		@mkdir($outputDir = __DIR__ . '/../temp/');
		$this->compiler = new Compiler($files, new DefaultOutputNamingConvention(), $outputDir);
	}

	public function testReplace()
	{
		$file = __DIR__ . '/../fixtures/style.less';
		$less = $this->filter->__invoke(file_get_contents($file), $this->compiler, $file);
		$this->assertSame(file_get_contents(__DIR__ . '/../fixtures/style.less.expected'), $less);
	}

}
