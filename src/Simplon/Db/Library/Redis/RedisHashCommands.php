<?php

    namespace Simplon\Db\Library\Redis;

    class RedisHashCommands
    {
        use RedisCommandsTrait;

        // ##########################################

        /**
         * @param $hashId
         * @param $keyId
         * @param $value
         *
         * @return array
         */
        protected function _getSetKeyValueQuery($hashId, $keyId, $value)
        {
            return ['HSET', $hashId, $keyId, $value];
        }

        // ##########################################

        /**
         * @param $hashId
         * @param $keyId
         * @param int $value
         *
         * @return bool|mixed
         */
        public function setKeyValue($hashId, $keyId, $value = 1)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getSetKeyValueQuery($hashId, $keyId, $value));

            if ($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $hashId
         * @param $pairs
         *
         * @return array
         */
        protected function _getSetKeyValueMultiQuery($hashId, $pairs)
        {
            $flat = [];

            foreach ($pairs as $keyId => $value)
            {
                $flat[] = $keyId;
                $flat[] = $value;
            }

            return array_merge(['HMSET'], [$hashId], $flat);
        }

        // ######################################

        /**
         * TODO: hotfix until we figure a way for using the protected method from RedisBase
         *
         * @param $hashId
         * @param $seconds
         *
         * @return array|bool
         */
        protected function _getSetExpireQuery($hashId, $seconds = -1)
        {
            if ($seconds > 0)
            {
                return ['EXPIRE', $hashId, $seconds];
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $hashId
         * @param $pairs
         * @param $expire
         *
         * @return array|bool
         */
        public function setKeyValueMulti($hashId, $pairs, $expire = -1)
        {
            $this
                ->_getRedisInstance()
                ->pipelineEnable(TRUE);

            $this
                ->_getRedisInstance()
                ->pipelineAddQueueItem($this->_getSetKeyValueMultiQuery($hashId, $pairs));

            $this
                ->_getRedisInstance()
                ->pipelineAddQueueItem($this->_getSetExpireQuery($hashId, $expire));

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
         * @param $hashId
         * @param $keyId
         *
         * @return array
         */
        protected function _getKeyValueQuery($hashId, $keyId)
        {
            return ['HGET', $hashId, $keyId];
        }

        // ##########################################

        /**
         * @param $hashId
         * @param $keyId
         *
         * @return bool|mixed
         */
        public function getKeyValue($hashId, $keyId)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getKeyValueQuery($hashId, $keyId));

            if ($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $hashId
         * @param $keyIds
         *
         * @return array
         */
        protected function _getKeyValueMultiQuery($hashId, $keyIds)
        {
            return array_merge(['HMGET', $hashId], $keyIds);
        }

        // ##########################################

        /**
         * @param $hashId
         * @param $keyIds
         *
         * @return bool|mixed
         */
        public function getKeyValueMulti($hashId, $keyIds)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getKeyValueMultiQuery($hashId, $keyIds));

            if ($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $hashId
         *
         * @return array
         */
        protected function _getKeysAndValuesQuery($hashId)
        {
            return array_merge(['HGETALL', $hashId]);
        }

        // ##########################################

        /**
         * @param $hashId
         *
         * @return array|bool
         */
        public function getKeysAndValues($hashId)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getKeysAndValuesQuery($hashId));

            if ($response != FALSE)
            {
                $hash = [];
                $responseLength = count($response);

                for ($i = 0; $i < $responseLength; $i += 2)
                {
                    $keyId = $response[$i];
                    $value = $response[$i + 1];
                    $hash[$keyId] = $value;
                }

                return $hash;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param array $hashIds
         *
         * @return array|bool
         */
        public function getKeysAndValuesMulti(array $hashIds)
        {
            $this
                ->_getRedisInstance()
                ->pipelineEnable(TRUE);

            foreach ($hashIds as $k)
            {
                $this
                    ->_getRedisInstance()
                    ->pipelineAddQueueItem($this->_getKeysAndValuesQuery($k));
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
         * @param $hashId
         * @param $hashIdId
         *
         * @return array
         */
        protected function _getHasKeyQuery($hashId, $hashIdId)
        {
            return ['HEXISTS', $hashId, $hashIdId];
        }

        // ##########################################

        /**
         * @param $hashId
         * @param $hashIdId
         *
         * @return bool|mixed
         */
        public function hasKey($hashId, $hashIdId)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getHasKeyQuery($hashId, $hashIdId));

            if ($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $hashId
         * @param $hashIdIds
         *
         * @return array
         */
        protected function _getDeleteKeyMultiQuery($hashId, array $hashIdIds)
        {
            return array_merge(['HDEL', $hashId], $hashIdIds);
        }

        // ##########################################

        /**
         * @param $hashId
         * @param $keyId
         *
         * @return bool|mixed
         */
        public function deleteKey($hashId, $keyId)
        {
            $response = $this->deleteKeyMulti($hashId, [$keyId]);

            if ($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $hashId
         * @param array $keyIds
         *
         * @return bool|mixed
         */
        public function deleteKeyMulti($hashId, array $keyIds)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getDeleteKeyMultiQuery($hashId, $keyIds));

            if ($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $hashId
         *
         * @return array
         */
        protected function _getKeysQuery($hashId)
        {
            return ['HKEYS', $hashId];
        }

        // ##########################################

        /**
         * @param $hashId
         *
         * @return bool|mixed
         */
        public function getKeys($hashId)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getKeysQuery($hashId));

            if ($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $hashId
         *
         * @return array
         */
        protected function _getValuesQuery($hashId)
        {
            return ['HVALS', $hashId];
        }

        // ##########################################

        /**
         * @param $hashId
         *
         * @return bool|mixed
         */
        public function getValues($hashId)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getValuesQuery($hashId));

            if ($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $hashId
         *
         * @return array
         */
        protected function _getKeysCountQuery($hashId)
        {
            return ['HLEN', $hashId];
        }

        // ##########################################

        /**
         * @param $hashId
         *
         * @return bool|mixed
         */
        public function getKeysCount($hashId)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getKeysCountQuery($hashId));

            if ($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $hashId
         * @param $value
         *
         * @return array
         */
        protected function _getIncrementByQuery($hashId, $value)
        {
            return ['HINCRBY', $hashId, $value];
        }

        // ##########################################

        /**
         * @param $hashId
         * @param $value
         *
         * @return bool|mixed
         */
        public function incrementBy($hashId, $value)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getIncrementByQuery($hashId, $value));

            if ($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $hashId
         * @param $value
         *
         * @return array
         */
        protected function _getDecrementByQuery($hashId, $value)
        {
            if ($value > 0)
            {
                $value = '-' . $value;
            }

            return ['HINCRBY', $hashId, $value];
        }

        // ##########################################

        /**
         * @param $hashId
         * @param $value
         *
         * @return bool|mixed
         */
        public function decrementBy($hashId, $value)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getDecrementByQuery($hashId, $value));

            if ($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }
    }