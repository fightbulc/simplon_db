<?php

    namespace Simplon\Db;

    use Simplon\Db\Library\Redis\Redis;
    use Simplon\Db\Library\Redis\RedisBitCommands;
    use Simplon\Db\Library\Redis\RedisHashCommands;
    use Simplon\Db\Library\Redis\RedisListCommands;
    use Simplon\Db\Library\Redis\RedisSetCommands;
    use Simplon\Db\Library\Redis\RedisSortedSetCommands;
    use Simplon\Db\Library\Redis\RedisStringCommands;

    class RedisManager
    {
        /** @var \Simplon\Db\Library\Redis\Redis */
        private $_redisInstance;

        /** @var RedisBitCommands */
        private $_redisBitCommandsInstance;

        /** @var RedisHashCommands */
        private $_redisHashCommandsInstance;

        /** @var RedisListCommands */
        private $_redisListCommandsInstance;

        /** @var RedisSetCommands */
        private $_redisSetCommandsInstance;

        /** @var RedisSortedSetCommands */
        private $_redisSortedSetCommandsInstance;

        /** @var RedisStringCommands */
        private $_redisStringCommandsInstance;

        // ######################################

        /**
         * @param Redis $redisBaseInstance
         */
        public function __construct(Redis $redisBaseInstance)
        {
            $this->_redisInstance = $redisBaseInstance;
        }

        // ######################################

        /**
         * @return Redis
         */
        public function getRedisInstance()
        {
            return $this->_redisInstance;
        }

        // ######################################

        /**
         * @return RedisBitCommands
         */
        public function getRedisBitCommandsInstance()
        {
            if(! $this->_redisBitCommandsInstance)
            {
                $this->_redisBitCommandsInstance = new RedisBitCommands($this->getRedisInstance());
            }

            return $this->_redisBitCommandsInstance;
        }

        // ######################################

        /**
         * @return RedisHashCommands
         */
        public function getRedisHashCommandsInstance()
        {
            if(! $this->_redisHashCommandsInstance)
            {
                $this->_redisHashCommandsInstance = new RedisHashCommands($this->getRedisInstance());
            }

            return $this->_redisHashCommandsInstance;
        }

        // ######################################

        /**
         * @return RedisListCommands
         */
        public function getRedisListCommandsInstance()
        {
            if(! $this->_redisListCommandsInstance)
            {
                $this->_redisListCommandsInstance = new RedisListCommands($this->getRedisInstance());
            }

            return $this->_redisListCommandsInstance;
        }

        // ######################################

        /**
         * @return RedisSetCommands
         */
        public function getRedisSetCommandsInstance()
        {
            if(! $this->_redisSetCommandsInstance)
            {
                $this->_redisSetCommandsInstance = new RedisSetCommands($this->getRedisInstance());
            }

            return $this->_redisSetCommandsInstance;
        }

        // ######################################

        /**
         * @return RedisSortedSetCommands
         */
        public function getRedisSortedSetCommandsInstance()
        {
            if(! $this->_redisSortedSetCommandsInstance)
            {
                $this->_redisSortedSetCommandsInstance = new RedisSortedSetCommands($this->getRedisInstance());
            }

            return $this->_redisSortedSetCommandsInstance;
        }

        // ######################################

        /**
         * @return RedisStringCommands
         */
        public function getRedisStringCommandsInstance()
        {
            if(! $this->_redisStringCommandsInstance)
            {
                $this->_redisStringCommandsInstance = new RedisStringCommands($this->getRedisInstance());
            }

            return $this->_redisStringCommandsInstance;
        }
    }
