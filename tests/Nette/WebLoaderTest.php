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

	const FILE_COLLECTION_ROOT_PATH = __DIR__ . '/../fixtures';

	const SOURCE_FILE_DIR_PATH = __DIR__ . '/../fixtures/dir';

	const SOURCE_FILE_NAME = 'one.css';

	const TEMP_PATH = __DIR__ . '/../temp';

	private $hashes = [
		self::HASHES_SHA256 => 'sha256-Ss8LOdnEdmcJo2ifVTrAGrVQVF/6RUTfwLLOqC+6AqM=',
		self::HASHES_SHA384 => 'sha384-OZ7wmy2rB2wregDCOAvEmnrP7wUiSrCbaFEn6r86mq6oPm8oqDrZMRy2GnFPUyxm',
		self::HASHES_SHA512 => 'sha512-xIr1p/bUqFH8ikNO7WOKsabvaOGdvK6JSsZ8n7xbywGCuOcSOz3zyeTct2kMIxA/A9wX9UNSBxzrKk6yBLJrkQ==',
	];

	public function setUp()
	{
		@mkdir(self::TEMP_PATH);
		copy(
			self::SOURCE_FILE_DIR_PATH  . '/' . self::SOURCE_FILE_NAME,
			self::TEMP_PATH . '/' . self::SOURCE_FILE_NAME
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
			self::SOURCE_FILE_DIR_PATH . '/' . self::SOURCE_FILE_NAME
		);
		$expected = file_get_contents(self::SOURCE_FILE_DIR_PATH . '/' . self::SOURCE_FILE_NAME);

		$this->assertSame($expected, $compiledFileContentResult);
	}

	/**
	 * @param array $hashingAlgorithms
	 * @return Compiler
	 */
	private function getCompiler($hashingAlgorithms = [])
	{
		$files = new FileCollection(self::FILE_COLLECTION_ROOT_PATH);
		$compiler = new Compiler($files, new DefaultOutputNamingConvention(), self::TEMP_PATH);

		foreach ($hashingAlgorithms as $alhorithm) {
			$compiler->addSriHashingAlgorithm($alhorithm);
		}

		return $compiler;
	}

	/**
	 * @param Compiler $compiler
	 * @return WebLoader
	 */
	private function getWebLoader(Compiler $compiler)
	{
		return new class($compiler, self::TEMP_PATH) extends WebLoader
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
		};
	}
}
