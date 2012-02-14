<?php

namespace WebLoader;

/**
 * IOutputNamingConvention
 *
 * @author Jan Marek
 */
interface IOutputNamingConvention
{

	public function getFilename(array $files, Compiler $compiler);

}
