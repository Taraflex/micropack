<?php

namespace AwesomePackage {

    /**
     * documentation
     */
    final class AwesomeEnum
    {
        const UNIVERSAL = 0;
        /**
         * documentation
         */
        const WEB = 1;
        const WEB_ALIAS = 1;
        const IMAGES = 2;
        const LOCAL = 3;
        const NEWS = 4;
        const PRODUCTS = 5;
        const VIDEO = 6;

        /**
         * @param  int $v
         * @return string
         */
        public static function nameOf(int $v)
        {
            switch ($v) {
                case 0: return 'UNIVERSAL';
                case 1: return 'WEB';
                case 1: return 'WEB_ALIAS';
                case 2: return 'IMAGES';
                case 3: return 'LOCAL';
                case 4: return 'NEWS';
                case 5: return 'PRODUCTS';
                case 6: return 'VIDEO';
            }
            return ''; //todo throw Exception
        }

        /**
         * @param  string $v
         * @return int
         */
        public static function byName(string $v)
        {
            switch ($v) {
                case 'UNIVERSAL': return 0;
                case 'WEB': return 1;
                case 'WEB_ALIAS': return 1;
                case 'IMAGES': return 2;
                case 'LOCAL': return 3;
                case 'NEWS': return 4;
                case 'PRODUCTS': return 5;
                case 'VIDEO': return 6;
            }
            return 0; //todo throw Exception
        }
    }
}