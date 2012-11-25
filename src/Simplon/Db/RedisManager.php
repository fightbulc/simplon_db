<?php

  namespace Simplon\Db;

  use Simplon\Db\Library\Redis;

  class RedisManager
  {
    /** @var Redis */
    protected $_redisInstance;

    // ########################################

    /**
     * @param Library\Redis $instance
     */
    public function __construct(Redis $instance)
    {
      $this->_redisInstance = $instance;
    }

    // ########################################

    /**
     * @return Library\Redis
     */
    public function getInstance()
    {
      return $this->_redisInstance;
    }
  }
