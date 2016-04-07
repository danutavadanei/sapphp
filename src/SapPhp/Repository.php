<?php

namespace SapPhp;

use Sabre\Xml\Service;
use SapPhp\Exceptions\DataNotFoundException;
use SapPhp\Exceptions\BoxNotFoundException;

class Repository
{


    /**
     * Collection of SAP Boxes
     *
     * @var \Illuminate\Support\Collection
     */
    public static $repository;

    /**
     * Retrieve details about provided box.
     *
     * @param  string $box SAP Box Shortname
     *
     * @return \Illuminate\Support\Collection
     *
     * @throws \SapPhp\Exceptions\BoxNotFoundException
     */
    public static function get($box, $user = null, $passwd = null, $client = null)
    {
        if (is_null(self::$repository)) {
            self::generate();
        }

        if (null === $collection = self::where('name', strtoupper($box))->first()) {
            throw new BoxNotFoundException($box);
        }

        if ($user) {
            $collection['user'] = $user;
            $collection['passwd'] = $passwd;
            $collection['client'] = $client;
        }

        return collect($collection);
    }

    /**
     * Generate repository Collection
     *
     * @return bool
     *
     * @throws \SapPhp\Exceptions\DataNotFoundException
     */
    private static function generate()
    {
        if (true === $iniPath = self::parseIniFile()) {
            return true;
        }

        #if (true === $xmlPath = self::parseXmlFile()) {
        #   return true;
        #}

        throw new DataNotFoundException($iniPath, $xmlPath);
    }

    /**
     * Parse saplogon.ini file.
     *
     * @return bool|string Boolean if not found/no data
     *                     File path if found.
     */
    private static function parseIniFile()
    {
        $map = [
            'Database'    => 'sysnr',
            'MSSysName' => 'name',
            'MSSrvName' => 'ashost',
        ];

        $path = $_SERVER['APPDATA'] . '\SAP\Common\saplogon.ini';

        if (!file_exists($path)) {
            return $path;
        }

        $group = '/\[(.*)\]/';
        $item  = '/Item([0-9]*)=(.*)/';

        $handle = fopen($path, 'r');

        $data  = [];
        $index = null;

        while (($line = fgets($handle)) !== false) {
            if (preg_match_all($group, $line, $matches) === 1) {
                $index = trim($matches[1][0]);
                if ($index == 'SCM 5.0 DP Prod') {
                    $index = 'Description0';
                }
            } elseif (preg_match_all($item, $line, $matches) === 1) {
                $value = trim($matches[2][0]);
                $key   = trim($matches[1][0]);

                if (!isset($data[$key]['lang'])) {
                    $data[$key]['lang'] = 'EN';
                }

                if (in_array($index, array_keys($map))) {
                    $data[$key][$map[$index]] = $value;
                } elseif (in_array($index, ['Description0', 'Description'])) {
                    if (isset($data[$key]['description'])) {
                        if ($data[$key]['description'] === '') {
                            $data[$key]['description'] = $value;
                        } else {
                            continue;
                        }
                    }
                    $data[$key]['description'] = $value;
                } elseif (in_array($index, ['Address', 'MSSrvName'])) {
                    if (isset($data[$key]['ashost'])) {
                        if ($data[$key]['ashost'] === '') {
                            $data[$key]['ashost'] = $value;
                        } else {
                            continue;
                        }
                    }
                    $data[$key]['ashost'] = $value;
                }
            }
        }

        $repository = collect($data)->filter(function ($item) {
            return data_get($item, 'name') !== '' && data_get($item, 'ashost');
        });

        if ($repository->count() === 0) {
            return $path;
        }

        self::$repository = $repository;

        return true;
    }

    /**
     * Parse sapphp.xml file.
     *
     * @return bool|string Boolean if not found/no data
     *                     File path if found.
     */
    private static function parseXmlFile()
    {
        $path = realpath(implode(DIRECTORY_SEPARATOR, [ __DIR__ , '..', '..', ''])) . DIRECTORY_SEPARATOR . 'sapphp.xml';

        if (!file_exists($path)) {
            return $path;
        }

        $xml = file_get_contents($path);

        $service = new Service();

        $service->elementMap = [
            '{}sapphp' => function ($reader) {
                return \Sabre\Xml\Deserializer\keyValue($reader, '');
            },
            '{}box' => function ($reader) {
                return \Sabre\Xml\Deserializer\keyValue($reader, '');
            }
        ];

        $repository = collect($service->parse($xml)['boxes'])->pluck('value');

        if ($repository->count() === 0) {
            return $path;
        }

        self::$repository = $repository;

        return true;
    }


    /**
     * Dynamically handle method calls to static object.
     *
     * @param  string $method
     * @param  mixed  $arguments
     *
     * @return mixed
     */
    public static function __callStatic($method, $arguments)
    {
        if (method_exists(self::class, $method)) {
            return self::{$method}(...$arguments);
        } elseif (method_exists(self::$repository, $method)) {
            return self::$repository->{$method}(...$arguments);
        } else {
            trigger_error("Call to undefined method ". self::class ."::$method()", E_USER_ERROR);
        }
    }
}
