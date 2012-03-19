<?php

namespace WebLoader;

/**
 * DefaultNamingConvention
 *
 * @author Jan Marek
 */
class DefaultOutputNamingConvention implements IOutputNamingConvention
{

	/** @var string */
	private $prefix = 'webloader-';

	/** @var string */
	private $suffix = '';

	/**
	 * @return DefaultOutputNamingConvention
	 */
	public static function createCssConvention()
	{
		$convention = new static();
		$convention->setPrefix('cssloader-');
		$convention->setSuffix('.css');

		return $convention;
	}

	/**
	 * @return DefaultOutputNamingConvention
	 */
	public static function createJsConvention()
	{
		$convention = new static();
		$convention->setPrefix('jsloader-');
		$convention->setSuffix('.js');

		return $convention;
	}

	/**
	 * Get generated file name prefix
	 * @return string
	 */
	public function getPrefix()
	{
		return $this->prefix;
	}

	/**
	 * Set generated file name prefix
	 * @param string $prefix generated file name prefix
	 */
	public function setPrefix($prefix)
	{
		$this->prefix = (string) $prefix;
	}


	/**
	 * Get generated file name suffix
	 * @return string
	 */
	public function getSuffix()
	{
		return $this->suffix;
	}


	/**
	 * Set generated file name suffix
	 * @param string $suffix generated file name suffix
	 */
	public function setSuffix($suffix)
	{
		$this->suffix = (string) $suffix;
	}

	/**
	 * Filename of generated file
	 * @param array $files
	 * @param \WebLoader\Compiler $compiler
	 * @return string
	 */
	public function getFilename(array $files, Compiler $compiler)
	{
		$name = $this->createHash($files, $compiler);

		if (count($files) === 1) {
			$name .= "-" . pathinfo($files[0], PATHINFO_FILENAME);
		}

		return $this->prefix . $name . $this->suffix;
	}

	protected function createHash(array $files, Compiler $compiler)
	{
		return substr(md5(implode("|", $files)), 0, 12);
	}

}
