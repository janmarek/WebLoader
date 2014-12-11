<?php

namespace WebLoader\Nette;

use Nette\Http\IRequest;
use WebLoader\Filter\CssUrlsFilter;



/**
 * @author Jan Marek
 * @license MIT
 */
class CssUrlFilter extends CssUrlsFilter
{

	public function __construct($docRoot, IRequest $httpRequest)
	{
		parent::__construct($docRoot, $httpRequest->getUrl()->getBasePath());
	}

}
