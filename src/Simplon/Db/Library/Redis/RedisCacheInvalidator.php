<?php

    namespace Simplon\Db\Library\Redis;

    class RedisCacheInvalidator
    {
        protected $_redisInstance;
        protected $_log = [];

        // ######################################

        public function __construct(Redis $redisInstance)
        {
            $this->_redisInstance = $redisInstance;
            $this->_log = [];
        }

        // ######################################

        /**
         * @return Redis
         */
        protected function _getRedisInstance()
        {
            return $this->_redisInstance;
        }

        // ######################################

        /**
         * @param $key
         * @param $response
         *
         * @return RedisCacheInvalidator
         */
        protected function _addToLog($key, $response)
        {
            $this->_log[$key] = $response;

            return $this;
        }

        // ######################################

        /**
         * @return array
         */
        public function getLog()
        {
            return $this->_log;
        }

        // ######################################

        /**
         * @param array $keyPatterns
         *
         * @return array
         */
        protected function _getKeysByMatchingKeyPatterns(array $keyPatterns)
        {
            $keys = [];

            foreach ($keyPatterns as $keyPattern)
            {
                // match patterns
                $matchedKeys = $this
                    ->_getRedisInstance()
                    ->keysGetByPattern($keyPattern);

                if (!empty($matchedKeys))
                {
                    foreach ($matchedKeys as $mk)
                    {
                        $keys[] = $mk;
                    }
                }
            }

            return $keys;
        }

        // ######################################

        /**
         * @param RedisCacheInvalidatorObjects $cacheInvalidatorObjects
         *
         * @return RedisCacheInvalidator
         */
        public function run(RedisCacheInvalidatorObjects $cacheInvalidatorObjects)
        {
            // get key patterns
            $keyPatterns = $cacheInvalidatorObjects->getKeyPatterns();

            // find keys by pattern and add to keys
            $cacheInvalidatorObjects->onKeyMultiple($this->_getKeysByMatchingKeyPatterns($keyPatterns));

            // ----------------------------------

            $keys = $cacheInvalidatorObjects->getKeys();

            foreach ($keys as $key)
            {
                // delete key
                $response = $this
                    ->_getRedisInstance()
                    ->keyDelete($key);

                // add response to log
                $this->_addToLog($key, $response);
            }

            // ----------------------------------

            return $this;
        }
    }