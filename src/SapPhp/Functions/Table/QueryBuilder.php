






<?php

namespace SapPhp\Functions\Table;

use Closure;

class QueryBuilder
{
	/**
	 * Query "wheres" conditions.
	 * 
	 * @var array
	 */
	public $wheres   = [];

	/**
	 * Current "where" index.
	 * 
	 * @var int
	 */
	private $index = 0;

	/**
	 * Create a new instance of Querybuilder
	 * 
	 * @param RfcReadTable $parent
	 *
	 * @return void
	 */
	public function __construct(RfcReadTable $parent = null)
	{
		$this->parent = $parent;
	}

	/**
     * Add an "and where" clause to the query.
     *
     * @param  string $column
     * @param  string $operator
     * @param  mixed  $value
     * 
     * @return $this
     */
	public function andWhere($column, $operator = null, $value = null)
	{
		return $this->where($column, $operator, $value, 'and');
	}

	/**
     * Add an "or where" clause to the query.
     *
     * @param  string $column
     * @param  string $operator
     * @param  mixed  $value
     * 
     * @return $this
     */
	public function orWhere($column, $operator = null, $value = null)
	{
		return $this->where($column, $operator, $value, 'or');
	}

	/**
     * Add a basic dynamic clause to the query.
     *
     * @param  string|Closure  $column
     * @param  string  		   $operator
     * @param  mixed           $value
     * @param  string          $boolean
     * 
     * @return $this
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and')
	{	
		// If $column is a Closure we open a group of wheres and close
		// after performing callback.
		if ($column instanceof Closure) {

			// Store group boolean.
			$this->wheres[$this->index]['boolean'] = $boolean;

			// Create new query builder instance.
			$query = new self;

			// Perform callback.
			$column($query);

			// Store "wheres".
			$this->wheres[$this->index]['conditions'] = $query->wheres;

			$this->index++;
			return $this;
		}

		// If value is null, we assume that value is in the operator argument
		// and we want the default opertor (=).
		if (is_null($value)) {
			$value = $operator;
			$operator = '=';
		}

		// If value is an array, simulate where in sql clause.
		if (is_array($value)) {
			return $this->{$boolean . 'Where'}(function($query) use ($column, $value, $operator){
				foreach ($value as $entry) {
					$query->orWhere($column, $operator, $entry);
				}
			});
		}

		// Store "where".
		$this->wheres[$this->index]['boolean'] = $boolean; 
		$this->wheres[$this->index]['conditions'] = strtoupper($column) . " $operator '$value'";

		$this->index++;
		return $this;
	}

	/**
	 * Return query options array
	 *
	 * @param array $wheres
	 * 
	 * @return array
	 */
	public function options($wheres = null)
	{	
		if (is_null($wheres)) {
			$wheres = $this->wheres;
		}

		$options = [];

		$size = count($wheres);

		for ($i = 0; $i < $size; $i++) {

			if ($i !== 0) {
				$options[] = ['TEXT' => strtoupper($wheres[$i]['boolean'])];
			} 

			if (is_array($wheres[$i]['conditions'])) {
				$options[] = ['TEXT' => '('];
				$options = array_merge($options, $this->options($wheres[$i]['conditions']));
				$options[] = ['TEXT' => ')'];
			} else {
				$options[] = ['TEXT' => $wheres[$i]['conditions']];
			}
		}

		return $options;
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
		} elseif (method_exists($this->parent, $method)) {
			return $this->parent->{$method}(...$arguments);
		} else {
			trigger_error("Call to undefined method ". get_class($this) ."::$method()", E_USER_ERROR);
		}
	}
}
