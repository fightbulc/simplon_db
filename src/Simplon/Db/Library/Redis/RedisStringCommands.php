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
         * @return array
         */
        protected function _getStringSetQuery($key, $value, $expire = -1)
        {
            if($expire > 0)
            {
                return ['SETEX', $key, (string)$expire, $value];
            }

            return ['SET', $key, $value];
        }

        // ##########################################

        /**
         * @param $key
         * @param $value
         * @param $expire
         * @return bool|mixed
         */
        public function stringSet($key, $value, $expire = -1)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getStringSetQuery($key, $value, $expire));

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param array $pairs
         * @param $expire
         * @return bool
         */
        public function stringSetMulti(array $pairs, $expire = -1)
        {
            $this
                ->_getRedisInstance()
                ->pipelineEnable(TRUE);

            foreach($pairs as $key => $value)
            {
                $this
                    ->_getRedisInstance()
                    ->pipelineAddQueueItem($this->_getStringSetQuery($key, $value, $expire));
            }

            $response = $this
                ->_getRedisInstance()
                ->pipelineExecute();

            if(empty($response['errors']))
            {
                return TRUE;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $key
         * @return string
         */
        protected function _getStringGetQuery($key)
        {
            return array('GET', $key);
        }

        // ##########################################

        /**
         * @param $key
         * @return bool|mixed
         */
        public function stringGetData($key)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getStringGetQuery($key));

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param array $keys
         * @return array
         */
        protected function _getStringGetMultiQuery(array $keys)
        {
            return array_merge(['MGET'], $keys);
        }

        // ##########################################

        /**
         * @param array $keys
         * @return bool|mixed
         */
        public function stringGetDataMulti(array $keys)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getStringGetMultiQuery($keys));

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
        public function stringDelete($key)
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
        public function stringDeleteMulti(array $keys)
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