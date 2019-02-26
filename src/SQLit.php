<?php
/**
 * @package FloatPHP | Array Component
 * @copyright Copyright (c) 2019 Jakiboy
 * @author Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link https://jakiboy.github.io/SQL-it/
 * @license MIT
 */

namespace floatPHP\SQLit;

class SQLit extends \ArrayObject
{
	/**
	 * @access private
	 */
	private $db; // Source data array
	private $data = []; // After Where statment
	private $result = []; // After Column filter
	private $column = []; // Columns in select query
	private $where = []; // Where statment
	private $link; // Operator : like, =, >, <, contain
	private $limit = null;
	private $error = false;

	/**
	 * @access public
	 */
	public $count; // Base data count

	/**
	 * @param array $entry
	 * @return void
	 */
	public function __construct($entry = [])
	{
		if ( $this->isMultiple($entry) ) {
			$this->db = new parent($entry);
			$this->count = $this->db->count();
		}
	}

	/**
	 * Select ORM behavior
	 * 
	 * @access public
	 * @param string|array $column
	 * @return object SQLit
	 */
	public function select($column = '*') {

		if ( $column == '*' ) {
			$this->selectDefault();

		} elseif ( !is_array($column) && strpos($column,',') !== false ) {

			$column = preg_replace('/\s+/', '', $column);
			$column = explode(',', $column);
			$this->column = $column;
		} else $this->column = is_array($column) ? $column : [$column];
		
		return $this;
	}

	/**
	 * When empty select
	 * Set default columns
	 * from db entries of first array
	 *
	 * @access private
	 * @param void
	 * @return void
	 */
	private function selectDefault()
	{
		if ( isset($this->db[0]) ) {
			$this->column = array_keys($this->db[0]);
		}
	}

	/**
	 * Show error
	 *
	 * @access public
	 * @param void
	 * @return object SQLit
	 */
	public function debug()
	{
		$this->error = true;
		return $this;
	}

	/**
	 * @access public
	 * @param array $where
	 * @return object SQLit
	 */
	public function where($where = [])
	{
		if (!$this->column) $this->selectDefault();
		$this->where = $where;
		return $this;
	}

	/**
	 * @access public
	 * @param array $limit
	 * @return object SQLit
	 */
	public function limit($limit = null)
	{
		$this->limit = !is_null($limit) ? $limit : null;
		return $this;
	}

	/**
	 * @access public
	 * @param void
	 * @return object SQLit
	 * @todo
	 */
	public function distinct($column)
	{
		foreach($this->db as $k => $v) {

		    foreach($this->db as $key => $value) {

		        if($k != $key && $v[$column] == $value[$column]) {
		            unset($this->db[$k]);
		        }
		    }
		}
	}

	/**
	 * @access public
	 * @param void
	 * @return object SQLit
	 */
	public function random()
	{
		$this->db = (array)$this->db;
		shuffle($this->db);
		return $this;
	}

	/**
	 * @access public
	 * @param string $col
	 * @param null|string $order
	 * @return object SQLit
	 */
	public function order($col, $order = null)
	{
	    $new = [];
	    $sortable = [];

	    if ( count($this->db) > 0 ) {
	        foreach ( $this->db as $k => $v ) {
	            if ( is_array($v) ) {
	                foreach ($v as $k2 => $v2) {
	                    if ($k2 == $col) $sortable[$k] = $v2;
	                }
	            } else $sortable[$k] = $v;
	        }
	        switch ($order) {
	        	case 'asc':
	        		asort($sortable);
	        		break;	        	
	        	case 'desc':
	        		arsort($sortable);
	        		break;
	        	default:
	        		asort($sortable);
	        		break;
	        }

	        foreach ($sortable as $k => $v) {
	            $new[$k] = $this->db[$k];
	        }
	    }

	    $this->db = $new;
		return $this;
	}

	/**
	 * @access public
	 * @param string|null $response
	 * @return string
	 */
	public function query($response = null)
	{
		if ($response == 'json') {
			return json_encode( $this->buildQuery() );
		} else return $this->buildQuery();
	}

