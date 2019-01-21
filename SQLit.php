<?php

class SQLit extends ArrayObject
{
	/**
	 * @access private
	 */
	private $db; // Original data
	private $data = []; // After Where statment
	private $result = []; // After Column filter
	private $column = []; // Columns for select
	private $where = []; // Where statment
	private $link; // operator : like, =, >, <, contain
	private $limit = null;

	/**
	 * @access public
	 */
	public $count; // Original data count

	/**
	 * @param array $entry
	 * @return void
	 */
	public function __construct($entry = [])
	{
		if ( $this->isMultiple($entry) )
		{
			$this->db = $entry;
			$this->count = count($this->db);
		}
	}

	/**
	 * @access public
	 * @param string|array $col
	 * @return object SQLit
	 */
	public function select($col = '*')
	{
		if ($col == '*') $this->selectDefault();
		else $this->column = is_array($col) ? $col : [$col];
		return $this;
	}

	/**
	 * @access private
	 * @param string|array $col
	 * @return object SQLit
	 */
	private function selectDefault()
	{
		if ( isset($this->db[0]) )
		{
			$this->column = array_keys($this->db[0]);
		}
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
	public function distinct($col = '*')
	{
		// ...
	}

	/**
	 * @access public
	 * @param void
	 * @return object SQLit
	 */
	public function random()
	{
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

	    if ( count($this->db) > 0 )
	    {
	        foreach ( $this->db as $k => $v )
	        {
	            if ( is_array($v) )
	            {
	                foreach ($v as $k2 => $v2)
	                {
	                    if ($k2 == $col) $sortable[$k] = $v2;
	                }
	            } 
	            else $sortable[$k] = $v;
	        }
	        switch ($order) 
	        {
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

	        foreach ($sortable as $k => $v)
	        {
	            $new[$k] = $this->db[$k];
	        }
	    }

	    $this->db = $new;
		return $this;
	}

	/**
	 * @access public
	 * @param string|null $response
	 * @return array|null
	 */
	public function query($response = null)
	{
		if ($response == 'json')
		{
			return json_encode( $this->buildQuery() );
		}
		else return $this->buildQuery();
	}

	/**
	 * @access protected
	 * @param void
	 * @return array|null
	 */
	protected function buildQuery()
	{
		if ( $this->isMultipleWhere($this->where) )
		{
			foreach ($this->db as $key => $row)
			{
				foreach ($this->where as $where)
				{
					$source = $this->db[$key][ $where['column'] ];
					$search = $where['value'];

					// Start Operator
					switch ( strtolower( $where['link'] ) )
					{
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
		}
		elseif ( $this->isSingleWhere($this->where) )
		{
			foreach ($this->db as $key => $row)
			{
				foreach ($this->where as $filter => $value)
				{
					if ( $this->db[$key][$filter] === $value )
					{
						$this->data[$key] = $this->db[$key];
					}
				}
			}
		}
		else $this->data = $this->db;

		// Reset database if nothing found
		if ( empty($this->data) )
		{
			$this->db = [];
		}

		if ( $this->isValidColumn($this->column) )
		{
			// Get database if no where statement
			$this->data = !empty($this->data) ? $this->data : $this->db;

			foreach ($this->data as $key => $row)
			{
				foreach ($this->column as $column)
				{
					if ( isset($this->data[$key][$column]) )
					{
						// Set result
						$this->result[$key][$column] = $this->data[$key][$column];
						$this->result[$key] = array_filter($this->result[$key]);
					}
				}
			}
		}

		// Apply Limit
		if ($this->limit)
		{
			$this->result = array_slice($this->result, 0, $this->limit);
		}

		// Reset keys
		return array_values($this->result);
	}

	/**
	 * @access private
	 * @param array $col
	 * @return boolean
	 */
	private function isValidColumn($col)
	{
	    if ($col && $col[0] != '*') return true;
	    return false;
	}

	/**
	 * @access private
	 * @param array $where
	 * @return boolean
	 */
	private function isMultipleWhere($where)
	{
	    if ( $this->isValidWhere($where) && $this->isMultiple($where) )
	    {
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
	    if ( $this->isValidWhere($where) && !$this->isMultiple($where) )
	    {
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
	 * @param array $array
	 * @return boolean
	 */
	private function isMultiple($array)
	{
	    $r = array_filter($array,'is_array');
	    if( count($r) > 0 ) return true;
	    return false;
	}
}
