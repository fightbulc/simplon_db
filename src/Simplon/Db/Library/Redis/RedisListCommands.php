<?php

    namespace Simplon\Db\Library\Redis;

    class RedisListCommands
    {
        use RedisCommandsTrait;

        // ##########################################

        /**
         * @param $key
         * @param $values
         * @return array
         */
        protected function _getListUnshiftMultiQuery($key, array $values)
        {
            return array_merge(['LPUSH', $key], $values);
        }

        // ##########################################

        /**
         * @param $key
         * @param $value
         * @return bool|mixed
         */
        public function listUnshiftValue($key, $value)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getListUnshiftMultiQuery($key, [$value]));

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
        public function listUnshiftValuesMulti($key, array $values)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getListUnshiftMultiQuery($key, $values));

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $listPairs
         * @return array|bool
         */
        public function listMultiUnshiftValue($listPairs)
        {
            $this
                ->_getRedisInstance()
                ->pipelineEnable(TRUE);

            foreach($listPairs as $listKey => $listValue)
            {
                $this
                    ->_getRedisInstance()
                    ->pipelineAddQueueItem($this->_getListUnshiftMultiQuery($listKey, [$listValue]));
            }

            $response = $this
                ->_getRedisInstance()
                ->pipelineExecute();

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $listPairs
         * @return array|bool
         */
        public function listMultiUnshiftValuesMulti($listPairs)
        {
            $this
                ->_getRedisInstance()
                ->pipelineEnable(TRUE);

            foreach($listPairs as $listKey => $listValues)
            {
                $this
                    ->_getRedisInstance()
                    ->pipelineAddQueueItem($this->_getListUnshiftMultiQuery($listKey, $listValues));
            }

            $response = $this
                ->_getRedisInstance()
                ->pipelineExecute();

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $key
         * @param $values
         * @return array
         */
        protected function _getListPushMultiQuery($key, array $values)
        {
            return array_merge(['RPUSH', $key], $values);
        }

        // ##########################################

        /**
         * @param $key
         * @param $value
         * @return bool|mixed
         */
        public function listPushValue($key, $value)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getListPushMultiQuery($key, [$value]));

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
        public function listPushValuesMulti($key, array $values)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getListUnshiftMultiQuery($key, $values));

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $listPairs
         * @return array|bool
         */
        public function listMultiPushValue($listPairs)
        {
            $this
                ->_getRedisInstance()
                ->pipelineEnable(TRUE);

            foreach($listPairs as $listKey => $listValue)
            {
                $this
                    ->_getRedisInstance()
                    ->pipelineAddQueueItem($this->_getListPushMultiQuery($listKey, [$listValue]));
            }

            $response = $this
                ->_getRedisInstance()
                ->pipelineExecute();

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $listPairs
         * @return array|bool
         */
        public function listMultiPushValuesMulti($listPairs)
        {
            $this
                ->_getRedisInstance()
                ->pipelineEnable(TRUE);

            foreach($listPairs as $listKey => $listValues)
            {
                $this
                    ->_getRedisInstance()
                    ->pipelineAddQueueItem($this->_getListPushMultiQuery($listKey, $listValues));
            }

            $response = $this
                ->_getRedisInstance()
                ->pipelineExecute();

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
        protected function _getListShiftQuery($key)
        {
            return ['LPOP', $key];
        }

        // ##########################################

        /**
         * @param $key
         * @return bool|mixed
         */
        public function listShift($key)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getListShiftQuery($key));

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
        protected function _getListPopQuery($key)
        {
            return ['RPOP', $key];
        }

        // ##########################################

        /**
         * @param $key
         * @return bool|mixed
         */
        public function listPop($key)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getListPopQuery($key));

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $key
         * @param $indexStart
         * @param $indexEnd
         * @return array
         */
        protected function _getListGetValuesByRangeQuery($key, $indexStart, $indexEnd)
        {
            return ['LRANGE', $key, (string)$indexStart, (string)$indexEnd];
        }

        // ##########################################

        /**
         * @param $key
         * @param $indexStart
         * @param $limit
         * @return bool|mixed
         */
        public function listGetDataByRange($key, $indexStart, $limit)
        {
            $limit = $this->_calcRangeLimit($indexStart, $limit);

            $response = $this
                ->_getRedisInstance()
                ->query($this->_getListGetValuesByRangeQuery($key, $indexStart, $limit));

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param array $keys
         * @param $indexStart
         * @param $limit
         * @return array|bool
         */
        public function listMultiGetDataByRange(array $keys, $indexStart, $limit)
        {
            $this
                ->_getRedisInstance()
                ->pipelineEnable(TRUE);

            foreach($keys as $key)
            {
                $limit = $this->_calcRangeLimit($indexStart, $limit);

                $this
                    ->_getRedisInstance()
                    ->pipelineAddQueueItem($this->_getListGetValuesByRangeQuery($key, $indexStart, $limit));
            }

            $response = $this
                ->_getRedisInstance()
                ->pipelineExecute();

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
        public function listGetData($key)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getListGetValuesByRangeQuery($key, 0, - 1));

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
        public function listMultiGetData(array $keys)
        {
            $this
                ->_getRedisInstance()
                ->pipelineEnable(TRUE);

            foreach($keys as $key)
            {
                $this
                    ->_getRedisInstance()
                    ->pipelineAddQueueItem($this->_getListGetValuesByRangeQuery($key, 0, - 1));
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
         * @return array
         */
        protected function _getListGetCountQuery($key)
        {
            return ['LLEN', $key];
        }

        // ##########################################

        /**
         * @param $key
         * @return bool|mixed
         */
        public function listGetCount($key)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getListGetCountQuery($key));

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $key
         * @param $index
         * @param $value
         * @return array
         */
        protected function _getListSetAtIndexQuery($key, $index, $value)
        {
            return ['LSET', $key, $index, $value];
        }

        // ##########################################

        /**
         * @param $key
         * @param $index
         * @param $value
         * @return bool|mixed
         */
        public function listSetAtIndex($key, $index, $value)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getListSetAtIndexQuery($key, $index, $value));

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $key
         * @param $index
         * @return array
         */
        protected function _getListGetByIndexQuery($key, $index)
        {
            return ['LINDEX', $key, $index];
        }

        // ##########################################

        /**
         * @param $key
         * @param $index
         * @return bool|mixed
         */
        public function listGetByIndex($key, $index)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getListGetByIndexQuery($key, $index));

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $key
         * @param $index
         * @param $value
         * @return array
         */
        protected function _getListTrimQuery($key, $index, $value)
        {
            return ['LTRIM', $key, $index, $value];
        }

        // ##########################################

        /**
         * @param $key
         * @param $index
         * @param $value
         * @return bool|mixed
         */
        public function listTrim($key, $index, $value)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getListTrimQuery($key, $index, $value));

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
        public function listDelete($key)
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
        public function listDeleteMulti(array $keys)
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