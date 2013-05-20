<?php

    namespace Simplon\Db\Library\Redis;

    class RedisListCommands
    {
        use RedisCommandsTrait;

        // ##########################################

        /**
         * @param $key
         * @param $values
         *
         * @return array
         */
        protected function _getUnshiftMultiQuery($key, array $values)
        {
            return array_merge(['LPUSH', $key], $values);
        }

        // ##########################################

        /**
         * @param $key
         * @param $value
         *
         * @return bool|mixed
         */
        public function unshiftValue($key, $value)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getUnshiftMultiQuery($key, [$value]));

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
        public function unshiftValueMulti($key, array $values)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getUnshiftMultiQuery($key, $values));

            if ($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $pairs
         *
         * @return array|bool
         */
        public function multiListUnshiftValue($pairs)
        {
            $this
                ->_getRedisInstance()
                ->pipelineEnable(TRUE);

            foreach ($pairs as $key => $value)
            {
                $this
                    ->_getRedisInstance()
                    ->pipelineAddQueueItem($this->_getUnshiftMultiQuery($key, [$value]));
            }

            $response = $this
                ->_getRedisInstance()
                ->pipelineExecute();

            if ($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $pairs
         *
         * @return array|bool
         */
        public function multiListUnshiftValueMulti($pairs)
        {
            $this
                ->_getRedisInstance()
                ->pipelineEnable(TRUE);

            foreach ($pairs as $key => $values)
            {
                $this
                    ->_getRedisInstance()
                    ->pipelineAddQueueItem($this->_getUnshiftMultiQuery($key, $values));
            }

            $response = $this
                ->_getRedisInstance()
                ->pipelineExecute();

            if ($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $key
         * @param $values
         *
         * @return array
         */
        protected function _getPushMultiQuery($key, array $values)
        {
            return array_merge(['RPUSH', $key], $values);
        }

        // ##########################################

        /**
         * @param $key
         * @param $value
         *
         * @return bool|mixed
         */
        public function pushValue($key, $value)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getPushMultiQuery($key, [$value]));

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
        public function pushValueMulti($key, array $values)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getUnshiftMultiQuery($key, $values));

            if ($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $pairs
         *
         * @return array|bool
         */
        public function multiListPushValue($pairs)
        {
            $this
                ->_getRedisInstance()
                ->pipelineEnable(TRUE);

            foreach ($pairs as $key => $value)
            {
                $this
                    ->_getRedisInstance()
                    ->pipelineAddQueueItem($this->_getPushMultiQuery($key, [$value]));
            }

            $response = $this
                ->_getRedisInstance()
                ->pipelineExecute();

            if ($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $pairs
         *
         * @return array|bool
         */
        public function multiListPushValueMulti($pairs)
        {
            $this
                ->_getRedisInstance()
                ->pipelineEnable(TRUE);

            foreach ($pairs as $key => $values)
            {
                $this
                    ->_getRedisInstance()
                    ->pipelineAddQueueItem($this->_getPushMultiQuery($key, $values));
            }

            $response = $this
                ->_getRedisInstance()
                ->pipelineExecute();

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
        protected function _getShiftQuery($key)
        {
            return ['LPOP', $key];
        }

        // ##########################################

        /**
         * @param $key
         *
         * @return bool|mixed
         */
        public function shift($key)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getShiftQuery($key));

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
        protected function _getPopQuery($key)
        {
            return ['RPOP', $key];
        }

        // ##########################################

        /**
         * @param $key
         *
         * @return bool|mixed
         */
        public function pop($key)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getPopQuery($key));

            if ($response != FALSE)
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
         *
         * @return array
         */
        protected function _getValuesByRangeQuery($key, $indexStart, $indexEnd)
        {
            return ['LRANGE', $key, $indexStart, $indexEnd];
        }

        // ##########################################

        /**
         * @param $key
         * @param $indexStart
         * @param $limit
         *
         * @return bool|mixed
         */
        public function getValuesByRange($key, $indexStart, $limit)
        {
            $limit = $this->_calcRangeLimit($indexStart, $limit);

            $response = $this
                ->_getRedisInstance()
                ->query($this->_getValuesByRangeQuery($key, $indexStart, $limit));

            if ($response != FALSE)
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
         *
         * @return array|bool
         */
        public function getMultiListValuesByRange(array $keys, $indexStart, $limit)
        {
            $this
                ->_getRedisInstance()
                ->pipelineEnable(TRUE);

            foreach ($keys as $key)
            {
                $limit = $this->_calcRangeLimit($indexStart, $limit);

                $this
                    ->_getRedisInstance()
                    ->pipelineAddQueueItem($this->_getValuesByRangeQuery($key, $indexStart, $limit));
            }

            $response = $this
                ->_getRedisInstance()
                ->pipelineExecute();

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
         * @return bool|mixed
         */
        public function getValues($key)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getValuesByRangeQuery($key, 0, -1));

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
        public function getMultiListValues(array $keys)
        {
            $this
                ->_getRedisInstance()
                ->pipelineEnable(TRUE);

            foreach ($keys as $key)
            {
                $this
                    ->_getRedisInstance()
                    ->pipelineAddQueueItem($this->_getValuesByRangeQuery($key, 0, -1));
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
        protected function _getCountQuery($key)
        {
            return ['LLEN', $key];
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
         * @param $key
         * @param $index
         * @param $value
         *
         * @return array
         */
        protected function _getSetAtIndexQuery($key, $index, $value)
        {
            return ['LSET', $key, $index, $value];
        }

        // ##########################################

        /**
         * @param $key
         * @param $index
         * @param $value
         *
         * @return bool|mixed
         */
        public function setAtIndex($key, $index, $value)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getSetAtIndexQuery($key, $index, $value));

            if ($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $key
         * @param $index
         *
         * @return array
         */
        protected function _getByIndexQuery($key, $index)
        {
            return ['LINDEX', $key, $index];
        }

        // ##########################################

        /**
         * @param $key
         * @param $index
         *
         * @return bool|mixed
         */
        public function getByIndex($key, $index)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getByIndexQuery($key, $index));

            if ($response != FALSE)
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
         *
         * @return array
         */
        protected function _getTrimQuery($key, $index, $value)
        {
            return ['LTRIM', $key, $index, $value];
        }

        // ##########################################

        /**
         * @param $key
         * @param $index
         * @param $value
         *
         * @return bool|mixed
         */
        public function trim($key, $index, $value)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getTrimQuery($key, $index, $value));

            if ($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }
    }