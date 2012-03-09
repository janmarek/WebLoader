<?php

namespace WebLoader\Test;

use WebLoader\FileCollection;

/**
 * FileCollection test
 *
 * @author Jan Marek
 */
class FileCollectionTest extends \PHPUnit_Framework_TestCase
{

	/** @var FileCollection */
	private $object;

	protected function setUp()
	{
		$this->object = new FileCollection(__DIR__ . '/fixtures');
	}

	public function testAddGetFiles()
	{
		$this->object->addFile('a.txt');
		$this->object->addFile(__DIR__ . '/fixtures/a.txt');
		$this->object->addFile(__DIR__ . '/fixtures/b.txt');
		$this->object->addFiles(array(__DIR__ . '/fixtures/c.txt'));
		$expected = array(
			__DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'a.txt',
			__DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'b.txt',
			__DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'c.txt',
		);
		$this->assertEquals($expected, $this->object->getFiles());
	}

	/**
	 * @expectedException \Webloader\FileNotFoundException
	 */
	public function testAddNonExistingFile()
	{
		$this->object->addFile('sdfsdg.txt');
	}

	public function testRemoveFile()
	{
		$this->object->addFile(__DIR__ . '/fixtures/a.txt');
		$this->object->addFile(__DIR__ . '/fixtures/b.txt');

		$this->object->removeFile('a.txt');
		$expected = array(
			__DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'b.txt',
		);
		$this->assertEquals($expected, $this->object->getFiles());

		$this->object->removeFiles(array(__DIR__ . '/fixtures/b.txt'));
	}

	public function testCannonicalizePath()
	{
		$abs = __DIR__ . '/./fixtures/a.txt';
		$rel = 'a.txt';
		$expected = __DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'a.txt';

		$this->assertEquals($expected, $this->object->cannonicalizePath($abs));
		$this->assertEquals($expected, $this->object->cannonicalizePath($rel));

		try {
			$this->object->cannonicalizePath('nesdagf');
			$this->fail('Exception was not thrown.');
		} catch (\WebLoader\FileNotFoundException $e) {
		}
	}

	public function testClear()
	{
		$this->object->addFile('a.txt');
		$this->object->addRemoteFile('http://jquery.com/jquery.js');
		$this->object->clear();

		$this->assertEquals(array(), $this->object->getFiles());
		$this->assertEquals(array(), $this->object->getRemoteFiles());
	}

	public function testRemoteFiles()
	{
		$this->object->addRemoteFile('http://jquery.com/jquery.js');
		$this->object->addRemoteFiles(array(
			'http://jquery.com/jquery.js',
			'http://google.com/angular.js',
		));

		$expected = array(
			'http://jquery.com/jquery.js',
			'http://google.com/angular.js',
		);
		$this->assertEquals($expected, $this->object->getRemoteFiles());
	}

	public function testTraversableFiles()
	{
		$this->object->addFiles(new \ArrayIterator(array('a.txt')));
		$this->assertEquals(1, count($this->object->getFiles()));
	}

	public function testTraversableRemoteFiles()
	{
		$this->object->addRemoteFiles(new \ArrayIterator(array('http://jquery.com/jquery.js')));
		$this->assertEquals(1, count($this->object->getRemoteFiles()));
	}

	public function testSplFileInfo()
	{
		$this->object->addFile(new \SplFileInfo(__DIR__ . '/fixtures/a.txt'));
		$this->assertEquals(1, count($this->object->getFiles()));
	}

}
