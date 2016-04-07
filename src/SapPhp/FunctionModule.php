<?php

namespace SapPhp;

use Closure;
use sapnwrfc;
use SapPhp\Exceptions\ParamNotFoundException;

class FunctionModule
{
    /**
     * sapnwrfc_function Handle.
     *
     * @var sapnwrfc_functio
     */
    public $fm;

    /**
     * sapnwrfc_function Paramters.
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * Parse function output.
     *
     * @var bool
     */
    protected $parse;

    /**
     * Parser Closure.
     *
     * @var Closure
     */
    protected $parser;

    /**
     * Create a new instance of FunctionModule.
     *
     * @param sapnwrfc $handle
     * @param string   $name
     */
    public function __construct(sapnwrfc $handle, $name, $parse = true)
    {
        $this->parse = $parse;
        $this->fm = $handle->function_lookup(strtoupper($name));
    }

    /**
     * Get FunctionModule description.
     *
     * @return array
     */
    public function description()
    {
        return json_decode(json_encode($this->fm), true);
    }

    /**
     * Add fm parameter.
     *
     * @param string $name
     * @param string $value
     *
     * @return $this
     */
    public function param($name, $value)
    {
        // Force to uppercase to prevent unwanted errors.
        // All fms, paramaters and table names are uppercase in SAP.
        $name = strtoupper($name);

        if (!isset($this->fm->{$name})) {
            throw new ParamNotFoundException($name, $this->fm);
        }

        // If parameter is set and is an array, push value to array.
        if (isset($this->parameters[$name])) {
            if (is_array($this->parameters[$name])) {
                if (is_array($value)) {
                    $this->parameters[$name] = array_merge($this->parameters[$name], $value);
                } else {
                    $this->parameters[$name][] = $value;
                }

                return $this;
            }
        }

        $this->parameters[$name] = $value;

        return $this;
    }

    /**
     * Add fm parameters.
     *
     * @param array $params
     *
     * @return $this
     */
    public function params($params)
    {
        foreach ($params as $name => $value) {
            $this->param($name, $value);
        }

        return $this;
    }

    /**
     * Invoke function and return result.
     *
     * @return mixed
     */
    public function invoke()
    {
        $result = $this->fm->invoke($this->parameters);

        // Parse result if trigger is true.
        if ($this->parse) {
            if ($this->parser instanceof Closure) {
                return $this->parser->__invoke($result);
            }

            return array_decode_guid(array_trim($result));
        }

        return $result;
    }
}
