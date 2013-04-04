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

        // ######################################

        /**
         * @param $indexStart
         * @param $limit
         * @return mixed
         */
        protected function _calcRangeLimit($indexStart, $limit)
        {
            return $indexStart + ($limit - 1);
        }
    }