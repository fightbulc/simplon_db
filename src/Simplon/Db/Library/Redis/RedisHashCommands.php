<?php

    namespace Simplon\Db\Library\Redis;

    class RedisHashCommands
    {
        use RedisCommandsTrait;

        // ##########################################

        /**
         * @param $hashKey
         * @param $fieldId
         * @param $value
         * @return array
         */
        protected function _getHashSetFieldQuery($hashKey, $fieldId, $value)
        {
            return ['HSET', $hashKey, $fieldId, $value];
        }

        // ##########################################

        /**
         * @param $hashKey
         * @param $fieldId
         * @param int $value
         * @return bool|mixed
         */
        public function hashSetField($hashKey, $fieldId, $value = 1)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getHashSetFieldQuery($hashKey, $fieldId, (string)$value));

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $hashKey
         * @param $pairs
         * @return array
         */
        protected function _getHashSetFieldsMultiQuery($hashKey, $pairs)
        {
            $flat = [];

            foreach($pairs as $fieldId => $value)
            {
                $flat[] = $fieldId;
                $flat[] = (string)$value;
            }

            return array_merge(['HMSET'], [$hashKey], $flat);
        }

        // ######################################
        // TODO: hotfix until we figure a way for using the protected method from RedisBase

        protected function _getKeySetExpireQuery($key, $seconds = -1)
        {
            if($seconds > 0)
            {
                return ['EXPIRE', $key, (string)$seconds];
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $hashKey
         * @param $pairs
         * @param $expire
         * @return array|bool
         */
        public function hashSetFieldsMulti($hashKey, $pairs, $expire = -1)
        {
            $this
                ->_getRedisInstance()
                ->pipelineEnable(TRUE);

            $this
                ->_getRedisInstance()
                ->pipelineAddQueueItem($this->_getHashSetFieldsMultiQuery($hashKey, $pairs));

            $this
                ->_getRedisInstance()
                ->pipelineAddQueueItem($this->_getKeySetExpireQuery($hashKey, $expire));

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
         * @param $hashKey
         * @param $fieldId
         * @return array
         */
        protected function _getHashGetFieldQuery($hashKey, $fieldId)
        {
            return ['HGET', $hashKey, $fieldId];
        }

        // ##########################################

        /**
         * @param $hashKey
         * @param $fieldId
         * @return bool|mixed
         */
        public function hashGetField($hashKey, $fieldId)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getHashGetFieldQuery($hashKey, $fieldId));

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $hashKey
         * @param $fieldIds
         * @return array
         */
        protected function _getHashGetFieldsMultiQuery($hashKey, $fieldIds)
        {
            return array_merge(['HMGET', $hashKey], $fieldIds);
        }

        // ##########################################

        /**
         * @param $hashKey
         * @param $fieldIds
         * @return bool|mixed
         */
        public function hashGetFieldsMulti($hashKey, $fieldIds)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getHashGetFieldsMultiQuery($hashKey, $fieldIds));

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $hashKey
         * @return array
         */
        protected function _getHashDataQuery($hashKey)
        {
            return array_merge(['HGETALL', $hashKey]);
        }

        // ##########################################

        /**
         * @param $hashKey
         * @return array|bool
         */
        public function hashGetData($hashKey)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getHashDataQuery($hashKey));

            if($response != FALSE)
            {
                $hash = [];
                $responseLength = count($response);

                for($i = 0; $i < $responseLength; $i += 2)
                {
                    $fieldId = $response[$i];
                    $value = $response[$i + 1];
                    $hash[$fieldId] = $value;
                }

                return $hash;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param array $hashKeys
         * @return array|bool
         */
        public function hashGetDataMulti(array $hashKeys)
        {
            $this
                ->_getRedisInstance()
                ->pipelineEnable(TRUE);

            foreach($hashKeys as $hashKey)
            {
                $this
                    ->_getRedisInstance()
                    ->pipelineAddQueueItem($this->_getHashDataQuery($hashKey));
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
         * @param $hashKey
         * @param $fieldId
         * @return array
         */
        protected function _getHashFieldExistsQuery($hashKey, $fieldId)
        {
            return ['HEXISTS', $hashKey, $fieldId];
        }

        // ##########################################

        /**
         * @param $hashKey
         * @param $fieldId
         * @return bool|mixed
         */
        public function hashFieldExists($hashKey, $fieldId)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getHashFieldExistsQuery($hashKey, $fieldId));

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $hashKey
         * @param $fieldIds
         * @return array
         */
        protected function _getHashDeleteFieldMultiQuery($hashKey, array $fieldIds)
        {
            return array_merge(['HDEL', $hashKey], $fieldIds);
        }

        // ##########################################

        /**
         * @param $hashKey
         * @param $fieldId
         * @return bool|mixed
         */
        public function hashDeleteField($hashKey, $fieldId)
        {
            $response = $this->hashDeleteFieldMulti($hashKey, [$fieldId]);

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $hashKey
         * @param array $fieldIds
         * @return bool|mixed
         */
        public function hashDeleteFieldMulti($hashKey, array $fieldIds)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getHashDeleteFieldMultiQuery($hashKey, $fieldIds));

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $hashKey
         * @return array
         */
        protected function _getHashGetKeysQuery($hashKey)
        {
            return ['HKEYS', $hashKey];
        }

        // ##########################################

        /**
         * @param $hashKey
         * @return bool|mixed
         */
        public function hashGetKeys($hashKey)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getHashGetKeysQuery($hashKey));

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $hashKey
         * @return array
         */
        protected function _getHashGetValuesQuery($hashKey)
        {
            return ['HVALS', $hashKey];
        }

        // ##########################################

        /**
         * @param $hashKey
         * @return bool|mixed
         */
        public function hashGetValues($hashKey)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getHashGetValuesQuery($hashKey));

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $hashKey
         * @return array
         */
        protected function _getHashGetFieldsCountQuery($hashKey)
        {
            return ['HLEN', $hashKey];
        }

        // ##########################################

        /**
         * @param $hashKey
         * @return bool|mixed
         */
        public function hashGetFieldsCount($hashKey)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getHashGetFieldsCountQuery($hashKey));

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $hashKey
         * @param $value
         * @return array
         */
        protected function _getHashIncrementByQuery($hashKey, $value)
        {
            return ['HINCRBY', $hashKey, $value];
        }

        // ##########################################

        /**
         * @param $hashKey
         * @param $value
         * @return bool|mixed
         */
        public function hashIncrementBy($hashKey, $value)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getHashIncrementByQuery($hashKey, $value));

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $hashKey
         * @param $value
         * @return array
         */
        protected function _getHashDecrementByQuery($hashKey, $value)
        {
            if($value > 0)
            {
                $value = '-' . $value;
            }

            return ['HINCRBY', $hashKey, $value];
        }

        // ##########################################

        /**
         * @param $hashKey
         * @param $value
         * @return bool|mixed
         */
        public function hashDecrementBy($hashKey, $value)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getHashDecrementByQuery($hashKey, $value));

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
        public function hashDelete($key)
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
        public function hashDeleteMulti(array $keys)
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