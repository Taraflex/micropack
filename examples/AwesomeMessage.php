<?php

namespace AwesomePackage {

    /**
     * documentation
     */
    class AwesomeMessage
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
         * @var int
         */
        public $enum = 0;
        /**
         * @var int
         */
        public $enum_empty = 0;
        /**
         * @var string
         */
        public $bt = '';
        /**
         * @var string
         */
        public $bt_empty = '';
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
        /**
         * @var int[]
         */
        public $r_enum = array();
        /**
         * @var int[]
         */
        public $r_enum_empty = array();
        /**
         * @var string[]
         */
        public $r_bt = array();
        /**
         * @var string[]
         */
        public $r_bt_empty = array();
        /**
         * @var \AwesomePackage\CustomMessage
         */
        public $custom = null;
        /**
         * @var \AwesomePackage\CustomMessage
         */
        public $custom_empty = null;
        /**
         * @var \AwesomePackage\CustomMessage[]
         */
        public $r_custom = array();
        /**
         * @var \AwesomePackage\CustomMessage[]
         */
        public $r_custom_empty = array();

        private $__indices = array(1 => 'str', 2 => 'str_empty', 3 => 'boolean', 4 => 'boolean_empty', 5 => 'uint', 6 => 'uint_empty', 7 => 'enum', 8 => 'enum_empty', 9 => 'bt', 10 => 'bt_empty', 11 => 'r_str', 12 => 'r_str_empty', 13 => 'r_boolean', 14 => 'r_boolean_empty', 15 => 'r_uint', 16 => 'r_uint_empty', 17 => 'r_enum', 18 => 'r_enum_empty', 19 => 'r_bt', 20 => 'r_bt_empty', 21 => 'custom', 22 => 'custom_empty', 23 => 'r_custom', 24 => 'r_custom_empty');

        /**
         * @param string $data
         * @param int    $offset
         * @param int    $size
         */
        public function __construct(string $data = null, int $offset = 0, int $size = 2147483647)
        {
            if ($data) {
                $end = $offset + $size;
                while ($offset < $end && $id = ord(@$data[$offset])) {
                    $offset = $this->__parse($data, $offset + 1, $id);
                }
            }
        }

