<?php

    namespace Simplon\Db\Library\Redis;

    class RedisBitCommands
    {
        use RedisCommandsTrait;

        // ##########################################

        /**
         * @param $key
         * @param $offset
         * @param $value
         * @return array
         */
        protected function _getBitSetQuery($key, $offset, $value)
        {
            return ['SETBIT', $key, $offset, $value];
        }

        // ##########################################

        /**
         * @param $key
         * @param $offset
         * @param $value
         * @return bool|mixed
         */
        public function bitSet($key, $offset, $value)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getBitSetQuery($key, $offset, $value));

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $key
         * @param $offset
         * @return array
         */
        protected function _getBitGetQuery($key, $offset)
        {
            return ['GETBIT', $key, $offset];
        }

        // ##########################################

        /**
         * @param $key
         * @param $offset
         * @return bool|mixed
         */
        public function bitGet($key, $offset)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getBitGetQuery($key, $offset));

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $key
         * @return array
         */
        protected function _getBitGetAllQuery($key)
        {
            return ['GET', $key];
        }

        // ##########################################

        /**
         * @param $key
         * @return bool|mixed
         */
        public function bitGetAll($key)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getBitGetAllQuery($key));

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $key
         * @param int $start
         * @param $end
         * @return array
         */
        protected function _getBitCountQuery($key, $start = 0, $end = -1)
        {
            return ['BITCOUNT', $key, $start, $end];
        }

        // ##########################################

        /**
         * @param $key
         * @param int $start
         * @param $end
         * @return bool|mixed
         */
        public function bitCount($key, $start = 0, $end = -1)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getBitCountQuery($key, $start, $end));

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $key
         * @return bool|mixed
         */
        public function bitDelete($key)
        {
            $response = $this
                ->_getRedisInstance()
                ->keyDelete($key);

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param array $keys
         * @return bool|mixed
         */
        public function bitDeleteMulti(array $keys)
        {
            $response = $this
                ->_getRedisInstance()
                ->keyDeleteMulti($keys);

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }
    }