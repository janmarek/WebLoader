<?php

namespace WebLoader\Test\Filter;

use WebLoader\Filter\VariablesFilter;

/**
 * CompilerTest
 *
 * @author Jan Marek
 */
class VariablesFilterTest extends \PHPUnit_Framework_TestCase
{

	/** @var VariablesFilter */
	private $object;

	protected function setUp()
	{
		$this->object = new VariablesFilter(array(
			'foo' => 'bar',
		));
	}

	public function testReplace()
	{
		$this->object->bar = 'baz';

		$filter = $this->object;

		$code = 'a tak sel {{$foo}} za {{$bar}}em a potkali druheho {{$foo}}';

		$filtered = $filter($code);

		$this->assertEquals('a tak sel bar za bazem a potkali druheho bar', $filtered);
	}

	public function testDelimiters()
	{
		$this->object->setDelimiter('[', ']');
		$this->assertEquals('bar', call_user_func($this->object, '[foo]'));
	}

}