	/**
	 * @access protected
	 * @param void
	 * @return array|null
	 */
	protected function buildQuery()
	{
		if ( $this->isMultipleWhere($this->where) ) {

			foreach ($this->db as $key => $row) {

				if ( empty($row) ) return;

				foreach ($this->where as $where) {

					if ( empty($where) ) {
						throw new \Exception('Invalid Where statment', 3);
					}

					if ( !isset($this->db[$key][ $where['column'] ]) ) {
						throw new \Exception('Invalid column name', 5);
					}

					$source = $this->db[$key][ $where['column'] ];
					$search = $where['value'];

					// Start Operator
					switch ( strtolower( $where['link'] ) ) {
						case '=': // int
							if ( $source === $search )
							$this->data[$key] = $this->db[$key];
							break;

						case 'like': // int|string
							if ( $source == $search )
							$this->data[$key] = $this->db[$key];
							break;

						case '%=': // int|string
							if ( strcasecmp($source, $search) == 0 )
							$this->data[$key] = $this->db[$key];
							break;

						case '%like': // int|string
							if ( strcasecmp($source, $search) == 0 )
							$this->data[$key] = $this->db[$key];
							break;

						case '!=': // int|string
							if ( $source !== $search )
							$this->data[$key] = $this->db[$key];
							break;

						case '!like': // int|string
							if ( $source !== $search )
							$this->data[$key] = $this->db[$key];
							break;
							
						case 'in': // string
							if (strpos( $source, $search ) !== false)
							$this->data[$key] = $this->db[$key];
							break;

						case '%in': // string
							if (strpos( strtolower($source), strtolower($search) ) !== false)
							$this->data[$key] = $this->db[$key];
							break;
							
						case '!in': // string
							if (strpos( $source, $search ) == false)
							$this->data[$key] = $this->db[$key];
							break;
							
						case '<': // string|int
							if ( $source < $search )
							$this->data[$key] = $this->db[$key];
							break;
							
						case '<=': // string|int
							if ( $source <= $search )
							$this->data[$key] = $this->db[$key];
							break;

						case '>': // string|int
							if ( $source > $search )
							$this->data[$key] = $this->db[$key];
							break;

						case '>=': // string|int
							if ( $source >= $search )
							$this->data[$key] = $this->db[$key];
							break;

						default:
							$this->db = [];
							break;
					}
				}
			}
		} elseif ( $this->isSingleWhere($this->where) ) {

			foreach ($this->db as $key => $row) {

				foreach ($this->where as $filter => $value) {

					if ( $this->db[$key][$filter] === $value ) {
						$this->data[$key] = $this->db[$key];
					}
				}
			}
		} else $this->data = $this->db;

		// Reset database if nothing found
		if ( empty($this->data) ) {
			$this->db = [];
		}

		if ( $this->isValidColumn($this->column) ) {

			// Get database if no where statement
			$this->data = !empty($this->data) ? $this->data : $this->db;

			foreach ($this->data as $key => $row) {

				foreach ($this->column as $column) {

					if ( isset($this->data[$key][$column]) ) {
						// Set result
						$this->result[$key][$column] = $this->data[$key][$column];
						$this->result[$key] = array_filter($this->result[$key]);

					} else {

						if ($this->error) {
							throw new \Exception("Invalid column name : '{$column}'", 4);
						}
					}
				}
			}
		}

		// Apply Limit
		if ($this->limit) {
			$this->result = array_slice($this->result, 0, $this->limit);
		}

		// Reset keys
		$this->result = array_values($this->result);
		return $this->result;
	}

	/**
	 * @access private
	 * @param array $column
	 * @return boolean
	 */
	private function isValidColumn($column)
	{
	    if ($column && $column[0] != '*') return true;
	    return false;
	}

	/**
	 * @access private
	 * @param array $where
	 * @return boolean
	 */
	private function isMultipleWhere($where)
	{
	    if ( $this->isValidWhere($where) 
	    	&& $this->isMultiple($where) ) {
	    	return true;
	    }
	    return false;
	}

	/**
	 * @access private
	 * @param array $where
	 * @return boolean
	 */
	private function isSingleWhere($where)
	{
	    if ( $this->isValidWhere($where) 
	    	&& !$this->isMultiple($where) ) {
	    	return true;
	    }
	    return false;
	}

	/**
	 * @access private
	 * @param array $where
	 * @return boolean
	 */
	private function isValidWhere($where)
	{
	    if ( $where && is_array($where) ) return true;
	    return false;
	}

	/**
	 * @access private
	 * @param array $data
	 * @return boolean
	 */
	private function isMultiple($data)
	{
		if ( $this->depth($data) > 2 ) {
			throw new \Exception('Invalid 2D Array', 1);
		}
	    $data = array_filter($data,'is_array');
	    if( count($data) > 0 ) {
	    	return true;
	    }
	    else return false;
	}

	/**
	 * @access private
	 * @param array $array
	 * @return int
	 */
	private function depth($array)
	{
		if ( !is_array($array) ) {
			throw new \Exception('No Array found', 2);
		}
	    $max = 1;
	    foreach ($array as $value) {
	        if ( is_array($value) ) {
	            $depth = $this->depth($value) + 1;
	            if ($depth > $max) {
	                $max = $depth;
	            }
	        }
	    }
	    return $max;
	}
}
