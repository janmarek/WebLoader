<?php

namespace WebLoader\Test\Path;

use WebLoader\Path;

class PathTest extends \PHPUnit_Framework_TestCase
{

	public function testNormalize()
	{
		$normalized = Path::normalize('/path/to//project//that/contains/0/in/it');
		$this->assertEquals('/path/to/project/that/contains/0/in/it', $normalized);
	}

}
