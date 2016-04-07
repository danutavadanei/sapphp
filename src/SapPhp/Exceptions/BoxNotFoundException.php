<?php

namespace SapPhp\Exceptions;

use Exception;

class BoxNotFoundException extends Exception
{

   
    /**
     * Create new BoxNotFoundException instance.
     *
     * @param string $box SAP Box
     *
     * @return void
     */
    public function __construct($box)
    {
        parent::__construct("'$box' SAP Box not found in registered boxes.", 0);
    }
}
