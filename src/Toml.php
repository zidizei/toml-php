<?php

class Toml {

    private $in;
    private $out;

    private $cursor;
    private $key;

    private $row = 1;
    private $col  = 0;

    private $lastParsedType = null;
    const String   = 6;
    const Integer  = 1;
    const Float    = 2;
    const Datetime = 3;
    const Table    = 4;
    const Boolean  = 5;

    private $lineEnd = false;

    private $tables      = array();
    private $tableArrays = array();

    public static function parse ($input)
    {
        $p = new self($input);

        return $p->out;
    }

    public static function parseFile ($input)
    {
        if (is_file($input) && is_readable($input)) {
            $input = file_get_contents($input);
        } else {
            throw new \InvalidArgumentException("Could not open TOML file '".$input."'.");
        }

        return self::parse($input);
    }

    private function __construct ($input)
    {
        // Splitting at the last \n before the next '=', '[' or # 
        $this->in = preg_split('/\r\n|\r|\n(?=\s*[a-zA-Z0-9_?]+\s*=|\s*\[|\n|#.*)/s', $input);
        $this->cursor = &$this->out;

        foreach ($this->in as &$row)
        {
        	$this->lineEnd        = false;
        	$this->lastParsedType = null;

        	$line = trim($row);

	    	echo $this->row." : ".$line."\n";

            $this->parseLine($line."\n");
   			$this->parseEnd($line);

            $this->row += (1 + substr_count($row, "\n"));
            $this->col = 0;
        }
    }


    private function parseLine ($line)
    {
    	$c = $line[$this->col];

    	switch ($c) {
    		case "\n":
		    	# leave current table when encountering empty line
    			$this->cursor = &$this->out;

    		case "#":
    			# don't do anything if it's a comment line
    			$this->lineEnd = true;
    			return;

    		case "[":
    			# it's a table.
    			if ($line[($this->col+1)] == '[')
    			{
    				# table array
    				$this->createTableArray($line);
    			}
    			else
    			{
    				# regular table
    				$this->createTable($line);
    			}

    			break;
    		
    		default:
    			# it's a key.
				if (preg_match('/^[a-zA-Z0-9_?]+/', $line))
				{
					$this->createKey($line);
				}

    			break;
    	}

    }

    private function parseEnd ($line)
    {
    	if (!$this->lineEnd) throw new \Exception("Unkown TOML parsing error on line ".$this->row.", column ".$this->col.".");
    	if (empty($line)) return;

    	$lastNewline = 0;

	   	while ($this->col < strlen($line))
  	 	{
  	 		$c = $line[$this->col];

  	 		if ($c == '#') {
  	 			return;
  	 		} else if ($c == "\n") {
  	 			$this->row++;
  	 			$lastNewline = $this->col+1;
  	 		} else if ($c != ' ' && $c != "\t") {
  	 			throw new \UnexpectedValueException("Invalid TOML syntax '".substr($line, $lastNewline)."' on line ".$this->row.".");
  	 		}

  	 		$this->col++;
    	}    	
    }


    private function createKey ($line)
    {
    	$key = null;

	   	while ($this->col < strlen($line))
  	 	{
	    	$c = $line[$this->col];

  	 		if ($c == '=') break;

    		$key .= $c;

	    	$this->col++;
    	}

    	$key = trim($key);

    	if (isset($this->cursor[$key])) throw new \Exception("Invalid TOML syntax on line ".$this->row.". Key '".$key."' already exists.");

    	$this->cursor[$key] = null;
    	$this->key          = $key;

    	$this->parseValue($line);
    }

    private function parseValue ($line)
    {
    	if ($line[$this->col] == '=')
    	{
    		# skip whitespace
    		while (($c = $line[++$this->col]) == ' ');

    		switch ($c) {
    			case '"':
    				# it's a string
    				$this->cursor[$this->key] = $this->parseString($line);
    				break;

    			case '[':
    				# it's an array
    				$this->cursor[$this->key] = $this->parseArray($line);
    				break;
    			
    			default:
    				# it's some other primitive data
    				$this->cursor[$this->key] = $this->parseData($line);
    				break;
    		}
    	}
    	else
    	{
			throw new \Exception("Invalid TOML syntax on line ".$this->row.".");
    	}
    }


