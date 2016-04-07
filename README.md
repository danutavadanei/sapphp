# SapPhp package
> SAP Remote Function Modules Calls made easy using sapnwrfc and PHP.

[![Latest Stable Version](https://poser.pugx.org/avadaneidanut/sapphp/v/stable)](https://packagist.org/packages/avadaneidanut/sapphp)
[![Total Downloads](https://poser.pugx.org/avadaneidanut/sapphp/downloads)](https://packagist.org/packages/avadaneidanut/sapphp)
[![Latest Unstable Version](https://poser.pugx.org/avadaneidanut/sapphp/v/unstable)](https://packagist.org/packages/avadaneidanut/sapphp)
[![License](https://poser.pugx.org/avadaneidanut/sapphp/license)](https://packagist.org/packages/avadaneidanut/sapphp)

## Summary

Welcome to SapPhp package. This packages is not a connector, it uses [php-sapnwrfc](https://github.com/piersharding/php-sapnwrfc) extension to handle client - server communication. This package is intended to provide a clean object oriented interface to handle extensive data extraction using RFC calls using PHP. My plan is to extend this class with PHP Interfaces to SAP FMs (check [RfcReadTable](src/SapPhp/Functions/RfcReadTable.php) interface) 

This is an early version and I expect you to raise issues and bugs and maybe give me some suggestions.

## Install

Make sure you have the [php-sapnwrfc](https://github.com/piersharding/php-sapnwrfc) extension installed and run:
```
composer require avdaneidanut/sapphp
```

## SAP Systems details

The package uses two methods for retrievieng SAP Systems details (ashost, sysnr, description and name) by parsing files using the \SapPhp\Repository class.

1. Parsing saplogon.ini file from: ``C:/Users/{currentUser}/AppData/Roaming/SAP/Common/``. 

2. Parsing sapphp.xml from package root folder.

If the first method fails or returns no result the second method will be performed.

## Connecting to SAP

```php
<?php

use SapPhp\Connection;
use SapPhp\Exceptions\BoxNotFoundException;

try {
	$connection = new Connection(
		'box', // SAP Box Name
		'user', // SAP Username
		'passwd', // SAP Password
		'500' // SAP Client Code
	);
} catch(sapnwrfcConnectionException $ex) {
	// Do something if login failed.
} catch(BoxNotFoundException $ex) {
	// Do something if box doesn't exist.
}
```

## Perform Function Module call

Let's get details about an user:

```php
<?php

// ... connection

// Instanciate new Function Module interface.
$function = $connection->fm(
	'BAPI_USER_GET_DETAIL', // RFC Enable FM
	true // Parse result (trim all strings and decode GUIDs)
);

// Get function description.
print_r($function->description());

// Add import parameter.
// Will trigger an \SapPhp\Exceptions\ParamNotFoundException if param is not found in function description.
$function->param('USERNAME', 'USER');

// Perform function call and retrieve result.
$result = $function->invoke();
```

How about getting details about an user using `RFC_READ_TABLE` function? Let's go:

```php
<?php

// ... connection

$function = $connection->fm('RFC_READ_TABLE');

$function->param('QUERY_TABLE', 'USR01')
	->param('OPTIONS', [
		['TEXT' => 'BNAME = \'USER\' OR BNAME = \'USER2\' OR BNAME LIKE \'USER5*\'']
	])
	->param('ROWCOUNT', 5)
	->param('DELIMITER', '~')
;

$result = $function->invoke();
```

Very nice, we can query a table using a SQL statement :O. But with the result, we need to parse it, ugh..
```
[
  "DATA" => [
    [
      "WA" => "500~USER2      ~                    ~    ~ ~H~K~1~ ~        ~   ~            ~ ~                    ~                    ~ ~ ~0",
    ],
    [
      "WA" => "500~USER5      ~                    ~    ~ ~H~K~1~ ~        ~   ~            ~ ~                    ~                    ~ ~ ~0",
    ],
    [
      "WA" => "500~USER55      ~                    ~    ~ ~H~K~1~ ~        ~   ~            ~ ~                    ~                    ~ ~ ~0",
    ],
  ],
  "FIELDS" => [
    [
      "FIELDNAME" => "MANDT",
      "OFFSET" => "000000",
      "LENGTH" => "000003",
      "TYPE" => "C",
      "FIELDTEXT" => "Client",
    ],
    [
      "FIELDNAME" => "BNAME",
      "OFFSET" => "000004",
      "LENGTH" => "000012",
      "TYPE" => "C",
      "FIELDTEXT" => "User Name in User Master Record",
    ],
    [
      "FIELDNAME" => "STCOD",
      "OFFSET" => "000017",
      "LENGTH" => "000020",
      "TYPE" => "C",
      "FIELDTEXT" => "Start menu (old, replaced by XUSTART)",
    ],
    [
      "FIELDNAME" => "SPLD",
      "OFFSET" => "000038",
      "LENGTH" => "000004",
      "TYPE" => "C",
      "FIELDTEXT" => "Spool: Output device",
    ],
    [
      "FIELDNAME" => "SPLG",
      "OFFSET" => "000043",
      "LENGTH" => "000001",
      "TYPE" => "C",
      "FIELDTEXT" => "Print parameter 1",
    ],
    [
      "FIELDNAME" => "SPDB",
      "OFFSET" => "000045",
      "LENGTH" => "000001",
      "TYPE" => "C",
      "FIELDTEXT" => "Print parameter 2",
    ],
    [
      "FIELDNAME" => "SPDA",
      "OFFSET" => "000047",
      "LENGTH" => "000001",
      "TYPE" => "C",
      "FIELDTEXT" => "Print parameter 3",
    ],
    [
      "FIELDNAME" => "DATFM",
      "OFFSET" => "000049",
      "LENGTH" => "000001",
      "TYPE" => "C",
      "FIELDTEXT" => "Date format",
    ],
    [
      "FIELDNAME" => "DCPFM",
      "OFFSET" => "000051",
      "LENGTH" => "000001",
      "TYPE" => "C",
      "FIELDTEXT" => "Decimal notation",
    ],
    [
      "FIELDNAME" => "HDEST",
      "OFFSET" => "000053",
      "LENGTH" => "000008",
      "TYPE" => "C",
      "FIELDTEXT" => "Host destination",
    ],
    [
      "FIELDNAME" => "HMAND",
      "OFFSET" => "000062",
      "LENGTH" => "000003",
      "TYPE" => "C",
      "FIELDTEXT" => "Default host client",
    ],
    [
      "FIELDNAME" => "HNAME",
      "OFFSET" => "000066",
      "LENGTH" => "000012",
      "TYPE" => "C",
      "FIELDTEXT" => "Default host user name",
    ],
    [
      "FIELDNAME" => "MENON",
      "OFFSET" => "000079",
      "LENGTH" => "000001",
      "TYPE" => "C",
      "FIELDTEXT" => "Automatic Start",
    ],
    [
      "FIELDNAME" => "MENUE",
      "OFFSET" => "000081",
      "LENGTH" => "000020",
      "TYPE" => "C",
      "FIELDTEXT" => "Menu name",
    ],
    [
      "FIELDNAME" => "STRTT",
      "OFFSET" => "000102",
      "LENGTH" => "000020",
      "TYPE" => "C",
      "FIELDTEXT" => "Start menu (old, replaced by XUSTART)",
    ],
    [
      "FIELDNAME" => "LANGU",
      "OFFSET" => "000123",
      "LENGTH" => "000001",
      "TYPE" => "C",
      "FIELDTEXT" => "Language",
    ],
    [
      "FIELDNAME" => "CATTKENNZ",
      "OFFSET" => "000125",
      "LENGTH" => "000001",
      "TYPE" => "C",
      "FIELDTEXT" => "CATT: Test status",
    ],
    [
      "FIELDNAME" => "TIMEFM",
      "OFFSET" => "000127",
      "LENGTH" => "000001",
      "TYPE" => "C",
      "FIELDTEXT" => "Time Format (12-/24-Hour Specification)",
    ],
  ],
  "OPTIONS" => [
    [
      "TEXT" => "TEXT' => 'BNAME = 'USER' OR BNAME = 'USER2' OR BNAME LIKE 'USER5*'",
    ],
  ],
]
```

But wait, how about using a FunctionModule interface that has a query builder and parses the result?
```php
<?php

// ... connection

// fm method will check if RfcReadTable is an FunctionModule Interface Class, if so will return a new instance.
$function = $connection->fm('RfcReadTable'); 

// Lets do the same thing as before.
$result = $function->table('usr01') // set the query table
	->where('bname', ['USER', 'USER5']) // add multiple where clause (simulating where in )
	->orWhere('bname', 'LIKE', 'USER5*') // add custom comparation operator
	->limit(5) // limit the result to 5
	->get() // perform function call, parse the result and return a Illuminate\Support\Collection object.
;

print_r($result->toArray());
```

And the result

```
[
	[
		"MANDT" => "500",
		"BNAME" => "USER2",
		"STCOD" => "",
		"SPLD" => "",
		"SPLG" => "",
		"SPDB" => "H",
		"SPDA" => "K",
		"DATFM" => "1",
		"DCPFM" => "",
		"HDEST" => "",
		"HMAND" => "",
		"HNAME" => "",
		"MENON" => "",
		"MENUE" => "",
		"STRTT" => "",
		"LANGU" => "",
		"CATTKENNZ" => "",
		"TIMEFM" => "0",
	],
	[
		"MANDT" => "500",
		"BNAME" => "USER5",
		"STCOD" => "",
		"SPLD" => "",
		"SPLG" => "",
		"SPDB" => "H",
		"SPDA" => "K",
		"DATFM" => "1",
		"DCPFM" => "",
		"HDEST" => "",
		"HMAND" => "",
		"HNAME" => "",
		"MENON" => "",
		"MENUE" => "",
		"STRTT" => "",
		"LANGU" => "",
		"CATTKENNZ" => "",
		"TIMEFM" => "0",
	],
	[
		"MANDT" => "500",
		"BNAME" => "USER55",
		"STCOD" => "",
		"SPLD" => "",
		"SPLG" => "",
		"SPDB" => "H",
		"SPDA" => "K",
		"DATFM" => "1",
		"DCPFM" => "",
		"HDEST" => "",
		"HMAND" => "",
		"HNAME" => "",
		"MENON" => "",
		"MENUE" => "",
		"STRTT" => "",
		"LANGU" => "",
		"CATTKENNZ" => "",
		"TIMEFM" => "0",
	],
]
```

Take a look at [RfcReadTable](src/SapPhp/Functions/Table/RfcReadTable.php) and [QueryBuilder](src/SapPhp/Functions/Table/QueryBuilder.php) available methods.


### QueryBuilder usage.
```php
<?php

$query->where('column', 'value') // Add WHERE clause
	->andWhere('column2,' 'value2') // AND logical operarator
	->orWhere('column3', '<>', 'value3') // OR logical operator
	->orWhere(function ($query) { // WJERE group
		$query->where('column11', 'value11')
			->andWhere('column22', 'value22');
	})
	->orWhere('column5', '<>', [1, 2, 3, 4]); // Simulate WHERE IN clause

```
The previous code will generate the folowing SQL query:

```sql
	WHERE
		COLUMN = 'value' 
		AND
		COLUMN2 = 'value2'
		OR
		COLUMN3 <> 'value3'
		OR
		(
			COLUMN11 = 'value11'
			AND
			COLUMN22 = 'value22'
		)
		OR 
		(
			COLUMN5 <> '1'
			OR
			COLUMN5 <> '2'
			OR
			COLUMN5 <> '3'
			OR
			COLUMN5 <> '4'
		)
```

## To-do
1. Aggregate multiple table results in one Collection and share the same query over multiple tables.

```php
<?php

// ... connection & function

$rfcReadTable->table(['table1', 'table2', 'table3'], function($agregatter) {
	$aggregate->table('table1')
		->with('table2')
		->on('column')
		->as('aggregated_table');
	$aggregate->table('aggregated_table', 'table3')
		->on('column2');
})->where('column', 'value');
```

2. Add mode FunctionModule interfaces as RfcReadTable - Please send suggestions!

## Support

I will help you ASAP if you find any issues in using this package.