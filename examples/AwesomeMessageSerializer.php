<?php

namespace AwesomePackage {

    final class AwesomeMessageSerializer
    {
        private $__data = array('');

        private function __construct()
        {}

        /**
         * @return string
         */
        public function dump()
        {
            return call_user_func_array('pack', $this->__data);
        }

        /**
         * @return string
         */
        public function __toString()
        {
            return $this->dump();
        }

        /**
         * @return self
         */
        public static function create()
        {
            return new static();
        }

        /**
         * @param  string $v
         * @return self
         */
        public function str(string $v)
        {
            if ($v) {
                $size = strlen($v);
                $this->__data[0] .= 'CVa' . $size;
                $this->__data[] = 1;
                $this->__data[] = $size;
                $this->__data[] = $v;
            }
            return $this;
        }
        /**
         * documentation
         * 
         * @param  string $v
         * @return self
         */
        public function str_empty(string $v)
        {
            if ($v) {
                $size = strlen($v);
                $this->__data[0] .= 'CVa' . $size;
                $this->__data[] = 2;
                $this->__data[] = $size;
                $this->__data[] = $v;
            }
            return $this;
        }
        /**
         * @param  bool $v
         * @return self
         */
        public function boolean(bool $v)
        {
            if ($v) {
                $this->__data[0] .= 'C';
                $this->__data[] = 3;
            }
            return $this;
        }
        /**
         * documetation
         * 
         * @param  bool $v
         * @return self
         */
        public function boolean_empty(bool $v)
        {
            if ($v) {
                $this->__data[0] .= 'C';
                $this->__data[] = 4;
            }
            return $this;
        }
        /**
         * @param  int $v
         * @return self
         */
        public function uint(int $v)
        {
            if ($v) {
                $this->__data[0] .= 'CV';
                $this->__data[] = 5;
                $this->__data[] = $v;
            }
            return $this;
        }
        /**
         * @param  int $v
         * @return self
         */
        public function uint_empty(int $v)
        {
            if ($v) {
                $this->__data[0] .= 'CV';
                $this->__data[] = 6;
                $this->__data[] = $v;
            }
            return $this;
        }
        /**
         * @param  string[] $v
         * @return self
         */
        public function r_str(array $v)
        {
            if ($v) {
                $c = count($v);
                $this->__data[0] .= 'CV';
                $this->__data[] = 7;
                $this->__data[] = $c;
                foreach ($v as $str) {
                    $size = strlen($str);
                    $this->__data[0] .= 'Va' . $size;
                    $this->__data[] = $size;
                    $this->__data[] = $str;
                }
            }
            return $this;
        }
        /**
         * @param  string[] $v
         * @return self
         */
        public function r_str_empty(array $v)
        {
            if ($v) {
                $c = count($v);
                $this->__data[0] .= 'CV';
                $this->__data[] = 8;
                $this->__data[] = $c;
                foreach ($v as $str) {
                    $size = strlen($str);
                    $this->__data[0] .= 'Va' . $size;
                    $this->__data[] = $size;
                    $this->__data[] = $str;
                }
            }
            return $this;
        }
        /**
         * @param  bool[] $v
         * @return self
         */
        public function r_boolean(array $v)
        {
            if ($v) {
                $size = count($v);
                $this->__data[0] .= 'CVC' . $size;
                $this->__data[] = 9;
                $this->__data[] = $size;
                $this->__data   = array_merge($this->__data, $v);
            }
            return $this;
        }
        /**
         * @param  bool[] $v
         * @return self
         */
        public function r_boolean_empty(array $v)
        {
            if ($v) {
                $size = count($v);
                $this->__data[0] .= 'CVC' . $size;
                $this->__data[] = 10;
                $this->__data[] = $size;
                $this->__data   = array_merge($this->__data, $v);
            }
            return $this;
        }
        /**
         * @param  int[] $v
         * @return self
         */
        public function r_uint(array $v)
        {
            if ($v) {
                $size = count($v);
                $this->__data[0] .= 'CVV' . $size;
                $this->__data[] = 11;
                $this->__data[] = $size;
                $this->__data   = array_merge($this->__data, $v);
            }
            return $this;
        }
        /**
         * @param  int[] $v
         * @return self
         */
        public function r_uint_empty(array $v)
        {
            if ($v) {
                $size = count($v);
                $this->__data[0] .= 'CVV' . $size;
                $this->__data[] = 12;
                $this->__data[] = $size;
                $this->__data   = array_merge($this->__data, $v);
            }
            return $this;
        }
    }
}