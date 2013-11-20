<?php

    namespace Simplon\Db\Library\Redis;

    class RedisSetCommands
    {
        use RedisCommandsTrait;

        // ##########################################

        /**
         * @param $key
         * @param array $values
         *
         * @return array
         */
        protected function _getAddMultiQuery($key, array $values)
        {
            return array_merge(['SADD', $key], $values);
        }

        // ##########################################

        /**
         * @param $key
         * @param $value
         * @param bool $expireSeconds
         *
         * @return bool|mixed
         */
        public function add($key, $value, $expireSeconds = FALSE)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getAddMultiQuery($key, [$value]));

            if ($response != FALSE)
            {
                if ($expireSeconds !== FALSE)
                {
                    $this->_getRedisInstance()
                        ->keySetExpire($key, $expireSeconds);
                }

                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $key
         * @param array $values
         * @param bool $expireSeconds
         *
         * @return bool|mixed
         */
        public function addMulti($key, array $values, $expireSeconds = FALSE)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getAddMultiQuery($key, $values));

            if ($response != FALSE)
            {
                if ($expireSeconds !== FALSE)
                {
                    $this->_getRedisInstance()
                        ->keySetExpire($key, $expireSeconds);
                }

                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $key
         * @param $value
         *
         * @return bool|mixed
         */
        public function resetAdd($key, $value)
        {
            // reset
            $this->delete($key);

            // add new value
            return $this->add($key, $value);
        }

        // ##########################################

        /**
         * @param $key
         * @param array $values
         *
         * @return bool|mixed
         */
        public function resetAddMulti($key, array $values)
        {
            // reset
            $this->delete($key);

            // add new data
            return $this->addMulti($key, $values);
        }

        // ##########################################

        /**
         * @param $key
         *
         * @return array
         */
        protected function _getCountQuery($key)
        {
            return ['SCARD', $key];
        }

        // ##########################################

        /**
         * @param $key
         *
         * @return bool|mixed
         */
        public function getCount($key)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getCountQuery($key));

            if ($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $setKeyA
         * @param array $setKeyN
         *
         * @return array
         */
        protected function _getDifferenceMultiQuery($setKeyA, array $setKeyN)
        {
            return array_merge(['SDIFF', $setKeyA], $setKeyN);
        }

        // ##########################################

        /**
         * @param $setKeyA
         * @param $setKeyB
         *
         * @return bool|mixed
         */
        public function getDifference($setKeyA, $setKeyB)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getDifferenceMultiQuery($setKeyA, [$setKeyB]));

            if ($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $setKeyA
         * @param array $setKeyN
         *
         * @return bool|mixed
         */
        public function getDifferenceMulti($setKeyA, array $setKeyN)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getDifferenceMultiQuery($setKeyA, $setKeyN));

            if ($response != FALSE)
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
         *
         * @return array
         */
        protected function _storeDifferenceMultiQuery($storeSetKey, $setKeyA, array $setKeyN)
        {
            return array_merge(['SDIFFSTORE', $storeSetKey, $setKeyA], $setKeyN);
        }

        // ##########################################

        /**
         * @param $storeSetKey
         * @param $setKeyA
         * @param $setKeyB
         *
         * @return bool|mixed
         */
        public function storeDifference($storeSetKey, $setKeyA, $setKeyB)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_storeDifferenceMultiQuery($storeSetKey, $setKeyA, [$setKeyB]));

            if ($response != FALSE)
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
         * @param bool $expireSeconds
         *
         * @return bool|mixed
         */
        public function storeDifferenceMulti($storeSetKey, $setKeyA, array $setKeyN, $expireSeconds = FALSE)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_storeDifferenceMultiQuery($storeSetKey, $setKeyA, $setKeyN));

            if ($response != FALSE)
            {
                if ($expireSeconds !== FALSE)
                {
                    $this->_getRedisInstance()
                        ->keySetExpire($storeSetKey, $expireSeconds);
                }

                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $setKeyA
         * @param array $setKeyN
         *
         * @return array
         */
        protected function _getIntersectionMultiQuery($setKeyA, array $setKeyN)
        {
            return array_merge(['SINTER', $setKeyA], $setKeyN);
        }

        // ##########################################

        /**
         * @param $setKeyA
         * @param $setKeyB
         *
         * @return bool|mixed
         */
        public function getIntersection($setKeyA, $setKeyB)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getIntersectionMultiQuery($setKeyA, [$setKeyB]));

            if ($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $setKeyA
         * @param array $setKeyN
         *
         * @return bool|mixed
         */
        public function getIntersectionMulti($setKeyA, array $setKeyN)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getIntersectionMultiQuery($setKeyA, $setKeyN));

            if ($response != FALSE)
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
         *
         * @return array
         */
        protected function _storeIntersectionMultiQuery($storeSetKey, $setKeyA, array $setKeyN)
        {
            return array_merge(['SINTERSTORE', $storeSetKey, $setKeyA], $setKeyN);
        }

        // ##########################################

        /**
         * @param $storeSetKey
         * @param $setKeyA
         * @param $setKeyB
         * @param bool $expireSeconds
         *
         * @return bool|mixed
         */
        public function storeIntersection($storeSetKey, $setKeyA, $setKeyB, $expireSeconds = FALSE)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_storeIntersectionMultiQuery($storeSetKey, $setKeyA, [$setKeyB]));

            if ($response != FALSE)
            {
                if ($expireSeconds !== FALSE)
                {
                    $this->_getRedisInstance()
                        ->keySetExpire($storeSetKey, $expireSeconds);
                }

                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $storeSetKey
         * @param $setKeyA
         * @param array $setKeyN
         * @param bool $expireSeconds
         *
         * @return bool|mixed
         */
        public function storeIntersectionMulti($storeSetKey, $setKeyA, array $setKeyN, $expireSeconds = FALSE)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_storeIntersectionMultiQuery($storeSetKey, $setKeyA, $setKeyN));

            if ($response != FALSE)
            {
                if ($expireSeconds !== FALSE)
                {
                    $this->_getRedisInstance()
                        ->keySetExpire($storeSetKey, $expireSeconds);
                }

                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $key
         * @param $value
         *
         * @return array
         */
        protected function _getHasValueQuery($key, $value)
        {
            return ['SISMEMBER', $key, $value];
        }

        // ##########################################

        /**
         * @param $key
         * @param $value
         *
         * @return bool|mixed
         */
        public function hasValue($key, $value)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getHasValueQuery($key, $value));

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
        protected function _getValuesQuery($key)
        {
            return ['SMEMBERS', $key];
        }

        // ##########################################

        /**
         * @param $key
         *
         * @return bool|mixed
         */
        public function getValues($key)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getValuesQuery($key));

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
         * @return array|bool
         */
        public function getValuesMulti(array $keys)
        {
            $this
                ->_getRedisInstance()
                ->pipelineEnable(TRUE);

            foreach ($keys as $key)
            {
                $this
                    ->_getRedisInstance()
                    ->pipelineAddQueueItem($this->_getValuesQuery($key));
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
         * @param $setKeySource
         * @param $setKeyDestination
         * @param $value
         *
         * @return array
         */
        protected function _getMoveValueQuery($setKeySource, $setKeyDestination, $value)
        {
            return ['SMOVE', $setKeySource, $setKeyDestination, $value];
        }

        // ##########################################

        /**
         * @param $setKeySource
         * @param $setKeyDestination
         * @param $value
         *
         * @return bool|mixed
         */
        public function moveValue($setKeySource, $setKeyDestination, $value)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getMoveValueQuery($setKeySource, $setKeyDestination, $value));

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
        protected function _getPopRandomValueQuery($key)
        {
            return ['SPOP', $key];
        }

        // ##########################################

        /**
         * @param $key
         *
         * @return bool|mixed
         */
        public function popRandomValue($key)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getPopRandomValueQuery($key));

            if ($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $key
         * @param int $amount
         *
         * @return array
         */
        protected function _getRandomValuesQuery($key, $amount = 1)
        {
            return ['SRANDMEMBER', $key, $amount];
        }

        // ##########################################

        /**
         * @param $key
         * @param int $amount
         *
         * @return bool|mixed
         */
        public function getRandomValues($key, $amount = 1)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getRandomValuesQuery($key, $amount));

            if ($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $key
         * @param array $values
         *
         * @return array
         */
        protected function _getDeleteValueMultiQuery($key, array $values)
        {
            return array_merge(['SREM', $key], $values);
        }

        // ##########################################

        /**
         * @param $key
         * @param $value
         *
         * @return bool|mixed
         */
        public function deleteValue($key, $value)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getDeleteValueMultiQuery($key, [$value]));

            if ($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $key
         * @param array $values
         *
         * @return bool|mixed
         */
        public function deleteValueMulti($key, array $values)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getDeleteValueMultiQuery($key, $values));

            if ($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $setKeyA
         * @param array $setKeysN
         *
         * @return array
         */
        protected function _getMergeMultiQuery($setKeyA, array $setKeysN)
        {
            return array_merge(['SUNION', $setKeyA], $setKeysN);
        }

        // ##########################################

        /**
         * @param $setKeyA
         * @param $setKeyB
         *
         * @return bool|mixed
         */
        public function merge($setKeyA, $setKeyB)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getMergeMultiQuery($setKeyA, [$setKeyB]));

            if ($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $setKeyA
         * @param array $setKeyN
         *
         * @return bool|mixed
         */
        public function mergeMulti($setKeyA, array $setKeyN)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getMergeMultiQuery($setKeyA, $setKeyN));

            if ($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $storeKey
         * @param array $mergingKeys
         *
         * @return array
         */
        protected function _getStoreMergeMultiQuery($storeKey, array $mergingKeys)
        {
            return array_merge(['SUNIONSTORE', $storeKey], $mergingKeys);
        }

        // ##########################################

        /**
         * @param $storeKey
         * @param array $mergingKeys
         * @param bool $expireSeconds
         *
         * @return bool|mixed
         */
        public function storeMerge($storeKey, array $mergingKeys, $expireSeconds = FALSE)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getStoreMergeMultiQuery($storeKey, $mergingKeys));

            if ($response != FALSE)
            {
                if ($expireSeconds !== FALSE)
                {
                    $this->_getRedisInstance()
                        ->keySetExpire($storeKey, $expireSeconds);
                }

                return $response;
            }

            return FALSE;
        }
    }