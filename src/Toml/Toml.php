<?php
namespace Toml;

class Toml
{
    private $in;
    private $out;

    private $prevType = null;
    private $lastParsedType = null;
    const STRING   = 6;
    const INTEGER  = 1;
    const FLOAT    = 2;
    const DATETIME = 3;
    const TABLE    = 4;
    const BOOLEAN  = 5;

    private $pointer;
    private $current = null;

    private $cursor = null;
    private $key    = null;
    private $lvl    = 0;

    private $line       = '';
    private $lineLength = 0;

    private $row = 1;
    private $col = 0;

    private $tables      = array();
    private $tableArrays = array();


    public static function parse ($input)
    {
        $p = new self(explode("\n", $input));

        return $p->out;
    }

    public static function parseFile ($input)
    {
        if (is_file($input) && is_readable($input)) {
            $input = file($input);
        } else {
            throw new \InvalidArgumentException("Could not open TOML file '".$input."'.");
        }

        $p = new self($input);

        return $p->out;
    }

    private function __construct ($input)
    {
        $this->in     = $input;
        $this->cursor = &$this->out;

        foreach ($this->in as &$row) {
            $this->line       = trim($row);
            $this->lineLength = strlen($this->line);

            if (empty($this->line)) {
                $this->cursor = &$this->out;
            } elseif ($this->line[$this->col] != "#") {
                if ($this->line[$this->col] == '[' && $this->lvl == 0) {
                    if ($this->line[($this->col+1)] == '[') {
                        # table array
                        $this->createTableArray();
                    } else {
                        # regular table
                        $this->createTable();
                    }
                } else {
                    if (preg_match('/^(\S+)\s*=\s*(.*)$/s', $this->line, $match)) {
                        # disallow empty keys (like `key = `)
                        if ($match[2] === "") {
                            throw new \UnexpectedValueException("Empty key found near '".$this->line."' on line ".$this->row.".");
                        }

                        # explicitly catch key defintions when still building an array (e.g. when mistakenly using more [ than ] brackets)
                        if ($this->lvl != 0) {
                            throw new \UnexpectedValueException("Expected array but found key definition near '".$this->line."' on line ".$this->row.".");
                        }

                        # key (as in `something = ...`)
                        $this->createKey($match[1]);

                        # move col to pos of value
                        $this->col = strpos($this->line, $match[2]);
                    }

                    $this->readLine();
                }

                $this->parseLineEnd();
            }

            $this->row++;
            $this->col = 0;
        }
    }


    private function readLine()
    {/*
        end($this->cursor);

        $this->current = &$this->cursor[key($this->cursor)];
*/
        if ($this->lvl == 0) {
            $this->current = &$this->cursor[$this->key];
        }

        $data = null;

        while ($this->col < $this->lineLength) {
            if ($this->lastParsedType != null && $this->lvl == 0) {
                break;
            }

            $c = $this->line[$this->col];

            switch ($c) {
                case '"':

                    $data = $this->parseString();
                    break;

                case "'":

                    $data = $this->parseString(true);
                    break;

                case '[':

                    # remember the "parent" references for later
                    $this->pointer[$this->lvl++] = &$this->current;

                    # if it's a multidimensional array, we create the "inner" array here...
                    if ($this->lvl > 1) {
                        $this->current[] = array();
                        end($this->current);
                        $this->current = &$this->current[key($this->current)]; # ...and set `current` to refer to it
                    }

                    $this->lastParsedType = null;
                    $this->prevType       = null;

                    break;

                case ']':

                    # go back to "parent" reference
                    $this->current = &$this->pointer[--$this->lvl];

                    # forget the "parent" reference
                    unset($this->pointer[$this->lvl]);

                    $this->lastParsedType = Toml::TABLE;

                    break;

                case ' ':
                case ',':

                    break;

                default:

                    if ($c == '#') {
                        break 2;
                    } else {
                        $data = $this->parseData();
                    }
            }

            if ($data != null) {
                if ($this->prevType != null
                    && $this->lastParsedType != null
                    && $this->lastParsedType != $this->prevType) {
                    throw new \UnexpectedValueException("Array with mixed data types found near '".$this->line."' on line ".$this->row.".");
                }

                if ($this->lvl == 0) {
                    $this->current = $data;
                } else {
                    $this->current[] = $data;
                }

                $this->prevType = $this->lastParsedType;

                # data parsed and stored, set to null for next
                $data = null;
            }

            $this->col++;
        }
    }


