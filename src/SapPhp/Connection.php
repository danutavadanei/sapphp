<?php

namespace SapPhp;

use sapnwrfc;
use SapPhp\Repository;
use SapPhp\BoxNotFoundException;
use SapPhp\Functions\Table\RfcReadTable;

class Connection
{

    /**
     * Cached Connections
     *
     * @var array
     */
    public static $cached = [];

    /**
     * sapnwrfc Handle
     *
     * @var sapnwrfc
     */
    public $handle;

    /**
     * Custom FunctionModules classes
     *
     * @var array
     */
    private static $customFms = [
        'RfcReadTable' => RfcReadTable::class,
    ];

    /**
     * Create a new Connection instance.
     *
     * @param string $box      SAP Box
     * @param string $user     SAP Username
     * @param string $password SAP Password
     * @param string $client   SAP Client
     *
     * @return void
     */
    public function __construct($box, $user, $password, $client)
    {
        // Get box details.
        $box = Repository::get($box, $user, $password, $client);

        if ($handle = self::cached($box)) {
            $this->handle = $handle;
        } else {
            $this->handle = self::connect($box);
        }
    }

    /**
     * Return a new instance of a FunctionModule
     *
     * @param  string   $name
     * @param  bool     $parse
     *
     * @return FunctionModule
     */
    public function fm($name, $parse = true)
    {
        if (isset(self::$customFms[$name])) {
            $class = self::$customFms[$name];
            return new $class($this->handle, $parse);
        }
        return new FunctionModule($this->handle, $name, $parse);
    }

    /**
     * Cache or return sapnwrfc handle
     *
     * @param  string $box    SAP Box
     * @param  string $user   SAP user
     * @param  mixed  $handle sapnwrfc handle
     *
     * @return bool|sappnwrfc
     */
    private static function cached($box, $handle = null)
    {
        $hash = sha1($box['name'] . $box['user']);

        if (isset(self::$cached[$hash])) {
            return self::$cached[$hash];
        } elseif ($handle !== null) {
            return self::$cached[$hash] = $handle;
        } else {
            return false;
        }
    }

    /**
     * Create a new sapnwrfc handle using provided authentification details.
     *
     * @param  Collection $box
     *
     * @return sapnwrfc
     */
    public static function connect($box)
    {
        // Connect to SAP Box.
        return self::cached(
            $box,
            new sapnwrfc($box->toArray())
        );
    }
}
