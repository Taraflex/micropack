<?php

namespace AwesomePackage {

    /**
     * documentation
     */
    final class AwesomeMessage
    {
        /**
         * @var string
         */
        public $str = '';
        /**
         * @var string documentation
         */
        public $str_empty = '';
        /**
         * @var bool
         */
        public $boolean = false;
        /**
         * @var bool documetation
         */
        public $boolean_empty = false;
        /**
         * @var int
         */
        public $uint = 0;
        /**
         * @var int
         */
        public $uint_empty = 0;
        /**
         * @var string[]
         */
        public $r_str = array();
        /**
         * @var string[]
         */
        public $r_str_empty = array();
        /**
         * @var bool[]
         */
        public $r_boolean = array();
        /**
         * @var bool[]
         */
        public $r_boolean_empty = array();
        /**
         * @var int[]
         */
        public $r_uint = array();
        /**
         * @var int[]
         */
        public $r_uint_empty = array();

        private $__indices = array(1 => 'str', 2 => 'str_empty', 3 => 'boolean', 4 => 'boolean_empty', 5 => 'uint', 6 => 'uint_empty', 7 => 'r_str', 8 => 'r_str_empty', 9 => 'r_boolean', 10 => 'r_boolean_empty', 11 => 'r_uint', 12 => 'r_uint_empty');

        /**
         * @param string $data
         */
        public function __construct($data = null)
        {
            if ($data) {
                $size   = strlen($data);
                $offset = 0;
                do {
                    $offset = $this->__parse($data, $offset + 1, ord($data[$offset]));
                } while ($offset < $size);
            }
        }

        private function __parse($data, $offset, $id)
        {
            $field = $this->__indices[$id];
            switch ($id) {

                case 1/*str*/: case 2/*str_empty*/:
                    // string
                    $size = ord($data[$offset]) | (ord($data[++$offset]) << 8) | (ord($data[++$offset]) << 16) | (ord($data[($offset += 2) - 1]) << 24);
                    list(, $this->{$field}) = unpack('a' . $size, substr($data, $offset, $size));
                    $offset += $size;
                    return $offset;

                case 3/*boolean*/: case 4/*boolean_empty*/:
                    // bool
                    $this->{$field} = true;
                    return $offset;

                case 5/*uint*/: case 6/*uint_empty*/:
                    // uint32
                    $this->{$field} = ord($data[$offset]) | (ord($data[++$offset]) << 8) | (ord($data[++$offset]) << 16) | (ord($data[($offset += 2) - 1]) << 24);
                    return $offset;

                case 7/*r_str*/: case 8/*r_str_empty*/:
                    // repeated string
                    $count = ord($data[$offset]) | (ord($data[++$offset]) << 8) | (ord($data[++$offset]) << 16) | (ord($data[($offset += 2) - 1]) << 24);
                    while (--$count >= 0) {
                        $size = ord($data[$offset]) | (ord($data[++$offset]) << 8) | (ord($data[++$offset]) << 16) | (ord($data[($offset += 2) - 1]) << 24);
                        list(, $this->{$field}[]) = unpack('a' . $size, substr($data, $offset, $size));
                        $offset += $size;
                    }
                    return $offset;

                case 9/*r_boolean*/: case 10/*r_boolean_empty*/:
                    // repeated bool
                    $size = ord($data[$offset]) | (ord($data[++$offset]) << 8) | (ord($data[++$offset]) << 16) | (ord($data[($offset += 2) - 1]) << 24);
                    $this->{$field} = unpack('C' . $size, substr($data, $offset, $size));
                    $offset += $size;
                    return $offset;

                case 11/*r_uint*/: case 12/*r_uint_empty*/:
                    // repeated uint32
                    $size = ord($data[$offset]) | (ord($data[++$offset]) << 8) | (ord($data[++$offset]) << 16) | (ord($data[($offset += 2) - 1]) << 24);
                    $this->{$field} = unpack('V' . $size, substr($data, $offset, $size * 4));
                    $offset += $size * 4;
                    return $offset;
            }
        }

        /**
         * @return string
         */
        public function dump()
        {
            return AwesomeMessageSerializer::create()
                ->str($this->str)
                ->str_empty($this->str_empty)
                ->boolean($this->boolean)
                ->boolean_empty($this->boolean_empty)
                ->uint($this->uint)
                ->uint_empty($this->uint_empty)
                ->r_str($this->r_str)
                ->r_str_empty($this->r_str_empty)
                ->r_boolean($this->r_boolean)
                ->r_boolean_empty($this->r_boolean_empty)
                ->r_uint($this->r_uint)
                ->r_uint_empty($this->r_uint_empty)->dump();
        }

        /**
         * @return string
         */
        public function __toString()
        {
            return $this->dump();
        }

        public function toArray()
        {
            return array(
                'str' => $this->str,
                'str_empty' => $this->str_empty,
                'boolean' => $this->boolean,
                'boolean_empty' => $this->boolean_empty,
                'uint' => $this->uint,
                'uint_empty' => $this->uint_empty,
                'r_str' => $this->r_str,
                'r_str_empty' => $this->r_str_empty,
                'r_boolean' => $this->r_boolean,
                'r_boolean_empty' => $this->r_boolean_empty,
                'r_uint' => $this->r_uint,
                'r_uint_empty' => $this->r_uint_empty
            );
        }
    }
}