<?php

namespace SapPhp\Functions\Table;

use Closure;
use sapnwrfc;
use Carbon\Carbon;
use SapPhp\FunctionModule;

class RfcReadTable extends FunctionModule
{	
	/**
	 * sapnwrfc_function Paramters
	 * 
	 * @var array
	 */
	public $parameters = [
		'DELIMITER'   => '§',
		'QUERY_TABLE' => '',
		'FIELDS'      => [],
		'OPTIONS'     => [],
	];

	/**
	 * QueryBuilders
	 * 
	 * @var QueryBuilder
	 */
	public $query;
	
	/**
	 * Create a new instance of RfcReadTable.
	 *
	 * @param sapnwrfc $handle
	 *
	 * @return void
	 */
	public function __construct(sapnwrfc $handle)
	{
		$this->query = new QueryBuilder($this);
		$this->parser = function($result) {
			return $this->parse($result);
		};
		parent::__construct($handle, 'RFC_READ_TABLE');
	}

	/**
	 * Delimiter used by SAP to concatenate table rows
	 * 
	 * @param  string $value
	 * 
	 * @return $this
	 */
	public function delimiter($value)
	{
		return $this->param('delimiter', $value);
	}

	/**
	 * Return query fields array.
	 * 
	 * @param  array $fields
	 * 
	 * @return array
	 */
	public function fields($fields) {
		foreach ($fields as $key => $field) {
			$fields[] = ['FIELDNAME' => strtoupper($field)];
			unset($fields[$key]);
		}
		return $fields;
	}

	/**
	 * Set fields for retrieval and execute function. Keep in mind this value is limited to
	 * 512 bytes per row.
	 * 
	 * @param  array  $fields
	 * 
	 * @return Collection
	 */
	public function get($fields = [])
	{
		$this->param('fields', $this->fields($fields));
		$this->param('options', $this->query->options());
		return $this->invoke();
	}

	/**
	 * Limit table rows to provided number.
	 * 
	 * @param  int $number
	 *  
	 * @return $this
	 */
	public function limit($number)
	{
		return $this->param('rowcount', (int)$number);
	}

	/**
	 * Skip provided number of rows from the result.
	 * 
	 * @param  int $number
	 * 
	 * @return $this
	 */
	public function offset($number)
	{
		return $this->param('rowskips', (int)$number);
	}

	/**
	 * Set table to be queried.
	 * 
	 * @param  string $name
	 * 
	 * @return $this
	 */
	public function table($name)
	{
		return $this->param('query_table', strtoupper($name));
	}

	/**
	 * Parse output from SAP and transform to Collection
	 * 
	 * @param  array $result
	 * 
	 * @return Collection
	 */
	public function parse($result)
	{
		// Clear all that spaces.
		$result = array_trim($result);

		// Get DATA and FIELDS SAP tables.
		$data   = collect($result['DATA']);
		$fields = collect($result['FIELDS']);

		// Get columns.
		$columns = $fields->pluck('FIELDNAME')->toArray();
		
		// If no raw rows early exit.
		if ($data->count() === 0) {
			return collect();
		}

		// Explode raw data rows and combine with columns.
		$table = $data->pluck('WA')->transform(function($item) use ($columns) 
		{
			$values = array_trim(explode($this->parameters['DELIMITER'], $item));
			if (count($values) != count($columns)) {
				eval(\Psy\sh());
			}
			return array_combine($columns, $values);
		});

		// Apply transformations in corelation with fields type.
		$fields->each(function ($field) use ($table) {
			// Transform dates.
			if ($field['TYPE'] === 'D') {
				$table->transform(function ($row) use($field) {
					$row[$field['FIELDNAME']] = Carbon::createFromFormat('Ymd', $row[$field['FIELDNAME']]);
					return $row;
				});
			}
		});

		return $table;
	}

	/**
	 * Dynamically handle calls to object methods.
	 * 
	 * @param  string $method
	 * @param  array  $arguments
	 * 
	 * @return mixed
	 */
	public function __call($method, $arguments)
	{
		if (method_exists($this, $method)) {
			return $this->{$method}(...$arguments);
		} elseif (method_exists($this->query, $method)) {
			return $this->query->{$method}(...$arguments);
		} else {
			trigger_error("Call to undefined method ". get_class($this) ."::$method()", E_USER_ERROR);
		}
	}
}
