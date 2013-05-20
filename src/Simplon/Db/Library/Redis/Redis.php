<?php

    namespace Simplon\Db\Library\Redis;

    class Redis
    {
        /** @var Phpiredis */
        protected $_redisInstance;

        /** @var bool */
        protected $_enablePipeline = FALSE;

        /** @var array */
        protected $_pipelineQueue = [];

        /** @var array */
        protected $_responseQueue = [];

        // ##########################################

        /**
         * @param $host
         * @param $dbId
         * @param int $port
         * @param null $password
         *
         * @throws \Exception
         */
        public function __construct($host, $dbId, $port = 6379, $password = NULL)
        {
            // redis connector
            $this->_redisInstance = phpiredis_connect($host, $port);

            // select db
            $this->dbSelect($dbId);

            // auth
            if (!is_null($password))
            {
                if ($this->dbAuth($password) != 'OK')
                {
                    throw new \Exception('DB: authentication failed.', 401);
                }
            }
        }

        // ##########################################

        /**
         * @return Phpiredis
         */
        protected function _getRedisInstance()
        {
            return $this->_redisInstance;
        }

        // ##########################################

        /**
         * @param $cmdArgs
         *
         * @return mixed
         */
        protected function _ensureStringCommands($cmdArgs)
        {
            foreach ($cmdArgs as $index => $command)
            {
                $cmdArgs[$index] = strval($command);
            }

            return $cmdArgs;
        }

        // ##########################################

        /**
         * @param $cmdArgs
         *
         * @return bool|mixed
         */
        public function query($cmdArgs)
        {
            // no connection || no commands?
            if ($this->_getRedisInstance() === FALSE || $cmdArgs === FALSE)
            {
                return FALSE;
            }

            // force strings only
            $cmdArgs = $this->_ensureStringCommands($cmdArgs);

            // query redis
            $response = phpiredis_command_bs($this->_getRedisInstance(), $cmdArgs);

            if (is_array($response) || substr($response, 0, 2) !== 'ERR')
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param bool $use
         *
         * @return $this
         */
        public function pipelineEnable($use = TRUE)
        {
            $this->_enablePipeline = $use === TRUE ? TRUE : FALSE;

            return $this;
        }

        // ##########################################

        /**
         * @return array
         */
        public function pipelineExecute()
        {
            // no connection?
            if ($this->_getRedisInstance() === FALSE)
            {
                return FALSE;
            }

            $_pipeline = $this->_pipelineGetQueue();

            $requestResponsesMulti = [
                'errors'    => [],
                'responses' => [],
            ];

            // run through all commands
            $responsesMulti = phpiredis_multi_command_bs($this->_getRedisInstance(), $_pipeline);

            // build request/response array
            foreach ($responsesMulti as $index => $response)
            {
                $_requestKey = json_encode($_pipeline[$index]);

                if (is_array($response) || substr($response, 0, 3) !== 'ERR')
                {
                    $requestResponsesMulti['responses'][$_requestKey] = $response;
                    continue;
                }

                $requestResponsesMulti['error'][$_requestKey] = $response;
            }

            // reset request/response queues
            $this->_pipelineResetQueue();

            // disable pipeline
            $this->pipelineEnable(FALSE);

            return $requestResponsesMulti;
        }

        // ##########################################

        /**
         * @return bool
         */
        public function pipelineIsEnabled()
        {
            return $this->_enablePipeline;
        }

        // ##########################################

        /**
         * @return array
         */
        protected function _pipelineGetQueue()
        {
            return $this->_pipelineQueue;
        }

        // ##########################################

        /**
         * @param $cmdArgs
         *
         * @return $this
         */
        public function pipelineAddQueueItem($cmdArgs)
        {
            if ($cmdArgs !== FALSE)
            {
                // force strings only
                $cmdArgs = $this->_ensureStringCommands($cmdArgs);

                $this->_pipelineQueue[] = $cmdArgs;
            }

            return $this;
        }

        // ##########################################

        /**
         * @return $this
         */
        protected function _pipelineResetQueue()
        {
            // reset queue
            $this->_pipelineQueue = [];

            return $this;
        }

        // ##########################################

        /**
         * @param $dbId
         *
         * @return array
         */
        protected function _getDbSelectQuery($dbId)
        {
            return ['SELECT', (string)$dbId];
        }

        // ##########################################

        /**
         * @param $dbId
         *
         * @return bool|mixed
         */
        public function dbSelect($dbId)
        {
            $response = $this->query($this->_getDbSelectQuery($dbId));

            if ($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $password
         *
         * @return array
         */
        protected function _getDbAuthQuery($password)
        {
            return ['AUTH', $password];
        }

        // ##########################################

        /**
         * @param $password
         *
         * @return bool|mixed
         */
        public function dbAuth($password)
        {
            $response = $this->query($this->_getDbAuthQuery($password));

            if ($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @return array
         */
        protected function _getDbFlushQuery()
        {
            return ['FLUSHDB'];
        }

        // ##########################################

        /**
         * @param bool $confirm
         *
         * @return bool|mixed
         */
        public function dbFlush($confirm = FALSE)
        {
            if ($confirm === TRUE)
            {
                $response = $this->query($this->_getDbFlushQuery());

                if ($response != FALSE)
                {
                    return $response;
                }
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param array $keys
         *
         * @return array
         */
        protected function _getKeyDeleteMultiQuery(array $keys)
        {
            return array_merge(['DEL'], $keys);
        }

        // ##########################################

        /**
         * @param $key
         *
         * @return bool|mixed
         */
        public function keyDelete($key)
        {
            $response = $this->query($this->_getKeyDeleteMultiQuery([$key]));

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
         * @return bool|mixed
         */
        public function keyDeleteMulti(array $keys)
        {
            $response = $this->query($this->_getKeyDeleteMultiQuery($keys));

            if ($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $key
         * @param $seconds
         *
         * @return array|bool
         */
        protected function _getKeySetExpireQuery($key, $seconds = -1)
        {
            if ($seconds > 0)
            {
                return ['EXPIRE', $key, (string)$seconds];
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $key
         * @param $seconds
         *
         * @return bool|mixed
         */
        public function keySetExpire($key, $seconds = -1)
        {
            $response = $this->query($this->_getKeySetExpireQuery($key, $seconds));

            if ($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param array $keys
         * @param $seconds
         *
         * @return array|bool
         */
        public function keySetExpireMulti(array $keys, $seconds = -1)
        {
            if ($seconds > 0)
            {
                $this->pipelineEnable(TRUE);

                foreach ($keys as $key)
                {
                    $this->pipelineAddQueueItem($this->_getKeySetExpireQuery($key, $seconds));
                }

                $response = $this->pipelineExecute();

                if ($response != FALSE)
                {
                    return $response;
                }
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @return array
         */
        protected function _getKeysGetCount()
        {
            return ['DBSIZE'];
        }

        // ##########################################

        /**
         * @return bool|mixed
         */
        public function keysGetCount()
        {
            $response = $this->query($this->_getKeysGetCount());

            if ($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }

        // ##########################################

        /**
         * @param $pattern
         *
         * @return array
         */
        protected function _getKeysGetByPatternQuery($pattern)
        {
            return ['KEYS', $pattern];
        }

        // ##########################################

        /**
         * @param $pattern
         *
         * @return bool|mixed
         */
        public function keysGetByPattern($pattern)
        {
            $response = $this->query($this->_getKeysGetByPatternQuery($pattern));

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
        protected function _getKeyExistsQuery($key)
        {
            return ['EXISTS', $key];
        }

        // ##########################################

        /**
         * @param $key
         *
         * @return bool|mixed
         */
        public function keyExists($key)
        {
            $response = $this->query($this->_getKeyExistsQuery($key));

            if ($response != FALSE)
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
        protected function _getKeyGetExpirationQuery($key)
        {
            return ['TTL', $key];
        }

        // ##########################################

        /**
         * @param $key
         *
         * @return bool|mixed
         */
        public function keyGetExpiration($key)
        {
            $response = $this->query($this->_getKeyGetExpirationQuery($key));

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
        protected function _getKeyRenameQuery($key)
        {
            return ['RENAMENX', $key];
        }

        // ##########################################

        /**
         * @param $key
         *
         * @return bool|mixed
         */
        public function keyRename($key)
        {
            $response = $this->query($this->_getKeyRenameQuery($key));

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
        protected function _getKeyRemoveExpirationQuery($key)
        {
            return ['PERSIST', $key];
        }

        // ##########################################

        /**
         * @param $key
         *
         * @return bool|mixed
         */
        public function keyRemoveExpiration($key)
        {
            $response = $this->query($this->_getKeyRemoveExpirationQuery($key));

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
        protected function _getKeyIncrementQuery($key)
        {
            return ['INCR', $key];
        }

        // ##########################################

        /**
         * @param $key
         *
         * @return bool|mixed
         */
        public function keyIncrement($key)
        {
            $response = $this->query($this->_getKeyIncrementQuery($key));

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
        protected function _getKeyIncrementByQuery($key, $value = 1)
        {
            return ['INCRBY', $key, (string)$value];
        }

        // ##########################################

        /**
         * @param $key
         * @param int $value
         *
         * @return bool|mixed
         */
        public function keyIncrementBy($key, $value = 1)
        {
            $response = $this->query($this->_getKeyIncrementByQuery($key, $value));

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
        protected function _getKeyDecrementQuery($key)
        {
            return ['DECR', $key];
        }

        // ##########################################

        /**
         * @param $key
         *
         * @return bool|mixed
         */
        public function keyDecrement($key)
        {
            $response = $this->query($this->_getKeyDecrementQuery($key));

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
        protected function _getKeyDecrementByQuery($key, $value = 1)
        {
            return ['DECRBY', $key, (string)$value];
        }

        // ##########################################

        /**
         * @param $key
         * @param int $value
         *
         * @return bool|mixed
         */
        public function keyDecrementBy($key, $value = 1)
        {
            $response = $this->query($this->_getKeyDecrementByQuery($key, $value));

            if ($response != FALSE)
            {
                return $response;
            }

            return FALSE;
        }
    }