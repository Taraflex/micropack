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
         * @param int    $offset
         * @param int    $size
         */
        public function __construct(string $data = null, int $offset = 0, int $size = 2147483647)
        {
            if ($data) {
                $end = $offset + $size;
                while ($offset < $end && $id = ord($data[$offset])) {
                    $offset = $this->__parse($data, $offset + 1, $id);
                }
            }
        }

        private function __parse($data, $offset, $id)
        {
            $field = $this->__indices[$id];
            switch ($id) {

                case 1/*test*/:
                    // string
                    $size = ord($data[$offset]) | (ord($data[++$offset]) << 8) | (ord($data[++$offset]) << 16) | (ord($data[($offset += 2) - 1]) << 24);
                    $this->{$field} = substr($data, $offset, $size);
                    $offset += $size;
                    return $offset;
            }
        }

        /**
         * @return string
         */
        public function dump()
        {
            return \AwesomePackage\CustomMessageSerializer::create()
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