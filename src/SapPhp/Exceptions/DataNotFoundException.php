<?php

namespace SapPhp\Exceptions;

use Exception;

class DataNotFoundException extends Exception
{	
	/**
	 * Create new DataNotFoundException instance.
	 * 
	 * @return void
	 */
	public function __construct($iniPath, $xmlPath)
	{
		$message = "\n        While generating \SapPhp\Repository data no file was found.\n" .
			"        Searched for: saplogon.ini file at '$iniPath',\n" .
			"                      sapphp.xml   file at '$xmlPath'.\n";
		parent::__construct($message, 0);
	}
}
