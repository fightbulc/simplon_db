<?php

    namespace Simplon\Db\Library\Redis;

    class RedisSetCommands
    {
        use RedisCommandsTrait;

        // ##########################################

        /**
         * @param $key
         * @param array $values
         * @return array
         */
        protected function _getSetAddMultiQuery($key, array $values)
        {
            return array_merge(['SADD', $key], $values);
        }

        // ##########################################

        /**
         * @param $key
         * @param $value
         * @return bool|mixed
         */
        public function setAdd($key, $value)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getSetAddMultiQuery($key, [$value]));

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $key
         * @param array $values
         * @return bool|mixed
         */
        public function setAddMulti($key, array $values)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getSetAddMultiQuery($key, $values));

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
        protected function _getSetGetCountQuery($key)
        {
            return ['SCARD', $key];
        }

        // ##########################################

        /**
         * @param $key
         * @return bool|mixed
         */
        public function setGetCount($key)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getSetGetCountQuery($key));

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $setKeyA
         * @param array $setKeyN
         * @return array
         */
        protected function _getSetGetDifferenceMultiQuery($setKeyA, array $setKeyN)
        {
            return array_merge(['SDIFF', $setKeyA], $setKeyN);
        }

        // ##########################################

        /**
         * @param $setKeyA
         * @param $setKeyB
         * @return bool|mixed
         */
        public function setDifference($setKeyA, $setKeyB)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getSetGetDifferenceMultiQuery($setKeyA, [$setKeyB]));

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $setKeyA
         * @param array $setKeyN
         * @return bool|mixed
         */
        public function setGetDifferenceMulti($setKeyA, array $setKeyN)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getSetGetDifferenceMultiQuery($setKeyA, $setKeyN));

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $storeSetKey
         * @param $setKeyA
         * @param array $setKeyN
         * @return array
         */
        protected function _getSetStoreDifferenceMultiQuery($storeSetKey, $setKeyA, array $setKeyN)
        {
            return array_merge(['SDIFFSTORE', $storeSetKey, $setKeyA], $setKeyN);
        }

        // ##########################################

        /**
         * @param $storeSetKey
         * @param $setKeyA
         * @param $setKeyB
         * @return bool|mixed
         */
        public function setStoreDifference($storeSetKey, $setKeyA, $setKeyB)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getSetStoreDifferenceMultiQuery($storeSetKey, $setKeyA, [$setKeyB]));

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $storeSetKey
         * @param $setKeyA
         * @param array $setKeyN
         * @return bool|mixed
         */
        public function setStoreDifferenceMulti($storeSetKey, $setKeyA, array $setKeyN)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getSetStoreDifferenceMultiQuery($storeSetKey, $setKeyA, $setKeyN));

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $setKeyA
         * @param array $setKeyN
         * @return array
         */
        protected function _getSetGetIntersectionMultiQuery($setKeyA, array $setKeyN)
        {
            return array_merge(['SINTER', $setKeyA], $setKeyN);
        }

        // ##########################################

        /**
         * @param $setKeyA
         * @param $setKeyB
         * @return bool|mixed
         */
        public function setIntersection($setKeyA, $setKeyB)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getSetGetIntersectionMultiQuery($setKeyA, [$setKeyB]));

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $setKeyA
         * @param array $setKeyN
         * @return bool|mixed
         */
        public function setGetIntersectionMulti($setKeyA, array $setKeyN)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getSetGetIntersectionMultiQuery($setKeyA, $setKeyN));

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $storeSetKey
         * @param $setKeyA
         * @param array $setKeyN
         * @return array
         */
        protected function _getSetStoreIntersectionMultiQuery($storeSetKey, $setKeyA, array $setKeyN)
        {
            return array_merge(['SINTERSTORE', $storeSetKey, $setKeyA], $setKeyN);
        }

        // ##########################################

        /**
         * @param $storeSetKey
         * @param $setKeyA
         * @param $setKeyB
         * @return bool|mixed
         */
        public function setStoreIntersection($storeSetKey, $setKeyA, $setKeyB)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getSetStoreIntersectionMultiQuery($storeSetKey, $setKeyA, [$setKeyB]));

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $storeSetKey
         * @param $setKeyA
         * @param array $setKeyN
         * @return bool|mixed
         */
        public function setStoreIntersectionMulti($storeSetKey, $setKeyA, array $setKeyN)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getSetStoreIntersectionMultiQuery($storeSetKey, $setKeyA, $setKeyN));

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $key
         * @param $value
         * @return array
         */
        protected function _getSetValueExistsQuery($key, $value)
        {
            return ['SISMEMBER', $key, $value];
        }

        // ##########################################

        /**
         * @param $key
         * @param $value
         * @return bool|mixed
         */
        public function setValueExists($key, $value)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getSetValueExistsQuery($key, $value));

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
        protected function _getSetGetValuesQuery($key)
        {
            return ['SMEMBERS', $key];
        }

        // ##########################################

        /**
         * @param $key
         * @return bool|mixed
         */
        public function setGetData($key)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getSetGetValuesQuery($key));

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param array $keys
         * @return array|bool
         */
        public function setGetDataMulti(array $keys)
        {
            $this
                ->_getRedisInstance()
                ->pipelineEnable(TRUE);

            foreach($keys as $key)
            {
                $this
                    ->_getRedisInstance()
                    ->pipelineAddQueueItem($this->_getSetGetValuesQuery($key));
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
         * @param $setKeySource
         * @param $setKeyDestination
         * @param $value
         * @return array
         */
        protected function _getSetMoveValueQuery($setKeySource, $setKeyDestination, $value)
        {
            return ['SMOVE', $setKeySource, $setKeyDestination, $value];
        }

        // ##########################################

        /**
         * @param $setKeySource
         * @param $setKeyDestination
         * @param $value
         * @return bool|mixed
         */
        public function setMoveValue($setKeySource, $setKeyDestination, $value)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getSetMoveValueQuery($setKeySource, $setKeyDestination, $value));

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
        protected function _getSetPopRandomValueQuery($key)
        {
            return ['SPOP', $key];
        }

        // ##########################################

        /**
         * @param $key
         * @return bool|mixed
         */
        public function setPopRandomValue($key)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getSetPopRandomValueQuery($key));

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $key
         * @param int $amount
         * @return array
         */
        protected function _getSetGetRandomValuesQuery($key, $amount = 1)
        {
            return ['SRANDMEMBER', $key, $amount];
        }

        // ##########################################

        /**
         * @param $key
         * @param int $amount
         * @return bool|mixed
         */
        public function setGetRandomValues($key, $amount = 1)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getSetGetRandomValuesQuery($key, $amount));

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $key
         * @param array $values
         * @return array
         */
        protected function _getSetDeleteValueMultiQuery($key, array $values)
        {
            return array_merge(['SREM', $key], $values);
        }

        // ##########################################

        /**
         * @param $key
         * @param $value
         * @return bool|mixed
         */
        public function setDeleteValue($key, $value)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getSetDeleteValueMultiQuery($key, [$value]));

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $key
         * @param array $values
         * @return bool|mixed
         */
        public function setDeleteValueMulti($key, array $values)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getSetDeleteValueMultiQuery($key, $values));

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $setKeyA
         * @param array $setKeysN
         * @return array
         */
        protected function _getSetGetMergeMultiQuery($setKeyA, array $setKeysN)
        {
            return array_merge(['SUNION', $setKeyA], $setKeysN);
        }

        // ##########################################

        /**
         * @param $setKeyA
         * @param $setKeyB
         * @return bool|mixed
         */
        public function setMerge($setKeyA, $setKeyB)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getSetGetMergeMultiQuery($setKeyA, [$setKeyB]));

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $setKeyA
         * @param array $setKeyN
         * @return bool|mixed
         */
        public function setGetMergeMulti($setKeyA, array $setKeyN)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getSetGetMergeMultiQuery($setKeyA, $setKeyN));

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $setKeyA
         * @param array $setKeysN
         * @return array
         */
        protected function _getSetStoreMergeMultiQuery($setKeyA, array $setKeysN)
        {
            return array_merge(['SUNIONSTORE', $setKeyA], $setKeysN);
        }

        // ##########################################

        /**
         * @param $setKeyA
         * @param $setKeyB
         * @return bool|mixed
         */
        public function setStoreMerge($setKeyA, $setKeyB)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getSetStoreMergeMultiQuery($setKeyA, [$setKeyB]));

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $setKeyA
         * @param array $setKeyN
         * @return bool|mixed
         */
        public function setStoreMergeMulti($setKeyA, array $setKeyN)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getSetStoreMergeMultiQuery($setKeyA, $setKeyN));

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
        public function setDelete($key)
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
        public function setDeleteMulti(array $keys)
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