    private function parseString ($line)
    {
    	$string = null;

    	while (++$this->col < strlen($line))
    	{
    		$c = $line[$this->col];

			if ($c == '\\')
			{
		    	$c = $line[++$this->col];

				if ($c == 'b') {
					$string .= mb_convert_encoding(pack('H*', '0008'), 'UTF-8', 'UCS-2BE');
				} else if ($c == 't') {
					$string .= mb_convert_encoding(pack('H*', '0009'), 'UTF-8', 'UCS-2BE');    			
				} else if ($c == 'n') {
					$string .= mb_convert_encoding(pack('H*', '000A'), 'UTF-8', 'UCS-2BE');
				} else if ($c == 'f') {
					$string .= mb_convert_encoding(pack('H*', '000C'), 'UTF-8', 'UCS-2BE');
				} else if ($c == 'r') {
					$string .= mb_convert_encoding(pack('H*', '000D'), 'UTF-8', 'UCS-2BE');
				} else if ($c == '"') {
					$string .= mb_convert_encoding(pack('H*', '0022'), 'UTF-8', 'UCS-2BE');
				} else if ($c == '/') {
					$string .= mb_convert_encoding(pack('H*', '002F'), 'UTF-8', 'UCS-2BE');
				} else if ($c == '\\') {
					$string .= mb_convert_encoding(pack('H*', '005C'), 'UTF-8', 'UCS-2BE');
				} else if ($c == 'u')
				{
			    	$code = null;

			    	for ($j=$this->col; $j<($this->col+5); $j++)
			    	{
			    		$code .= $line[$j];
			    	}

			    	# move cursor ahead in current line
			    	$this->col = $j-1;

			    	if (preg_match('/\\\\u([0-9a-f]{4})/i', '\\'.$code, $match)) {
						$string .= mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
					} else {
						throw new \Exception("Unrecognized Unicode sequence '$code' on line ".$this->currentLine." near column ".($this->currentCol+1).".");
					}
				}
			}
			else if ($c == '"')
			{
				$this->col++;

				$this->lineEnd        = true;
				$this->lastParsedType = Toml::String;

				return $string;
			}
			else
			{
				$string .= $c;
			}
    	}
    }


    private function parseArray ($line, $lvl=0)
    {
    	$array = array();

    	$end = $lvl;
		$lvl++;
		$this->col++;

		$prevType = null;
    	$comment  = false;

		while ($lvl > $end && $this->col < strlen($line))
		{
			$c = $line[$this->col];

			if ($comment && $c != "\n") {
				$this->col++;
				continue; 
			}

			if ($this->lineEnd && $c != ',' && $c != ' ' && $c != "\n" && $c != ']') throw new \Exception("Invalid TOML syntax. Could not recognize array format on line ".$this->row.", column ".$this->col.".");

       		switch ($c) {
    			case '"':
    				# it's a string
    				$array[] = $this->parseString($line);
    				$this->col--;
    				break;

    			case '[':
    				# it's an array
	   				$array[] = $this->parseArray($line, $lvl);
    				break;

    			case ']':
    				$lvl--;
    				break;

    			case ',':
    				$this->lineEnd = false;
    				break;

    			case "\n":
    				if ($comment) $comment = false;
    			case "\t":
    			case " ":
    				break;

    			case '#':
    				$comment = true;
    				break;

    			default:
    				# it's some other primitive data
    				$array[] = $this->parseData($line, array(',', ' ', ']'));
    				$this->col--;
    				break;
    		}

    		if ($prevType == null) {
				$prevType = $this->lastParsedType;
    		} else if ($prevType != null && $prevType != $this->lastParsedType) {
    			throw new \UnexpectedValueException("Mixing data types in an array is stupid.\n".var_export($array, true)." on line ".$this->row.".");
    		}

			$this->col++;
    	}


    	if ($lvl != $end) throw new \Exception("Invalid TOML syntax. Could not recognize array format on line ".$this->row.". Did you forget some closing brackets?");

    	if ($lvl == 0) {
    		$this->lineEnd = true;
		} else {
			$this->lineEnd = false;
		}

		$this->lastParsedType = Toml::Table;

    	return $array;
    }


