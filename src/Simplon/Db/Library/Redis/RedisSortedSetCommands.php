<?php

    namespace Simplon\Db\Library\Redis;

    class RedisSortedSetCommands
    {
        use RedisCommandsTrait;

        // ##########################################

        /**
         * @param $key
         * @param array $scoreValuePairs
         *
         * @return array
         */
        protected function _getAddValuesMultiQuery($key, array $scoreValuePairs)
        {
            $flat = [];

            foreach ($scoreValuePairs as $pair)
            {
                $flat[] = $pair[0];
                $flat[] = $pair[1];
            }

            return array_merge(['ZADD', $key], $flat);
        }

        // ##########################################

        /**
         * @param $key
         * @param $score
         * @param $value
         *
         * @return bool|mixed
         */
        public function addValue($key, $score, $value)
        {
            $scoreValuePair = [$score, $value];

            $response = $this
                ->_getRedisInstance()
                ->query($this->_getAddValuesMultiQuery($key, [$scoreValuePair]));

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
        public function multiAddValue($pairs)
        {
            $this
                ->_getRedisInstance()
                ->pipelineEnable(TRUE);

            foreach ($pairs as $key => $setValues)
            {
                $scoreValuePair = [$setValues[0], $setValues[1]];

                $this
                    ->_getRedisInstance()
                    ->pipelineAddQueueItem($this->_getAddValuesMultiQuery($key, [$scoreValuePair]));
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
         * @param array $scoreValuePairs
         *
         * @return bool|mixed
         */
        public function addValuesMulti($key, array $scoreValuePairs)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getAddValuesMultiQuery($key, $scoreValuePairs));

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
        protected function _getCountQuery($key)
        {
            return ['ZCARD', $key];
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
         * @param array $values
         *
         * @return array
         */
        protected function _getDeleteValueMultiQuery($key, array $values)
        {
            return array_merge(['ZREM', $key], $values);
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
         * @param $key
         * @param string $scoreStart
         * @param string $scoreEnd
         *
         * @return array
         */
        protected function _getRangeCountQuery($key, $scoreStart = '-inf', $scoreEnd = '+inf')
        {
            return ['ZCOUNT', $key, $scoreStart, $scoreEnd];
        }

        // ##########################################

        /**
         * @param $key
         * @param string $scoreStart
         * @param string $scoreEnd
         *
         * @return bool|mixed
         */
        public function getRangeCount($key, $scoreStart = '-inf', $scoreEnd = '+inf')
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getRangeCountQuery($key, $scoreStart, $scoreEnd));

            if ($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $key
         * @param string $indexStart
         * @param string $indexEnd
         *
         * @return array
         */
        protected function _getValuesByRangeQuery($key, $indexStart, $indexEnd)
        {
            return ['ZRANGE', $key, $indexStart, $indexEnd];
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
         * @param $key
         * @param string $indexStart
         * @param string $indexEnd
         *
         * @return array
         */
        protected function _getValuesByRangeReverseQuery($key, $indexStart, $indexEnd)
        {
            return ['ZREVRANGE', $key, $indexStart, $indexEnd];
        }

        // ##########################################

        /**
         * @param $key
         * @param $indexStart
         * @param $limit
         *
         * @return bool|mixed
         */
        public function getValuesByRangeReverse($key, $indexStart, $limit)
        {
            $limit = $this->_calcRangeLimit($indexStart, $limit);

            $response = $this
                ->_getRedisInstance()
                ->query($this->_getValuesByRangeReverseQuery($key, $indexStart, $limit));

            if ($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $key
         * @param string $scoreStart
         * @param string $scoreEnd
         *
         * @return array
         */
        protected function _getValuesByRangeWithScoresQuery($key, $scoreStart, $scoreEnd)
        {
            return ['ZRANGE', $key, $scoreStart, $scoreEnd, 'WITHSCORES'];
        }

        // ##########################################

        /**
         * @param $key
         * @param $scoreStart
         * @param $scoreEnd
         *
         * @return array|bool
         */
        public function getValuesByRangeWithScores($key, $scoreStart, $scoreEnd)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getValuesByRangeWithScoresQuery($key, $scoreStart, $scoreEnd));

            if ($response != FALSE)
            {
                $setWithScores = [];
                $responseLength = count($response);

                for ($i = 0; $i < $responseLength; $i += 2)
                {
                    $setWithScores[] = [
                        'score' => $response[$i],
                        'value' => $response[$i + 1],
                    ];
                }

                return $setWithScores;
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
        protected function _getIndexByValueQuery($key, $value)
        {
            return ['ZRANK', $key, $value];
        }

        // ##########################################

        /**
         * @param $key
         * @param $value
         *
         * @return bool|mixed
         */
        public function getIndexByValue($key, $value)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getIndexByValueQuery($key, $value));

            if ($response != FALSE)
            {
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
        protected function _getIndexByValueReverseQuery($key, $value)
        {
            return ['ZREVRANK', $key, $value];
        }

        // ##########################################

        /**
         * @param $key
         * @param $value
         *
         * @return bool|mixed
         */
        public function getIndexByValueReverse($key, $value)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getIndexByValueReverseQuery($key, $value));

            if ($response != FALSE)
            {
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
        protected function _getScoreByValueQuery($key, $value)
        {
            return ['ZSCORE', $key, $value];
        }

        // ##########################################

        /**
         * @param $key
         * @param $value
         *
         * @return bool|mixed
         */
        public function getScoreByValue($key, $value)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getScoreByValueQuery($key, $value));

            if ($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }
    }