    private function parseString($isLiteral = false)
    {
        $line   = $this->line;
        $string = null;

        while (1+$this->col < $this->lineLength) {
            $c = $line[++$this->col];

            if ($c == '\\' && !$isLiteral) {
                // Escaped character

                $c = $line[++$this->col];

                if ($c == 'b') {
                    $string .= mb_convert_encoding(pack('H*', '0008'), 'UTF-8', 'UCS-2BE');
                } elseif ($c == 't') {
                    $string .= mb_convert_encoding(pack('H*', '0009'), 'UTF-8', 'UCS-2BE');
                } elseif ($c == 'n') {
                    $string .= mb_convert_encoding(pack('H*', '000A'), 'UTF-8', 'UCS-2BE');
                } elseif ($c == 'f') {
                    $string .= mb_convert_encoding(pack('H*', '000C'), 'UTF-8', 'UCS-2BE');
                } elseif ($c == 'r') {
                    $string .= mb_convert_encoding(pack('H*', '000D'), 'UTF-8', 'UCS-2BE');
                } elseif ($c == '"') {
                    $string .= mb_convert_encoding(pack('H*', '0022'), 'UTF-8', 'UCS-2BE');
                } elseif ($c == '/') {
                    $string .= mb_convert_encoding(pack('H*', '002F'), 'UTF-8', 'UCS-2BE');
                } elseif ($c == '\\') {
                    $string .= mb_convert_encoding(pack('H*', '005C'), 'UTF-8', 'UCS-2BE');
                } elseif ($c == 'u') {
                    // Escaped unicode sequence

                    $code = null;

                    for ($j=$this->col; $j<($this->col+5); $j++) {
                        $code .= $line[$j];
                    }

                    # move column ahead in current line
                    $this->col = $j-1;

                    if (preg_match('/\\\\u([0-9a-f]{4})/i', '\\'.$code, $match)) {
                        $string .= mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
                    } else {
                        throw new \Exception("Unrecognized Unicode sequence '$code' on line ".$this->currentLine." near column ".($this->currentCol+1).".");
                    }
                }
            } elseif (($c == '"' && !$isLiteral) || ($c == "'" && $isLiteral)) {
                // End of String

                $this->lastParsedType = Toml::STRING;

                return $string;
            } else {
                // Build String

                $string .= $c;
            }
        }

        throw new \Exception("TOML parsing error on line ".$this->row.". Missing closing String delimiter.");
    }