    private function parseData ($line, $lineEndMarker=array(' '))
    {
    	$data  = null;
		$c = $line[$this->col];

		while (in_array($c, $lineEndMarker) === false)
		{
			if ($c == "\n") break;
	    	$data .= $c;

			$c = $line[++$this->col];
		}

        if ($data === "") throw new \UnexpectedValueException("Invalid TOML syntax. Empty key on line ".$this->row.".");

        $this->lineEnd = true;

        # parse bools
        if ($data === 'true' || $data === 'false') {
			$this->lastParsedType = Toml::Boolean;
            return $data === 'true';
        }

        # parse floats
        if (preg_match('/^\-?\d*?\.\d+$/', $data)) {
			$this->lastParsedType = Toml::Float;
            return (float) $data;
        }

        # parse integers
        if (preg_match('/^\-?\d*?$/', $data)) {
			$this->lastParsedType = Toml::Integer;
            return (int) $data;
        }

        # parse datetime
        if (strtotime($data)) {
			$this->lastParsedType = Toml::Datetime;
            return new \Datetime($data);
        }

        $this->lineEnd = false;

		throw new \Exception("Unrecognized data type '$data' on line ".$this->row." near column ".($this->col+1).".");		
    }


    private function createTable ($line)
    {
    	$table = &$this->out;
    	$name  = null;
    	$tableid = null;

	   	while ($this->col < strlen($line))
  	 	{
	    	$c = $line[$this->col];

    		if ($c == '.') {
    			$tableid .= $name.'.';

		    	if (isset($table[$name]) && !is_array($table[$name])) throw new \Exception("TOML parsing error on line ".$this->row.". Table [".$tableid."] already defined as key.");
    	
    			$table = &$table[$name];
    			$name = '';
    		} else if ($c == ']') {
    			$this->lineEnd = true;
    			break;
    		} else if ($c != '[') {
	    		$name .= $c;
	    	}

	    	$this->col++;
    	}

    	$tableid .= $name;

    	if (isset($this->tables[$tableid])) throw new \Exception("TOML parsing error on line ".$this->row.". Table [".$tableid."] already defined on line ".$this->tables[$tableid].".");
    	if (isset($table[$name]) && !is_array($table[$name])) throw new \Exception("TOML parsing error on line ".$this->row.". Table [".$tableid."] already defined as key.");

		$table                  = &$table[$name];
		$this->cursor           = &$table;
		$this->tables[$tableid] = $this->row;

    	$this->col++;
    }

    private function createTableArray ($line)
    {
    	if (preg_match('/^\[\[([^\s]+\])\].*$/', $line, $match))
    	{
    		$line = $match[1];
	    	$table = &$this->out;
	    	$name  = null;
	    	$tableid = null;

		   	while ($this->col < strlen($line))
	  	 	{
		    	$c = $line[$this->col++];

	    		if ($c == '.') {
	    			$tableid .= $name.'.';

			    	if (isset($table[$name]) && !is_array($table[$name])) throw new \Exception("TOML parsing error on line ".$this->row.". Table [".$tableid."] already defined as key.");

			    	if (isset($this->tableArrays[$name])) {
			    		$table = &$this->tableArrays[$name];
			    	} else {
		    			$table = &$table[$name];
		    		}

	    			$name = '';
	    		} else if ($c == ']') {
	    			$this->lineEnd = true;
	    			break;
	    		} else {
		    		$name .= $c;
		    	}
	    	}

	    	$tableid .= $name;

			$table                       = &$table[$name];
			$this->cursor                = &$table[];

			$this->tables[$tableid]      = $this->row;
			$this->tableArrays[$tableid] = &$this->cursor;

	    	$this->col += 4; // +4 because we need to skip the two opening [[ und closing ]] brackets
	    }
    	else
    	{
    		throw new \Exception("Invalid TOML syntax. Could not recognize table format on line ".$this->row.".");
    	}
    }
	
	private function __clone() {}
}
