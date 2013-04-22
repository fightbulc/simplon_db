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
         *
         * @return array
         */
        protected function _getSetQuery($key, $offset, $value)
        {
            return ['SETBIT', $key, $offset, $value];
        }

        // ##########################################

        /**
         * @param $key
         * @param $offset
         * @param $value
         *
         * @return bool|mixed
         */
        public function set($key, $offset, $value)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getSetQuery($key, $offset, $value));

            if ($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $key
         * @param $offset
         *
         * @return array
         */
        protected function _getQuery($key, $offset)
        {
            return ['GETBIT', $key, $offset];
        }

        // ##########################################

        /**
         * @param $key
         * @param $offset
         *
         * @return bool|mixed
         */
        public function get($key, $offset)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getQuery($key, $offset));

            if ($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $key
         *
         * @return array
         */
        protected function _getAllQuery($key)
        {
            return ['GET', $key];
        }

        // ##########################################

        /**
         * @param $key
         *
         * @return bool|mixed
         */
        public function getAll($key)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getAllQuery($key));

            if ($response != FALSE)
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
         *
         * @return array
         */
        protected function _getCountQuery($key, $start = 0, $end = -1)
        {
            return ['BITCOUNT', $key, $start, $end];
        }

        // ##########################################

        /**
         * @param $key
         * @param int $start
         * @param $end
         *
         * @return bool|mixed
         */
        public function getCount($key, $start = 0, $end = -1)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getCountQuery($key, $start, $end));

            if ($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }
    }