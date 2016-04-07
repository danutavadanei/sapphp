<?php

if (! function_exists('array_trim')) {
    /**
     * Trim all values from an array recursively.
     *
     * @param  mixed $array
     * 
     * @return array
     */
    function array_trim($input)
    {
        if (!is_array($input))
	        return trim($input);
	 
	    return array_map('array_trim', $input);
    }
}

if (! function_exists('array_decode_guid')) {
    /**
     * Decode all GUID from an array.
     *
     * @param  mixed $array
     * 
     * @return array
     */
    function array_decode_guid($input)
    {
        if (!is_array($input)){
        	if (mb_strlen($input) !== strlen($input)) {
        		if (strlen($guid = strtoupper(unpack('h*', $input)[1])) === 32) {
        			return $guid;
        		}
        	}
        	return $input;
        }
	 
	    return array_map('array_decode_guid', $input);
    }
}

if (! function_exists('array_utf8ize')) {
    /**
     * UTF8 encode array.
     *
     * @param  mixed $array
     * 
     * @return array
     */
    function array_utf8ize($input)
    {
        if (is_array($input)) {
            foreach ($input as $key => $value) {
                $input[$key] = array_utf8ize($value);
            }
        } else if (is_string ($input)) {
            return utf8_encode($input);
        }
        return $input;
    }
}