        private function __parse($data, $offset, $id)
        {
            $field = $this->__indices[$id];
            switch ($id) {

                case 1/*str*/: case 2/*str_empty*/:
                    // string
                    $size = ord($data[$offset]) | (ord($data[++$offset]) << 8) | (ord($data[++$offset]) << 16) | (ord($data[($offset += 2) - 1]) << 24);
                    $this->{$field} = substr($data, $offset, $size);
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

                case 7/*enum*/: case 8/*enum_empty*/:
                    // \AwesomePackage\AwesomeEnum
                    $this->{$field} = ord($data[$offset]) | (ord($data[++$offset]) << 8) | (ord($data[++$offset]) << 16) | (ord($data[($offset += 2) - 1]) << 24);
                    return $offset;

                case 9/*bt*/: case 10/*bt_empty*/:
                    // bytes
                    $size = ord($data[$offset]) | (ord($data[++$offset]) << 8) | (ord($data[++$offset]) << 16) | (ord($data[($offset += 2) - 1]) << 24);
                    $this->{$field} = substr($data, $offset, $size);
                    $offset += $size;
                    return $offset;

                case 11/*r_str*/: case 12/*r_str_empty*/: case 19/*r_bt*/: case 20/*r_bt_empty*/:
                    // repeated string
                    $count = ord($data[$offset]) | (ord($data[++$offset]) << 8) | (ord($data[++$offset]) << 16) | (ord($data[($offset += 2) - 1]) << 24);
                    while (--$count >= 0) {
                        $size = ord($data[$offset]) | (ord($data[++$offset]) << 8) | (ord($data[++$offset]) << 16) | (ord($data[($offset += 2) - 1]) << 24);
                        $this->{$field}[] = substr($data, $offset, $size);
                        $offset += $size;
                    }
                    return $offset;

                case 13/*r_boolean*/: case 14/*r_boolean_empty*/:
                    // repeated bool
                    $size = ord($data[$offset]) | (ord($data[++$offset]) << 8) | (ord($data[++$offset]) << 16) | (ord($data[($offset += 2) - 1]) << 24);
                    $this->{$field} = unpack('C' . $size, substr($data, $offset, $size));
                    $offset += $size;
                    return $offset;

                case 15/*r_uint*/: case 16/*r_uint_empty*/:
                    // repeated uint32
                    $size = ord($data[$offset]) | (ord($data[++$offset]) << 8) | (ord($data[++$offset]) << 16) | (ord($data[($offset += 2) - 1]) << 24);
                    $this->{$field} = unpack('V' . $size, substr($data, $offset, $size * 4));
                    $offset += $size * 4;
                    return $offset;

                case 17/*r_enum*/: case 18/*r_enum_empty*/:
                    // repeated \AwesomePackage\AwesomeEnum
                    $size = ord($data[$offset]) | (ord($data[++$offset]) << 8) | (ord($data[++$offset]) << 16) | (ord($data[($offset += 2) - 1]) << 24);
                    $this->{$field} = unpack('V' . $size, substr($data, $offset, $size * 4));
                    $offset += $size * 4;
                    return $offset;

                case 21/*custom*/: case 22/*custom_empty*/:
                    // \AwesomePackage\CustomMessage
                    $size = ord($data[$offset]) | (ord($data[++$offset]) << 8) | (ord($data[++$offset]) << 16) | (ord($data[($offset += 2) - 1]) << 24);
                    $this->{$field} = new \AwesomePackage\CustomMessage($data, $offset, $size);
                    $offset += $size;
                    return $offset;

                case 23/*r_custom*/: case 24/*r_custom_empty*/:
                    // repeated \AwesomePackage\CustomMessage
                    $count = ord($data[$offset]) | (ord($data[++$offset]) << 8) | (ord($data[++$offset]) << 16) | (ord($data[($offset += 2) - 1]) << 24);
                    while (--$count >= 0) {
                        $size = ord($data[$offset]) | (ord($data[++$offset]) << 8) | (ord($data[++$offset]) << 16) | (ord($data[($offset += 2) - 1]) << 24);
                        $this->{$field}[] = new \AwesomePackage\CustomMessage($data, $offset, $size);
                        $offset += $size;
                    }
                    return $offset;
            }
        }

        /**
         * @return string
         */
        public function dump()
        {
            return \AwesomePackage\AwesomeMessageSerializer::create()
                ->str($this->str)
                ->str_empty($this->str_empty)
                ->boolean($this->boolean)
                ->boolean_empty($this->boolean_empty)
                ->uint($this->uint)
                ->uint_empty($this->uint_empty)
                ->enum($this->enum)
                ->enum_empty($this->enum_empty)
                ->bt($this->bt)
                ->bt_empty($this->bt_empty)
                ->r_str($this->r_str)
                ->r_str_empty($this->r_str_empty)
                ->r_boolean($this->r_boolean)
                ->r_boolean_empty($this->r_boolean_empty)
                ->r_uint($this->r_uint)
                ->r_uint_empty($this->r_uint_empty)
                ->r_enum($this->r_enum)
                ->r_enum_empty($this->r_enum_empty)
                ->r_bt($this->r_bt)
                ->r_bt_empty($this->r_bt_empty)
                ->custom($this->custom)
                ->custom_empty($this->custom_empty)
                ->r_custom($this->r_custom)
                ->r_custom_empty($this->r_custom_empty)->dump();
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
                'enum' => $this->enum,
                'enum_empty' => $this->enum_empty,
                'bt' => $this->bt,
                'bt_empty' => $this->bt_empty,
                'r_str' => $this->r_str,
                'r_str_empty' => $this->r_str_empty,
                'r_boolean' => $this->r_boolean,
                'r_boolean_empty' => $this->r_boolean_empty,
                'r_uint' => $this->r_uint,
                'r_uint_empty' => $this->r_uint_empty,
                'r_enum' => $this->r_enum,
                'r_enum_empty' => $this->r_enum_empty,
                'r_bt' => $this->r_bt,
                'r_bt_empty' => $this->r_bt_empty,
                'custom' => $this->custom,
                'custom_empty' => $this->custom_empty,
                'r_custom' => $this->r_custom,
                'r_custom_empty' => $this->r_custom_empty
            );
        }
    }
}