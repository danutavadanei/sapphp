<?php

namespace SapPhp\Exceptions;

use Exception;

class ParamNotFoundException extends Exception
{	
	/**
	 * Create new ParamNotFoundException instance.
	 * 
	 * @param string         $box SAP Box
	 * @param FunctionModule $f   FunctionModule instance
	 * 
	 * @return void
	 */
	public function __construct($name, $f)
	{
		parent::__construct("$name parameter not found on the function module. FunctionModule description: " . print_r($f, true), 0);
	}
}
