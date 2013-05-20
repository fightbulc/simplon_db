<?php

    namespace Simplon\Db\Library\Redis;

    trait RedisCommandsTrait
    {
        /** @var \Simplon\Db\Library\Redis\Redis */
        protected $_redisInstance;

        // ######################################

        public function __construct(Redis $redisInstance)
        {
            $this->_redisInstance = $redisInstance;
        }

        // ######################################

        /**
         * @return Redis
         */
        protected function _getRedisInstance()
        {
            return $this->_redisInstance;
        }

        // ##########################################

        /**
         * @param $key
         *
         * @return bool|mixed
         */
        public function delete($key)
        {
            $response = $this
                ->_getRedisInstance()
                ->keyDelete($key);

            if ($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param array $keys
         *
         * @return bool|mixed
         */
        public function deleteMulti(array $keys)
        {
            $response = $this
                ->_getRedisInstance()
                ->keyDeleteMulti($keys);

            if ($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ######################################

        /**
         * @param $indexStart
         * @param $limit
         *
         * @return mixed
         */
        protected function _calcRangeLimit($indexStart, $limit)
        {
            return $indexStart + ($limit - 1);
        }
    }