<?php

    namespace Simplon\Db\Library\Redis;

    class RedisSortedSetCommands
    {
        use RedisCommandsTrait;

        // ##########################################

        /**
         * @param $key
         * @param array $scoreValuePairs
         * @return array
         */
        protected function _getSortedSetAddValuesMultiQuery($key, array $scoreValuePairs)
        {
            $flat = [];

            foreach($scoreValuePairs as $pair)
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
         * @return bool|mixed
         */
        public function sortedSetAddValue($key, $score, $value)
        {
            $scoreValuePair = [
                (string)$score,
                (string)$value
            ];

            $response = $this
                ->_getRedisInstance()
                ->query($this->_getSortedSetAddValuesMultiQuery($key, [$scoreValuePair]));

            if($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $pairs
         * @return array|bool
         */
        public function sortedSetMultiAddValue($pairs)
        {
            $this
                ->_getRedisInstance()
                ->pipelineEnable(TRUE);

            foreach($pairs as $key => $setValues)
            {
                $scoreValuePair = [
                    (string)$setValues[0],
                    (string)$setValues[1]
                ];

                $this
                    ->_getRedisInstance()
                    ->pipelineAddQueueItem($this->_getSortedSetAddValuesMultiQuery($key, [$scoreValuePair]));
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
         * @param array $scoreValuePairs
         * @return bool|mixed
         */
        public function sortedSetAddValuesMulti($key, array $scoreValuePairs)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getSortedSetAddValuesMultiQuery($key, $scoreValuePairs));

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
        protected function _getSortedSetGetCountQuery($key)
        {
            return ['ZCARD', $key];
        }

        // ##########################################

        /**
         * @param $key
         * @return bool|mixed
         */
        public function sortedSetGetCount($key)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getSortedSetGetCountQuery($key));

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
        protected function _getSortedSetDeleteValueMultiQuery($key, array $values)
        {
            return array_merge(['ZREM', $key], $values);
        }

        // ##########################################

        /**
         * @param $key
         * @param $value
         * @return bool|mixed
         */
        public function sortedSetDeleteValue($key, $value)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getSortedSetDeleteValueMultiQuery($key, [$value]));

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
        public function sortedSetDeleteValueMulti($key, array $values)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getSortedSetDeleteValueMultiQuery($key, $values));

            if($response != FALSE)
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
         * @return array
         */
        protected function _getSortedSetGetRangeCountQuery($key, $scoreStart = '-inf', $scoreEnd = '+inf')
        {
            return ['ZCOUNT', $key, $scoreStart, $scoreEnd];
        }

        // ##########################################

        /**
         * @param $key
         * @param string $scoreStart
         * @param string $scoreEnd
         * @return bool|mixed
         */
        public function sortedSetGetRangeCount($key, $scoreStart = '-inf', $scoreEnd = '+inf')
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getSortedSetGetRangeCountQuery($key, $scoreStart, $scoreEnd));

            if($response != FALSE)
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
         * @return array
         */
        protected function _getSortedSetGetDataByRangeQuery($key, $indexStart, $indexEnd)
        {
            return ['ZRANGE', $key, (string)$indexStart, (string)$indexEnd];
        }

        // ##########################################

        /**
         * @param $key
         * @param $indexStart
         * @param $limit
         * @return bool|mixed
         */
        public function sortedSetGetDataByRange($key, $indexStart, $limit)
        {
            $limit = $this->_calcRangeLimit($indexStart, $limit);

            $response = $this
                ->_getRedisInstance()
                ->query($this->_getSortedSetGetDataByRangeQuery($key, $indexStart, $limit));

            if($response != FALSE)
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
         * @return array
         */
        protected function _getSortedSetReverseGetDataByRangeQuery($key, $indexStart, $indexEnd)
        {
            return ['ZREVRANGE', $key, (string)$indexStart, (string)$indexEnd];
        }

        // ##########################################

        /**
         * @param $key
         * @param $indexStart
         * @param $limit
         * @return bool|mixed
         */
        public function sortedSetReverseGetDataByRange($key, $indexStart, $limit)
        {
            $limit = $this->_calcRangeLimit($indexStart, $limit);

            $response = $this
                ->_getRedisInstance()
                ->query($this->_getSortedSetReverseGetDataByRangeQuery($key, $indexStart, $limit));

            if($response != FALSE)
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
         * @return array
         */
        protected function _getSortedSetGetDataByRangeWithScoresQuery($key, $scoreStart, $scoreEnd)
        {
            return ['ZRANGE', $key, $scoreStart, $scoreEnd, 'WITHSCORES'];
        }

        // ##########################################

        /**
         * @param $key
         * @param $scoreStart
         * @param $scoreEnd
         * @return array|bool
         */
        public function sortedSetGetDataByRangeWithScores($key, $scoreStart, $scoreEnd)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getSortedSetGetDataByRangeWithScoresQuery($key, $scoreStart, $scoreEnd));

            if($response != FALSE)
            {
                $setWithScores = [];
                $responseLength = count($response);

                for($i = 0; $i < $responseLength; $i += 2)
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
         * @return array
         */
        protected function _getSortedSetGetIndexByValueQuery($key, $value)
        {
            return ['ZRANK', $key, $value];
        }

        // ##########################################

        /**
         * @param $key
         * @param $value
         * @return bool|mixed
         */
        public function sortedSetGetIndexByValue($key, $value)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getSortedSetGetIndexByValueQuery($key, $value));

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
        protected function _getSortedSetReverseGetIndexByValueQuery($key, $value)
        {
            return ['ZREVRANK', $key, $value];
        }

        // ##########################################

        /**
         * @param $key
         * @param $value
         * @return bool|mixed
         */
        public function sortedSetReverseGetIndexByValue($key, $value)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getSortedSetReverseGetIndexByValueQuery($key, $value));

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
        protected function _getSortedSetGetScoreByValueQuery($key, $value)
        {
            return ['ZSCORE', $key, $value];
        }

        // ##########################################

        /**
         * @param $key
         * @param $value
         * @return bool|mixed
         */
        public function sortedSetGetScoreByValue($key, $value)
        {
            $response = $this
                ->_getRedisInstance()
                ->query($this->_getSortedSetGetScoreByValueQuery($key, $value));

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
        public function sortedSetDelete($key)
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
        public function sortedSetDeleteMulti(array $keys)
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