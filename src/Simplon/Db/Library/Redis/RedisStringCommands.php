<?php

    namespace Simplon\Db\Library\Redis;

    class RedisStringCommands
    {
        use RedisCommandsTrait;

        // ##########################################

        /**
         * @param $key
         * @param $value
         * @param $expire
         *
         * @return array
         */
        protected function _getSetQuery($key, $value, $expire = -1)
        {
            if ($expire > 0)
            {
                return ['SETEX', $key, $expire, $value];
            }

            return ['SET', $key, $value];
        }

        // ##########################################

        /**
         * @param $key
         * @param $value
         * @param $expire
         *
         * @return bool|mixed
         */
        public function setValue($key, $value, $expire = -1)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getSetQuery($key, $value, $expire));

            if ($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param array $pairs
         * @param $expire
         *
         * @return bool
         */
        public function setMulti(array $pairs, $expire = -1)
        {
            $this
                ->_getRedisInstance()
                ->pipelineEnable(TRUE);

            foreach ($pairs as $key => $value)
            {
                $this
                    ->_getRedisInstance()
                    ->pipelineAddQueueItem($this->_getSetQuery($key, $value, $expire));
            }

            $response = $this
                ->_getRedisInstance()
                ->pipelineExecute();

            if (empty($response['errors']))
            {
                return TRUE;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $key
         *
         * @return array
         */
        protected function _getValueQuery($key)
        {
            return array('GET', $key);
        }

        // ##########################################

        /**
         * @param $key
         *
         * @return bool|mixed
         */
        public function getValue($key)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getValueQuery($key));

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
         * @return array
         */
        protected function _getValueMultiQuery(array $keys)
        {
            return array_merge(['MGET'], $keys);
        }

        // ##########################################

        /**
         * @param array $keys
         *
         * @return bool|mixed
         */
        public function getValueMulti(array $keys)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getValueMultiQuery($keys));

            if ($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }
    }