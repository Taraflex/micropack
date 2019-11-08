<?php

namespace AwesomePackage {


    class CustomMessage
    {
        /**
         * @var string
         */
        public $test = '';

        private $__indices = array(1 => 'test');

        /**
         * @param string $data
         */
        public function __construct(string $data = null)
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

                case 1/*test*/:
                    // string
                    $size = ord($data[$offset]) | (ord($data[++$offset]) << 8) | (ord($data[++$offset]) << 16) | (ord($data[($offset += 2) - 1]) << 24);
                    list(, $this->{$field}) = unpack('a' . $size, substr($data, $offset, $size));
                    $offset += $size;
                    return $offset;
            }
        }

        /**
         * @return string
         */
        public function dump()
        {
            return CustomMessageSerializer::create()
                ->test($this->test)->dump();
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
                'test' => $this->test
            );
        }
    }
}