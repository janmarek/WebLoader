<?php

namespace WebLoader\Filter;

/**
 * Less CSS fileFilter
 *
 * @author Jan Marek
 * @author pvy
 * @license MIT
 */
class LessFilter
{

	private $lessC;

	/**
	 * Makes possible to inject a customized object
	 * @param \lessc $lessC
	 */
	public function setLessC(\lessc $lessC){
	    $this->lessC = $lessC;
	}

	/**
	 * @return \lessc
	 */
	private function getLessC()
	{
		// lazy loading
		if (empty($this->lessC)) {
			$this->lessC = new \lessc;
		}

		return $this->lessC;
	}

	/**
	 * Invoke filter
	 * @param string $code
	 * @param \WebLoader\Compiler $loader
	 * @param string $file
	 * @return string
	 */
	public function __invoke($code, \WebLoader\Compiler $loader, $file = NULL)
	{
		if (pathinfo($file, PATHINFO_EXTENSION) === 'less') {

			$lc = $this->getLessC();

			//is import from current directory enabled?
			if(in_array("",$lc->importDir)){

				//current directory is added only for this compilation
				$oldImportDir=$lc->importDir;
				$lc->addImportDir(pathinfo($file, PATHINFO_DIRNAME) . '/');
				$output = $lc->compile($code);
				$lc->setImportDir($oldImportDir);
				
				return $output;
			}

			return $lc->compile($code);

		}

		return $code;
	}

}
