<?php

namespace WebLoader;

/**
 * Variables filter for WebLoader
 *
 * @author Jan Marek
 * @license MIT
 */
class VariablesFilter extends \Nette\Object {

	/** @var string */
	private $startVariable = '{{$';

	/** @var string */
	private $endVariable = '}}';

	/** @var array */
	private $variables;


	/**
	 * Construct
	 * @param array variables
	 */
	public function __construct(array $variables = array()) {
		foreach ($variables as $key => $value) {
			$this->$key = $value;
		}
	}


	/**
	 * Set delimiter
	 * @param string start
	 * @param string end
	 * @return VariablesFilter
	 */
	public function setDelimiter($start, $end) {
		$this->startVariable = (string) $start;
		$this->endVariable = (string) $end;
		return $this;
	}


	/**
	 * Invoke filter
	 * @param string code
	 * @return string
	 */
	public function __invoke($code) {
		$start = $this->startVariable;
		$end = $this->endVariable;

		$variables = array_map(function ($key) use ($start, $end) {
			return $start . $key . $end;
		}, array_keys($this->variables));

		$values = array_values($this->variables);

		return str_replace($variables, $values, $code);
	}


	/**
	 * Magic set variable, do not call directly
	 * @param string name
	 * @param string value
	 */
	public function __set($name, $value) {
		$this->variables[$name] = (string) $value;
	}


	/**
	 * Magic get variable, do not call directly
	 * @param string name
	 * @return string
	 * @throws \Nette\InvalidArgumentException
	 */
	public function & __get($name) {
		if (array_key_exists($name, $this->variables)) {
			return $this->variables[$name];
		} else {
			throw new \Nette\InvalidArgumentException("Variable '$name' is not set.");
		}
	}

}