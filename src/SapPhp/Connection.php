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
     * SapConnection Handle
     *
     * @var SapConnection
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
     * @param string $user     SAP Username
     * @param string $passwd   SAP passwd  
     * @param string $client   SAP Client
     * @param string $host     SAP Host
     * @param string $sysnr    SAP System NUmber
     * @param string $lang     SAP language
     *
     * @return void
     */
    public function __construct($user, $passwd, $client, $host, $sysnr = '00', $lang = 'EN')
    {
        $config = [
            'user' => $user,
            'passwd' => $passwd,
            'client' => $client,
            'ashost' => $host,
            'sysnr'  => $sysnr,
            'lang'  => $lang,
        ];


        if ($handle = self::cached($config)) {
            $this->handle = $handle;
        } else {
            $this->handle = self::connect($config);
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
     * Return the SapConnection handle.
     * 
     * @return SapConnection
     */
    public function getHandle()
    {
        return $this->handle;
    }   

    /**
     * Cache or return SapConnection handle
     *
     * @param  array  $config SAP Box
     * @param  string $user   SAP user
     * @param  mixed  $handle SapConnection handle
     *
     * @return bool|SapConenction
     */
    private static function cached(array $config, $handle = null)
    {
        $hash = sha1(implode(null, $config));

        if (isset(self::$cached[$hash])) {
            try {
                self::$cached[$hash]->ping();
            } catch (\Exception $e) {
                return false;
            }
            return self::$cached[$hash];
        } elseif ($handle !== null) {
            return self::$cached[$hash] = $handle;
        } else {
            return false;
        }
    }

    /**
     * Create a new SapConnection handle using provided authentification details.
     *
     * @param  array $config
     *
     * @return SapConnection
     */
    public static function connect(array $config)
    {
        // Connect to SAP Box.
        return self::cached(
            $config,
            new sapnwrfc($config)
        );
    }
}
