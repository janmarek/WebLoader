<?php

namespace WebLoader\Test\Nette;

use WebLoader\Compiler;
use WebLoader\DefaultOutputNamingConvention;
use WebLoader\FileCollection;
use WebLoader\Nette\WebLoader;

class WebLoaderTest extends \PHPUnit_Framework_TestCase
{
	const HASHES_SHA256 = 'sha256';

	const HASHES_SHA384 = 'sha384';

	const HASHES_SHA512 = 'sha512';

	const HASHES_TEST_STRING = 'testString';

	const SOURCE_FILE_NAME = 'one.css';

	private $hashes = [
		self::HASHES_SHA256 => 'sha256-Ss8LOdnEdmcJo2ifVTrAGrVQVF/6RUTfwLLOqC+6AqM=',
		self::HASHES_SHA384 => 'sha384-OZ7wmy2rB2wregDCOAvEmnrP7wUiSrCbaFEn6r86mq6oPm8oqDrZMRy2GnFPUyxm',
		self::HASHES_SHA512 => 'sha512-xIr1p/bUqFH8ikNO7WOKsabvaOGdvK6JSsZ8n7xbywGCuOcSOz3zyeTct2kMIxA/A9wX9UNSBxzrKk6yBLJrkQ==',
	];

	private $fileCollectionRootPath;

	private $sourceFileDirPath;

	private $tempPath;

	public function setUp()
	{
		$this->fileCollectionRootPath = __DIR__ . '/../fixtures';
		$this->sourceFileDirPath = __DIR__ . '/../fixtures/dir';
		$this->tempPath = __DIR__ . '/../temp';

		@mkdir($this->tempPath);
		copy(
			$this->sourceFileDirPath  . '/' . self::SOURCE_FILE_NAME,
			$this->tempPath . '/' . self::SOURCE_FILE_NAME
		);
	}

	/**
	 * @dataProvider provideTestGetSriChecksums
	 * @param $hashingAlgorithms
	 * @param $fileContent
	 * @param $expected
	 */
	public function testGetSriChecksums($hashingAlgorithms, $fileContent, $expected)
	{
		$compiler = $this->getCompiler($hashingAlgorithms);
		$webloader = $this->getWebLoader($compiler);
		$sriChecksumsResult = $webloader->getSriChecksumsResult($fileContent);

		$this->assertSame($expected, $sriChecksumsResult);
	}

	public function provideTestGetSriChecksums()
	{
		return [
			[
				[],
				self::HASHES_TEST_STRING,
				'',
			],
			[
				[
					self::HASHES_SHA256,
				],
				self::HASHES_TEST_STRING,
				$this->hashes[self::HASHES_SHA256],
			],
			[
				[
					self::HASHES_SHA256,
					self::HASHES_SHA512,
				],
				self::HASHES_TEST_STRING,
				implode(' ', [
					$this->hashes[self::HASHES_SHA256],
					$this->hashes[self::HASHES_SHA512],
				]),
			],
		];
	}

	public function testGetCompiledFileContent()
	{
		$compiler = $this->getCompiler();
		$webloader = $this->getWebLoader($compiler);
		$compiledFileContentResult = $webloader->getCompiledFileContentResult(
			$this->sourceFileDirPath . '/' . self::SOURCE_FILE_NAME
		);
		$expected = file_get_contents($this->sourceFileDirPath . '/' . self::SOURCE_FILE_NAME);

		$this->assertSame($expected, $compiledFileContentResult);
	}

	/**
	 * @param array $hashingAlgorithms
	 * @return Compiler
	 */
	private function getCompiler($hashingAlgorithms = [])
	{
		$files = new FileCollection($this->fileCollectionRootPath);
		$compiler = new Compiler($files, new DefaultOutputNamingConvention(), $this->tempPath);

		foreach ($hashingAlgorithms as $alhorithm) {
			$compiler->addSriHashingAlgorithm($alhorithm);
		}

		return $compiler;
	}

	/**
	 * @param Compiler $compiler
	 * @return WebLoaderTestImplementation
	 */
	private function getWebLoader(Compiler $compiler)
	{
		return new WebLoaderTestImplementation($compiler, $this->tempPath);
	}
}


class WebLoaderTestImplementation extends WebLoader
{
	public function getCompiledFileContentResult($source)
	{
		return $this->getCompiledFileContent($source);
	}

	public function getSriChecksumsResult($fileContent)
	{
		return $this->getSriChecksums($fileContent);
	}

	public function getElement($source)
	{
		// not important now
	}
}
