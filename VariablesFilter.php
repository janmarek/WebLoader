<?php

/**
 * Variables filter for WebLoader
 *
 * @author Jan Marek
 * @license MIT
 */
class VariablesFilter extends Object
{
	/** @var string */
	private $startVariable = "{{\$";

	/** @var string */
	private $endVariable = "}}";

	/** @var array */
	private $variables;

	/**
	 * Construct
	 * @param array $variables
	 */
	public function __construct(array $variables = array()) {
		$this->variables = $variables;
	}

	/**
	 * Set variable
	 * @param string $name
	 * @param string $value
	 */
	public function setVariable($name, $value)
	{
		$this->variables[$name] = $value;
	}

	/**
	 * Set delimiter
	 * @param string $start
	 * @param string $end
	 */
	public function setDelimiter($start, $end)
	{
		$this->startVariable = $start;
		$this->endVariable = $end;
	}

	/**
	 * Apply string
	 * @param string $s
	 * @return string
	 */
	public function apply($s)
	{
		$variables = array();
		$values = array();

		foreach ($this->variables as $key => $value) {
			$variables[] = $this->startVariable . $key . $this->endVariable;
			$values[] = $value;
		}

		return str_replace($variables, $values, $s);
	}
}