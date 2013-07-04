<?php

    namespace Simplon\Db\Library\Redis;

    class RedisCacheInvalidatorObjects
    {
        protected $_keys = [];
        protected $_keyPatterns = [];

        // ######################################

        public function __construct()
        {
            $this->_keys = [];
            $this->_keyPatterns = [];
        }

        // ######################################

        /**
         * @return array
         */
        public function getKeys()
        {
            return $this->_keys;
        }

        // ######################################

        /**
         * @return array
         */
        public function getKeyPatterns()
        {
            return $this->_keyPatterns;
        }

        // ######################################

        /**
         * @param $key
         *
         * @return RedisCacheInvalidatorObjects
         */
        public function onKey($key)
        {
            $this->_keys[] = $key;

            return $this;
        }

        // ######################################

        /**
         * @param array $keys
         *
         * @return RedisCacheInvalidatorObjects
         */
        public function onKeyMultiple(array $keys)
        {
            if (!empty($keys))
            {
                foreach ($keys as $key)
                {
                    $this->onKey($key);
                }
            }

            return $this;
        }

        // ######################################

        /**
         * @param $cacheKeyPattern
         *
         * @return RedisCacheInvalidatorObjects
         */
        public function onPattern($cacheKeyPattern)
        {
            $this->_keyPatterns[] = $cacheKeyPattern;

            return $this;
        }

        // ######################################

        /**
         * @param array $cacheKeyPatterns
         *
         * @return RedisCacheInvalidatorObjects
         */
        public function onPatternMultiple(array $cacheKeyPatterns)
        {
            if (!empty($cacheKeyPatterns))
            {
                foreach ($cacheKeyPatterns as $keyPattern)
                {
                    $this->onPattern($keyPattern);
                }
            }

            return $this;
        }
    }