    private function parseData()
    {
        $line   = $this->line;
        $data   = "";
        $parsed = null;

        while ($this->col < $this->lineLength) {
            $c = $line[$this->col];

            // TODO
            if ($c == "\n" || $c == ' ' || $c == '#' || $c == ',' || $c == ']') {
                $this->col--;
                break;
            }

            $data .= $c;

            $this->col++;
        }

        if ($data === "") {
            throw new \UnexpectedValueException("Invalid TOML syntax. Empty key on line ".$this->row.".");
        }


        // parse bools

        if ($data === 'true' || $data === 'false') {
            $this->lastParsedType = Toml::BOOLEAN;
            $parsed = ($data === 'true');
        }
        else

        // parse floats

        if (preg_match('/^\-?\d*?\.\d+$/', $data)) {
            $this->lastParsedType = Toml::FLOAT;
            $parsed = (float) $data;
        }
        else

        // parse integers

        if (preg_match('/^\-?\d*?$/', $data)) {
            $this->lastParsedType = Toml::INTEGER;
            $parsed = (int) $data;
        }
        else

        // parse datetime

        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})Z$/', $data)) {
            $this->lastParsedType = Toml::DATETIME;

            try {
                $dt = new \Datetime($data);
            } catch (\Exception $e) {
                date_default_timezone_set('Europe/Berlin');
                $dt = new \Datetime($data);
            }

            $parsed = $dt;
        }

        if ($parsed !== null) {
            return $parsed;
        }

        throw new \UnexpectedValueException("Unrecognized data type '$data' on line ".$this->row." near column ".($this->col+1).".");
    }


    private function parseLineEnd()
    {
        if ($this->lvl == 0) {
            $this->lastParsedType = null;
            $this->prevType       = null;
            $this->key            = null;
        }

        while ($this->col < $this->lineLength) {
            # if we find a '#' somewhere, the following stuff is a comment, so we're done here...
            if ($this->line[$this->col] == '#') {
                return;
            }

            if ($this->lvl > 0 && $this->line[$this->col] == ',') {
                return;
            }

            # else only allow whitespace characters
            if ($this->line[$this->col] != ' ') {
                throw new \UnexpectedValueException("Invalid TOML syntax on line ".$this->row.". Value '".substr($this->line, $this->col)."' found. Expected whitespace or newline character.");
            }

            $this->col++;
        }
    }


    private function createKey($key)
    {
        $line = $this->line;
        $key = trim($key);

        if (isset($this->cursor[$key])) {
            throw new \Exception("Invalid TOML syntax on line ".$this->row.". Key '".$key."' already exists.");
        }

        $this->cursor[$key] = null;
        $this->key          = $key;
    }

    private function createTable()
    {
        $table = &$this->out;
        $name  = null;
        $tableid = null;

        while ($this->col < $this->lineLength) {
            $c = $this->line[$this->col];

            if ($c == '.') {
                $tableid .= $name.'.';

                if (isset($table[$name]) && !is_array($table[$name])) {
                    throw new \Exception("TOML parsing error on line ".$this->row.". Table [".$tableid."] already defined as key.");
                }

                $table = &$table[$name];
                $name = '';
            } elseif ($c == ']') {
                $this->lineEnd = true;
                break;
            } elseif ($c != '[') {
                $name .= $c;
            }

            $this->col++;
        }

        $tableid .= $name;

        if (isset($this->tables[$tableid])) {
            throw new \Exception("TOML parsing error on line ".$this->row.". Table [".$tableid."] already defined on line ".$this->tables[$tableid].".");
        }

        if (isset($table[$name]) && !is_array($table[$name])) {
            throw new \Exception("TOML parsing error on line ".$this->row.". Table [".$tableid."] already defined as key.");
        }

        $table                  = &$table[$name];
        $this->cursor           = &$table;
        $this->tables[$tableid] = $this->row;

        $this->col++;
    }

    private function createTableArray()
    {
        if (preg_match('/^\[\[([^\s]+\])\].*$/', $this->line, $match)) {
            $line = $match[1];
            $table = &$this->out;
            $name  = null;
            $tableid = null;

            while ($this->col < $this->lineLength) {
                $c = $line[$this->col++];

                if ($c == '.') {
                    $tableid .= $name.'.';

                    if (isset($table[$name]) && !is_array($table[$name])) {
                        throw new \Exception("TOML parsing error on line ".$this->row.". Table [".$tableid."] already defined as key.");
                    }

                    if (isset($this->tableArrays[$name])) {
                        $table = &$this->tableArrays[$name];
                    } else {
                        $table = &$table[$name];
                    }

                    $name = '';
                } elseif ($c == ']') {
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
        } else {
            throw new \Exception("Invalid TOML syntax. Could not recognize table format on line ".$this->row.".");
        }
    }

    private function __destruct()
    {

    }

    private function __clone()
    {

    }
}
