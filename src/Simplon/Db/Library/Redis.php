<?php

  namespace Simplon\Db\Library;

  class Redis
  {
    /** @var Phpiredis */
    private $_redisInstance;

    /** @var bool */
    private $_enablePipeline = FALSE;

    /** @var array */
    private $_pipelineQueue = array();

    /** @var array */
    private $_responseQueue = array();

    // ##########################################

    /**
     * @param $host
     * @param $dbId
     * @param int $port
     * @param null $password
     * @throws \Exception
     */
    public function __construct($host, $dbId, $port = 6379, $password = NULL)
    {
      // redis connector
      $this->_setRedisInstance(phpiredis_connect($host, $port));

      // select db
      $this->dbSelect($dbId);

      // auth
      if(! is_null($password))
      {
        if($this->dbAuth($password) != 'OK')
        {
          throw new \Exception('DB: authentication failed.', 401);
        }
      }
    }

    // ##########################################

    /**
     * @param $redisInstance
     * @return Redis
     */
    protected function _setRedisInstance($redisInstance)
    {
      $this->_redisInstance = $redisInstance;

      return $this;
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
     * @param $commandArgs
     * @return array|bool|Redis
     */
    protected function _query($commandArgs)
    {
      if($commandArgs === FALSE)
      {
        return FALSE;
      }

      $this->_addPipelineQueue($commandArgs);

      if($this->_isEnabledPipeline())
      {
        return $this;
      }

      return $this->execute();
    }

    // ##########################################

    /**
     * @param $commandArgsMulti
     * @return array|bool
     */
    protected function _queryMulti($commandArgsMulti)
    {
      $this->pipelineEnable(TRUE);

      foreach($commandArgsMulti as $commandArgs)
      {
        if($commandArgs !== FALSE)
        {
          $this->_addPipelineQueue($commandArgs);
        }
      }

      return $this->execute();
    }

    // ##########################################

    /**
     * @return array|bool
     */
    public function execute()
    {
      $response = FALSE;
      $_pipelineQueue = $this->_getPipelineQueue();

      // run through all commands
      while($commandArgs = array_shift($_pipelineQueue))
      {
        $response = phpiredis_command_bs($this->_getRedisInstance(), $commandArgs);

        // cache response
        $this->_addResponseQueue($response);
      }

      // return all pipe responses
      if($this->_isEnabledPipeline())
      {
        // disable pipeline
        $this->pipelineEnable(FALSE);

        return $this->_getResponseQueue();
      }

      // false if empty
      if(empty($response))
      {
        return FALSE;
      }

      return $response;
    }

    // ##########################################

    /**
     * @param bool $use
     * @return Redis
     */
    public function pipelineEnable($use = TRUE)
    {
      $this->_enablePipeline = $use === TRUE ? TRUE : FALSE;

      // reset request/response queues
      $this->_resetQueues();

      return $this;
    }

    // ##########################################

    /**
     * @return bool
     */
    protected function _isEnabledPipeline()
    {
      return $this->_enablePipeline;
    }

    // ##########################################

    /**
     * @return array
     */
    protected function _getPipelineQueue()
    {
      return $this->_pipelineQueue;
    }

    // ##########################################

    /**
     * @param $cmdArgs
     * @return Redis
     */
    protected function _addPipelineQueue($cmdArgs)
    {
      $this->_pipelineQueue[] = $cmdArgs;

      return $this;
    }

    // ##########################################

    /**
     * @param $response
     * @return Redis
     */
    protected function _addResponseQueue($response)
    {
      $this->_responseQueue[] = $response;

      return $this;
    }

    // ##########################################

    /**
     * @return array
     */
    protected function _getResponseQueue()
    {
      return $this->_responseQueue;
    }

    // ##########################################

    /**
     * @return Redis
     */
    protected function _resetQueues()
    {
      // reset queue
      $this->_pipelineQueue = array();

      // reset responses
      $this->_responseQueue = array();

      return $this;
    }

    // ##########################################

    /**
     * @param $dbId
     * @return Redis
     */
    private function _getDbSelectQuery($dbId)
    {
      return array('SELECT', (string)$dbId);
    }

    // ##########################################

    /**
     * @param $dbId
     * @return Redis
     */
    public function dbSelect($dbId)
    {
      $response = $this->_query($this->_getDbSelectQuery($dbId));

      return $this;
    }

    // ##########################################

    /**
     * @param $password
     * @return Redis
     */
    private function _getDbAuthQuery($password)
    {
      return array('AUTH', $password);
    }

    // ##########################################

    /**
     * @param $password
     * @return Redis
     */
    public function dbAuth($password)
    {
      return $this->_query($this->_getDbAuthQuery($password));
    }

    // ##########################################

    /**
     * @return array
     */
    private function _getDbFlushQuery()
    {
      return ['FLUSHDB'];
    }

    // ##########################################

    /**
     * @param bool $confirm
     * @return Redis
     */
    public function dbFlush($confirm = FALSE)
    {
      if($confirm === TRUE)
      {
        $response = $this->_query($this->_getDbFlushQuery());
      }

      return $this;
    }

    // ##########################################

    /**
     * @param array $keys
     * @return array
     */
    private function _getKeyDeleteMultiQuery(array $keys)
    {
      return array_merge(['DEL'], $keys);
    }

    // ##########################################

    /**
     * @param $key
     * @return mixed
     */
    public function keyDelete($key)
    {
      $response = $this->_query($this->_getKeyDeleteMultiQuery([$key]));

      return $this;
    }

    // ##########################################

    /**
     * @param $keys
     * @return mixed
     */
    public function keyDeleteMulti(array $keys)
    {
      $response = $this->_query($this->_getKeyDeleteMultiQuery($keys));

      return $this;
    }

    // ##########################################

    /**
     * @param $key
     * @param $seconds
     * @return string
     */
    private function _getKeySetExpireQuery($key, $seconds = -1)
    {
      if($seconds > 0)
      {
        return array('EXPIRE', $key, (string)$seconds);
      }

      return FALSE;
    }

    // ##########################################

    /**
     * @param $key
     * @param $seconds
     * @return Redis
     */
    public function keySetExpire($key, $seconds = -1)
    {
      $response = $this->_query($this->_getKeySetExpireQuery($key, $seconds));

      return $this;
    }

    // ##########################################

    /**
     * @param array $keys
     * @param $seconds
     * @return array|bool
     */
    private function _getKeySetExpireMultiQuery(array $keys, $seconds = -1)
    {
      if($seconds > 0)
      {
        $cmds = [];

        foreach($keys as $key)
        {
          $cmds[] = $this->_getKeySetExpireQuery($key, $seconds);
        }

        return $cmds;
      }

      return FALSE;
    }

    // ##########################################

    /**
     * @param array $keys
     * @param $seconds
     * @return Redis
     */
    public function keySetExpireMulti(array $keys, $seconds = -1)
    {
      $response = $this->_queryMulti($this->_getKeySetExpireMultiQuery($keys, $seconds));

      return $this;
    }

    // ##########################################

    /**
     * @return array
     */
    private function _getKeysGetCount()
    {
      return ['DBSIZE'];
    }

    // ##########################################

    /**
     * @return array|mixed|Redis
     */
    public function keysGetCount()
    {
      return $this->_query($this->_getKeysGetCount());
    }

    // ##########################################

    /**
     * @param $pattern
     * @return array
     */
    private function _getKeysGetByPatternQuery($pattern)
    {
      return ['KEYS', $pattern];
    }

    // ##########################################

    /**
     * @param $pattern
     * @return array|bool|Redis
     */
    public function keysGetByPattern($pattern)
    {
      return $this->_query($this->_getKeysGetByPatternQuery($pattern));
    }

    // ##########################################

    /**
     * @param $key
     * @return array
     */
    private function _getKeyExistsQuery($key)
    {
      return ['EXISTS', $key];
    }

    // ##########################################

    /**
     * @param $key
     * @return array|mixed|Redis
     */
    public function keyExists($key)
    {
      return $this->_query($this->_getKeyExistsQuery($key));
    }

    // ##########################################

    /**
     * @param $key
     * @return array
     */
    private function _getKeyGetExpirationQuery($key)
    {
      return ['TTL', $key];
    }

    // ##########################################

    /**
     * @param $key
     * @return array|mixed|Redis
     */
    public function keyGetExpiration($key)
    {
      return $this->_query($this->_getKeyGetExpirationQuery($key));
    }

    // ##########################################

    /**
     * @param $key
     * @return array
     */
    private function _getKeyRenameQuery($key)
    {
      return ['RENAMENX', $key];
    }

    // ##########################################

    /**
     * @param $key
     * @return array|mixed|Redis
     */
    public function keyRename($key)
    {
      return $this->_query($this->_getKeyRenameQuery($key));
    }

    // ##########################################

    /**
     * @param $key
     * @return array
     */
    private function _getKeyRemoveExpirationQuery($key)
    {
      return ['PERSIST', $key];
    }

    // ##########################################

    /**
     * @param $key
     * @return array|mixed|Redis
     */
    public function keyRemoveExpiration($key)
    {
      return $this->_query($this->_getKeyRemoveExpirationQuery($key));
    }

    // ##########################################

    /**
     * @param $key
     * @param $value
     * @return array
     */
    private function _getKeyIncrementByQuery($key, $value = 1)
    {
      return ['INCRBY', $key, $value];
    }

    // ##########################################

    /**
     * @param $key
     * @param int $value
     * @return Redis
     */
    public function keyIncrementBy($key, $value = 1)
    {
      $response = $this->_query($this->_getKeyIncrementByQuery($key, $value));

      return $this;
    }

    // ##########################################

    /**
     * @param $key
     * @param $value
     * @return array
     */
    private function _getKeyDecrementByQuery($key, $value = 1)
    {
      return ['DECRBY', $key, $value];
    }

    // ##########################################

    /**
     * @param $key
     * @param $value
     * @return array|mixed|Redis
     */
    public function keyDecrementBy($key, $value = 1)
    {
      return $this->_query($this->_getKeyDecrementByQuery($key, $value));
    }

    // ##########################################

    /**
     * @param $key
     * @param $offset
     * @param $value
     * @return array
     */
    private function _getBitSetQuery($key, $offset, $value)
    {
      return array('SETBIT', $key, $offset, $value);
    }

    // ##########################################

    /**
     * @param $key
     * @param $offset
     * @param $value
     * @return Redis
     */
    public function bitSet($key, $offset, $value)
    {
      $response = $this->_query($this->_getBitSetQuery($key, $offset, $value));

      return $this;
    }

    // ##########################################

    /**
     * @param $key
     * @param $offset
     * @return array
     */
    private function _getBitGetQuery($key, $offset)
    {
      return array('GETBIT', $key, $offset);
    }

    // ##########################################

    /**
     * @param $key
     * @param $offset
     * @return array|bool|Redis
     */
    public function bitGet($key, $offset)
    {
      return $this->_query($this->_getBitGetQuery($key, $offset));
    }

    // ##########################################

    /**
     * @param $key
     * @return array
     */
    private function _getBitGetAllQuery($key)
    {
      return ['GET', $key];
    }

    // ##########################################

    /**
     * @param $key
     * @return array|bool|\Simplon\Db\Library\Redis
     */
    public function bitGetAll($key)
    {
      return $this->_query($this->_getBitGetAllQuery($key));
    }

    // ##########################################

    /**
     * @param $key
     * @param int $start
     * @param $end
     * @return array
     */
    private function _getBitCountQuery($key, $start = 0, $end = -1)
    {
      return array('BITCOUNT', $key, $start, $end);
    }

    // ##########################################

    /**
     * @param $key
     * @param int $start
     * @param $end
     * @return array|bool|Redis
     */
    public function bitCount($key, $start = 0, $end = -1)
    {
      return $this->_query($this->_getBitCountQuery($key, $start, $end));
    }

    // ##########################################

    /**
     * @param $key
     * @return Redis
     */
    public function bitDelete($key)
    {
      $response = $this->keyDelete($key);

      return $this;
    }

    // ##########################################

    /**
     * @param array $keys
     * @return Redis
     */
    public function bitDeleteMulti(array $keys)
    {
      $response = $this->keyDeleteMulti($keys);

      return $this;
    }

    // ##########################################

    /**
     * @param $key
     * @param $value
     * @param $expire
     * @return array
     */
    private function _getStringSetQuery($key, $value, $expire = -1)
    {
      if($expire > 0)
      {
        return array('SETEX', $key, (string)$expire, $value);
      }

      return array('SET', $key, $value);
    }

    // ##########################################

    /**
     * @param $key
     * @param $value
     * @param $expire
     * @return Redis
     */
    public function stringSet($key, $value, $expire = -1)
    {
      $response = $this->_query($this->_getStringSetQuery($key, $value, $expire));

      return $this;
    }

    // ##########################################

    /**
     * @param array $pairs
     * @param $expire
     * @return Redis
     */
    public function stringSetMulti(array $pairs, $expire = -1)
    {
      $this->pipelineEnable(TRUE);

      foreach($pairs as $key => $value)
      {
        $this->stringSet($key, $value, $expire);
      }

      $response = $this->execute();

      return $this;
    }

    // ##########################################

    /**
     * @param $key
     * @return string
     */
    private function _getStringGetQuery($key)
    {
      return array('GET', $key);
    }

    // ##########################################

    /**
     * @param $key
     * @return mixed
     */
    public function stringGet($key)
    {
      return $this->_query($this->_getStringGetQuery($key));
    }

    // ##########################################

    /**
     * @param array $keys
     * @return array
     */
    private function _getStringGetMultiQuery(array $keys)
    {
      return array_merge(['MGET'], $keys);
    }

    // ##########################################

    /**
     * @param $keys
     * @return mixed
     */
    public function stringGetMulti(array $keys)
    {
      return $this->_query($this->_getStringGetMultiQuery($keys));
    }

    // ##########################################

    /**
     * @param $key
     * @return Redis
     */
    public function stringDelete($key)
    {
      $response = $this->keyDelete($key);

      return $this;
    }

    // ##########################################

    /**
     * @param array $keys
     * @return Redis
     */
    public function stringDeleteMulti(array $keys)
    {
      $response = $this->keyDeleteMulti($keys);

      return $this;
    }

    // ##########################################

    /**
     * @param $key
     * @param $values
     * @return array
     */
    private function _getListUnshiftMultiQuery($key, array $values)
    {
      return array_merge(['LPUSH', $key], $values);
    }

    // ##########################################

    /**
     * @param $key
     * @param $value
     * @return Redis
     */
    public function listUnshift($key, $value)
    {
      $response = $this->_query($this->_getListUnshiftMultiQuery($key, [$value]));

      return $this;
    }

    // ##########################################

    /**
     * @param $key
     * @param array $values
     * @return Redis
     */
    public function listUnshiftMulti($key, array $values)
    {
      $response = $this->_query($this->_getListUnshiftMultiQuery($key, $values));

      return $this;
    }

    // ##########################################

    /**
     * @param $key
     * @param $values
     * @return array
     */
    private function _getListPushMultiQuery($key, array $values)
    {
      return array_merge(['RPUSH', $key], $values);
    }

    // ##########################################

    /**
     * @param $key
     * @param $value
     * @return Redis
     */
    public function listPush($key, $value)
    {
      $response = $this->_query($this->_getListPushMultiQuery($key, [$value]));

      return $this;
    }

    // ##########################################

    /**
     * @param $key
     * @param array $values
     * @return Redis
     */
    public function listPushMulti($key, array $values)
    {
      $response = $this->_query($this->_getListUnshiftMultiQuery($key, $values));

      return $this;
    }

    // ##########################################

    /**
     * @param $key
     * @return array
     */
    private function _getListShiftQuery($key)
    {
      return ['LPOP', $key];
    }

    // ##########################################

    /**
     * @param $key
     * @return array|bool|Redis
     */
    public function listShift($key)
    {
      return $this->_query($this->_getListShiftQuery($key));
    }

    // ##########################################

    /**
     * @param $key
     * @return array
     */
    private function _getListPopQuery($key)
    {
      return ['RPOP', $key];
    }

    // ##########################################

    /**
     * @param $key
     * @return array|bool|Redis
     */
    public function listPop($key)
    {
      return $this->_query($this->_getListPopQuery($key));
    }

    // ##########################################

    /**
     * @param $key
     * @param $start
     * @param $end
     * @return array
     */
    private function _getListGetValuesByRangeQuery($key, $start, $end)
    {
      return ['LRANGE', $key, (string)$start, (string)$end];
    }

    // ##########################################

    /**
     * @param $key
     * @param $start
     * @param $end
     * @return array|bool|Redis
     */
    public function listGetValuesByRange($key, $start, $end)
    {
      return $this->_query($this->_getListGetValuesByRangeQuery($key, $start, $end));
    }

    // ##########################################

    /**
     * @param $key
     * @return array|bool|Redis
     */
    public function listGetAllValues($key)
    {
      return $this->_query($this->_getListGetValuesByRangeQuery($key, 0, - 1));
    }

    // ##########################################

    /**
     * @param $key
     * @return array
     */
    private function _getListGetCountQuery($key)
    {
      return ['LLEN', $key];
    }

    // ##########################################

    /**
     * @param $key
     * @return array|bool|Redis
     */
    public function listGetCount($key)
    {
      return $this->_query($this->_getListGetCountQuery($key));
    }

    // ##########################################

    /**
     * @param $key
     * @param $index
     * @param $value
     * @return array
     */
    private function _getListSetAtIndexQuery($key, $index, $value)
    {
      return ['LSET', $key, $index, $value];
    }

    // ##########################################

    /**
     * @param $key
     * @param $index
     * @param $value
     * @return Redis
     */
    public function listSetAtIndex($key, $index, $value)
    {
      $response = $this->_query($this->_getListSetAtIndexQuery($key, $index, $value));

      return $this;
    }

    // ##########################################

    /**
     * @param $key
     * @param $index
     * @param $value
     * @return array
     */
    private function _getListGetFromIndexQuery($key, $index, $value)
    {
      return ['LINDEX', $key, $index, $value];
    }

    // ##########################################

    /**
     * @param $key
     * @param $index
     * @param $value
     * @return Redis
     */
    public function listGetFromIndex($key, $index, $value)
    {
      $response = $this->_query($this->_getListGetFromIndexQuery($key, $index, $value));

      return $this;
    }

    // ##########################################

    /**
     * @param $key
     * @param $index
     * @param $value
     * @return array
     */
    private function _getListTrimQuery($key, $index, $value)
    {
      return ['LTRIM', $key, $index, $value];
    }

    // ##########################################

    /**
     * @param $key
     * @param $index
     * @param $value
     * @return Redis
     */
    public function listTrim($key, $index, $value)
    {
      $response = $this->_query($this->_getListTrimQuery($key, $index, $value));

      return $this;
    }

    // ##########################################

    /**
     * @param $key
     * @return Redis
     */
    public function listDelete($key)
    {
      $response = $this->keyDelete($key);

      return $this;
    }

    // ##########################################

    /**
     * @param array $keys
     * @return Redis
     */
    public function listDeleteMulti(array $keys)
    {
      $response = $this->keyDeleteMulti($keys);

      return $this;
    }

    // ##########################################

    /**
     * @param $hashKey
     * @param $fieldId
     * @param $value
     * @return array
     */
    private function _getHashSetFieldQuery($hashKey, $fieldId, $value)
    {
      return ['HSET', $hashKey, $fieldId, $value];
    }

    // ##########################################

    /**
     * @param $hashKey
     * @param $fieldId
     * @param int $value
     * @return Redis
     */
    public function hashSetField($hashKey, $fieldId, $value = 1)
    {
      $response = $this->_query($this->_getHashSetFieldQuery($hashKey, $fieldId, $value));

      return $this;
    }

    // ##########################################

    /**
     * @param $hashKey
     * @param $pairs
     * @return array
     */
    private function _getHashSetFieldsMultiQuery($hashKey, $pairs)
    {
      $flat = [];

      foreach($pairs as $fieldId => $value)
      {
        $flat[] = $fieldId;
        $flat[] = (string)$value;
      }

      return array_merge(['HMSET'], [$hashKey], $flat);
    }

    // ##########################################

    /**
     * @param $hashKey
     * @param $pairs
     * @param $expire
     * @return Redis
     */
    public function hashSetFieldsMulti($hashKey, $pairs, $expire = -1)
    {
      $cmds = [
        $this->_getHashSetFieldsMultiQuery($hashKey, $pairs),
        $this->_getKeySetExpireQuery($hashKey, $expire),
      ];

      $response = $this->_queryMulti($cmds);

      return $this;
    }

    // ##########################################

    /**
     * @param $hashKey
     * @param $fieldId
     * @return array
     */
    private function _getHashGetFieldQuery($hashKey, $fieldId)
    {
      return ['HGET', $hashKey, $fieldId];
    }

    // ##########################################

    /**
     * @param $hashKey
     * @param $fieldId
     * @return array|bool|Redis
     */
    public function hashGetField($hashKey, $fieldId)
    {
      return $this->_query($this->_getHashGetFieldQuery($hashKey, $fieldId));
    }

    // ##########################################

    /**
     * @param $hashKey
     * @param $fieldIds
     * @return array
     */
    private function _getHashGetFieldsMultiQuery($hashKey, $fieldIds)
    {
      return array_merge(['HMGET', $hashKey], $fieldIds);
    }

    // ##########################################

    /**
     * @param $hashKey
     * @param $fieldIds
     * @return array|mixed|Redis
     */
    public function hashGetFieldsMulti($hashKey, $fieldIds)
    {
      return $this->_query($this->_getHashGetFieldsMultiQuery($hashKey, $fieldIds));
    }

    // ##########################################

    /**
     * @param $hashKey
     * @return array
     */
    private function _getHashDataQuery($hashKey)
    {
      return array_merge(['HGETALL', $hashKey]);
    }

    // ##########################################

    /**
     * @param $hashKey
     * @return array|mixed|Redis
     */
    public function hashGetData($hashKey)
    {
      $response = $this->_query($this->_getHashDataQuery($hashKey));

      if($response)
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
      $this->pipelineEnable(TRUE);

      foreach($hashKeys as $hashKey)
      {
        $this->_query($this->_getHashDataQuery($hashKey));
      }

      return $this->execute();
    }

    // ##########################################

    /**
     * @param $hashKey
     * @param $fieldId
     * @return array
     */
    private function _getHashFieldExistsQuery($hashKey, $fieldId)
    {
      return ['HEXISTS', $hashKey, $fieldId];
    }

    // ##########################################

    /**
     * @param $hashKey
     * @param $fieldId
     * @return array|mixed|Redis
     */
    public function hashFieldExists($hashKey, $fieldId)
    {
      return $this->_query($this->_getHashFieldExistsQuery($hashKey, $fieldId));
    }

    // ##########################################

    /**
     * @param $hashKey
     * @param $fieldIds
     * @return array
     */
    private function _getHashDeleteFieldMultiQuery($hashKey, array $fieldIds)
    {
      return array_merge(['HDEL', $hashKey], $fieldIds);
    }

    // ##########################################

    /**
     * @param $hashKey
     * @param $fieldId
     * @return array|mixed|Redis
     */
    public function hashDeleteField($hashKey, $fieldId)
    {
      return $this->hashDeleteFieldMulti($hashKey, [$fieldId]);
    }

    // ##########################################

    /**
     * @param $hashKey
     * @param $fieldIds
     * @return array|mixed|Redis
     */
    public function hashDeleteFieldMulti($hashKey, array $fieldIds)
    {
      return $this->_query($this->_getHashDeleteFieldMultiQuery($hashKey, $fieldIds));
    }

    // ##########################################

    /**
     * @param $hashKey
     * @return array
     */
    private function _getHashGetKeysQuery($hashKey)
    {
      return ['HKEYS', $hashKey];
    }

    // ##########################################

    /**
     * @param $hashKey
     * @return array|bool|Redis
     */
    public function hashGetKeys($hashKey)
    {
      return $this->_query($this->_getHashGetKeysQuery($hashKey));
    }

    // ##########################################

    /**
     * @param $hashKey
     * @return array
     */
    private function _getHashGetValuesQuery($hashKey)
    {
      return ['HVALS', $hashKey];
    }

    // ##########################################

    /**
     * @param $hashKey
     * @return array|bool|Redis
     */
    public function hashGetValues($hashKey)
    {
      return $this->_query($this->_getHashGetValuesQuery($hashKey));
    }

    // ##########################################

    /**
     * @param $hashKey
     * @return array
     */
    private function _getHashGetFieldsCountQuery($hashKey)
    {
      return ['HLEN', $hashKey];
    }

    // ##########################################

    /**
     * @param $hashKey
     * @return array|bool|Redis
     */
    public function hashGetFieldsCount($hashKey)
    {
      return $this->_query($this->_getHashGetFieldsCountQuery($hashKey));
    }

    // ##########################################

    /**
     * @param $hashKey
     * @param $value
     * @return array
     */
    private function _getHashIncrementByQuery($hashKey, $value)
    {
      return ['HINCRBY', $hashKey, $value];
    }

    // ##########################################

    /**
     * @param $hashKey
     * @param $value
     * @return Redis
     */
    public function hashIncrementBy($hashKey, $value)
    {
      $response = $this->_query($this->_getHashIncrementByQuery($hashKey, $value));

      return $this;
    }

    // ##########################################

    /**
     * @param $hashKey
     * @param $value
     * @return array
     */
    private function _getHashDecrementByQuery($hashKey, $value)
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
     * @return Redis
     */
    public function hashDecrementBy($hashKey, $value)
    {
      $response = $this->_query($this->_getHashDecrementByQuery($hashKey, $value));

      return $this;
    }

    // ##########################################

    /**
     * @param $key
     * @return Redis
     */
    public function hashDelete($key)
    {
      $response = $this->keyDelete($key);

      return $this;
    }

    // ##########################################

    /**
     * @param array $keys
     * @return Redis
     */
    public function hashDeleteMulti(array $keys)
    {
      $response = $this->keyDeleteMulti($keys);

      return $this;
    }

    // ##########################################

    /**
     * @param $key
     * @param array $values
     * @return array
     */
    private function _getSetAddMultiQuery($key, array $values)
    {
      return array_merge(['SADD', $key], $values);
    }

    // ##########################################

    /**
     * @param $key
     * @param $value
     * @return Redis
     */
    public function setAdd($key, $value)
    {
      $response = $this->_query($this->_getSetAddMultiQuery($key, [$value]));

      return $this;
    }

    // ##########################################

    /**
     * @param $key
     * @param $values
     * @return Redis
     */
    public function setAddMulti($key, array $values)
    {
      $response = $this->_query($this->_getSetAddMultiQuery($key, $values));

      return $this;
    }

    // ##########################################

    /**
     * @param $key
     * @return array
     */
    private function _getSetGetCountQuery($key)
    {
      return ['SCARD', $key];
    }

    // ##########################################

    /**
     * @param $key
     * @return array|bool|Redis
     */
    public function setGetCount($key)
    {
      return $this->_query($this->_getSetGetCountQuery($key));
    }

    // ##########################################

    /**
     * @param $setKeyA
     * @param array $setKeyN
     * @return array
     */
    private function _getSetGetDifferenceMultiQuery($setKeyA, array $setKeyN)
    {
      return array_merge(['SDIFF', $setKeyA], $setKeyN);
    }

    // ##########################################

    /**
     * @param $setKeyA
     * @param $setKeyB
     * @return array|bool|Redis
     */
    public function setDifference($setKeyA, $setKeyB)
    {
      return $this->_query($this->_getSetGetDifferenceMultiQuery($setKeyA, [$setKeyB]));
    }

    // ##########################################

    /**
     * @param $setKeyA
     * @param array $setKeyN
     * @return array|bool|Redis
     */
    public function setGetDifferenceMulti($setKeyA, array $setKeyN)
    {
      return $this->_query($this->_getSetGetDifferenceMultiQuery($setKeyA, $setKeyN));
    }

    // ##########################################

    /**
     * @param $storeSetKey
     * @param $setKeyA
     * @param array $setKeyN
     * @return array
     */
    private function _getSetStoreDifferenceMultiQuery($storeSetKey, $setKeyA, array $setKeyN)
    {
      return array_merge(['SDIFFSTORE', $storeSetKey, $setKeyA], $setKeyN);
    }

    // ##########################################

    /**
     * @param $storeSetKey
     * @param $setKeyA
     * @param $setKeyB
     * @return Redis
     */
    public function setStoreDifference($storeSetKey, $setKeyA, $setKeyB)
    {
      $response = $this->_query($this->_getSetStoreDifferenceMultiQuery($storeSetKey, $setKeyA, [$setKeyB]));

      return $this;
    }

    // ##########################################

    /**
     * @param $storeSetKey
     * @param $setKeyA
     * @param array $setKeyN
     * @return Redis
     */
    public function setStoreDifferenceMulti($storeSetKey, $setKeyA, array $setKeyN)
    {
      $response = $this->_query($this->_getSetStoreDifferenceMultiQuery($storeSetKey, $setKeyA, $setKeyN));

      return $this;
    }

    // ##########################################

    /**
     * @param $setKeyA
     * @param array $setKeyN
     * @return array
     */
    private function _getSetGetIntersectionMultiQuery($setKeyA, array $setKeyN)
    {
      return array_merge(['SINTER', $setKeyA], $setKeyN);
    }

    // ##########################################

    /**
     * @param $setKeyA
     * @param $setKeyB
     * @return array|bool|Redis
     */
    public function setIntersection($setKeyA, $setKeyB)
    {
      return $this->_query($this->_getSetGetIntersectionMultiQuery($setKeyA, [$setKeyB]));
    }

    // ##########################################

    /**
     * @param $setKeyA
     * @param array $setKeyN
     * @return array|bool|Redis
     */
    public function setGetIntersectionMulti($setKeyA, array $setKeyN)
    {
      return $this->_query($this->_getSetGetIntersectionMultiQuery($setKeyA, $setKeyN));
    }

    // ##########################################

    /**
     * @param $storeSetKey
     * @param $setKeyA
     * @param array $setKeyN
     * @return array
     */
    private function _getSetStoreIntersectionMultiQuery($storeSetKey, $setKeyA, array $setKeyN)
    {
      return array_merge(['SINTERSTORE', $storeSetKey, $setKeyA], $setKeyN);
    }

    // ##########################################

    /**
     * @param $storeSetKey
     * @param $setKeyA
     * @param $setKeyB
     * @return Redis
     */
    public function setStoreIntersection($storeSetKey, $setKeyA, $setKeyB)
    {
      $response = $this->_query($this->_getSetStoreIntersectionMultiQuery($storeSetKey, $setKeyA, [$setKeyB]));

      return $this;
    }

    // ##########################################

    /**
     * @param $storeSetKey
     * @param $setKeyA
     * @param array $setKeyN
     * @return Redis
     */
    public function setStoreIntersectionMulti($storeSetKey, $setKeyA, array $setKeyN)
    {
      $response = $this->_query($this->_getSetStoreIntersectionMultiQuery($storeSetKey, $setKeyA, $setKeyN));

      return $this;
    }

    // ##########################################

    /**
     * @param $key
     * @param $value
     * @return array
     */
    private function _getSetValueExistsQuery($key, $value)
    {
      return ['SISMEMBER', $key, $value];
    }

    // ##########################################

    /**
     * @param $key
     * @param $value
     * @return array|bool|Redis
     */
    public function setValueExists($key, $value)
    {
      return $this->_query($this->_getSetValueExistsQuery($key, $value));
    }

    // ##########################################

    /**
     * @param $key
     * @return array
     */
    private function _getSetGetValuesQuery($key)
    {
      return ['SMEMBERS', $key];
    }

    // ##########################################

    /**
     * @param $key
     * @return array|bool|Redis
     */
    public function setGetValues($key)
    {
      return $this->_query($this->_getSetGetValuesQuery($key));
    }

    // ##########################################

    /**
     * @param $setKeySource
     * @param $setKeyDestination
     * @param $value
     * @return array
     */
    private function _getSetMoveValueQuery($setKeySource, $setKeyDestination, $value)
    {
      return ['SMOVE', $setKeySource, $setKeyDestination, $value];
    }

    // ##########################################

    /**
     * @param $setKeySource
     * @param $setKeyDestination
     * @param $value
     * @return Redis
     */
    public function setMoveValue($setKeySource, $setKeyDestination, $value)
    {
      $response = $this->_query($this->_getSetMoveValueQuery($setKeySource, $setKeyDestination, $value));

      return $this;
    }

    // ##########################################

    /**
     * @param $key
     * @return array
     */
    private function _getSetPopRandomValueQuery($key)
    {
      return ['SPOP', $key];
    }

    // ##########################################

    /**
     * @param $key
     * @return array|bool|Redis
     */
    public function setPopRandomValue($key)
    {
      return $this->_query($this->_getSetPopRandomValueQuery($key));
    }

    // ##########################################

    /**
     * @param $key
     * @param int $amount
     * @return array
     */
    private function _getSetGetRandomValuesQuery($key, $amount = 1)
    {
      return ['SRANDMEMBER', $key, $amount];
    }

    // ##########################################

    /**
     * @param $key
     * @param int $amount
     * @return array|bool|Redis
     */
    public function setGetRandomValues($key, $amount = 1)
    {
      return $this->_query($this->_getSetGetRandomValuesQuery($key, $amount));
    }

    // ##########################################

    /**
     * @param $key
     * @param array $values
     * @return array
     */
    private function _getSetDeleteValueMultiQuery($key, array $values)
    {
      return array_merge(['SREM', $key], $values);
    }

    // ##########################################

    /**
     * @param $key
     * @param $value
     * @return Redis
     */
    public function setDeleteValue($key, $value)
    {
      $response = $this->_query($this->_getSetDeleteValueMultiQuery($key, [$value]));

      return $this;
    }

    // ##########################################

    /**
     * @param $key
     * @param array $values
     * @return Redis
     */
    public function setDeleteValueMulti($key, array $values)
    {
      $response = $this->_query($this->_getSetDeleteValueMultiQuery($key, $values));

      return $this;
    }

    // ##########################################

    /**
     * @param $setKeyA
     * @param array $setKeysN
     * @return array
     */
    private function _getSetGetMergeMultiQuery($setKeyA, array $setKeysN)
    {
      return array_merge(['SUNION', $setKeyA], $setKeysN);
    }

    // ##########################################

    /**
     * @param $setKeyA
     * @param $setKeyB
     * @return array|bool|Redis
     */
    public function setMerge($setKeyA, $setKeyB)
    {
      return $this->_query($this->_getSetGetMergeMultiQuery($setKeyA, [$setKeyB]));
    }

    // ##########################################

    /**
     * @param $setKeyA
     * @param array $setKeyN
     * @return array|bool|Redis
     */
    public function setGetMergeMulti($setKeyA, array $setKeyN)
    {
      return $this->_query($this->_getSetGetMergeMultiQuery($setKeyA, $setKeyN));
    }

    // ##########################################

    /**
     * @param $setKeyA
     * @param array $setKeysN
     * @return array
     */
    private function _getSetStoreMergeMultiQuery($setKeyA, array $setKeysN)
    {
      return array_merge(['SUNIONSTORE', $setKeyA], $setKeysN);
    }

    // ##########################################

    /**
     * @param $setKeyA
     * @param $setKeyB
     * @return array|bool|Redis
     */
    public function setStoreMerge($setKeyA, $setKeyB)
    {
      return $this->_query($this->_getSetStoreMergeMultiQuery($setKeyA, [$setKeyB]));
    }

    // ##########################################

    /**
     * @param $setKeyA
     * @param array $setKeyN
     * @return array|bool|Redis
     */
    public function setStoreMergeMulti($setKeyA, array $setKeyN)
    {
      return $this->_query($this->_getSetStoreMergeMultiQuery($setKeyA, $setKeyN));
    }

    // ##########################################

    /**
     * @param $key
     * @return Redis
     */
    public function setDelete($key)
    {
      $response = $this->keyDelete($key);

      return $this;
    }

    // ##########################################

    /**
     * @param array $keys
     * @return Redis
     */
    public function setDeleteMulti(array $keys)
    {
      $response = $this->keyDeleteMulti($keys);

      return $this;
    }

    // ##########################################

    /**
     * @param $key
     * @param array $scoreValuePairs
     * @return array
     */
    private function _getSortedSetAddMultiQuery($key, array $scoreValuePairs)
    {
      $flat = [];

      foreach($scoreValuePairs as $pair)
      {
        $flat[] = $pair['score'];
        $flat[] = $pair['value'];
      }

      return array_merge(['ZADD', $key], $flat);
    }

    // ##########################################

    /**
     * @param $key
     * @param $score
     * @param $value
     * @return Redis
     */
    public function sortedSetAdd($key, $score, $value)
    {
      $scoreValuePair = [
        'score' => $score,
        'value' => $value
      ];

      $response = $this->_query($this->_getSortedSetAddMultiQuery($key, $scoreValuePair));

      return $this;
    }

    // ##########################################

    /**
     * @param $key
     * @param $scoreValuePairs
     * @return Redis
     */
    public function sortedSetAddMulti($key, array $scoreValuePairs)
    {
      $response = $this->_query($this->_getSortedSetAddMultiQuery($key, $scoreValuePairs));

      return $this;
    }

    // ##########################################

    /**
     * @param $key
     * @return array
     */
    private function _getSortedSetGetCountQuery($key)
    {
      return ['ZCARD', $key];
    }

    // ##########################################

    /**
     * @param $key
     * @return array|bool|Redis
     */
    public function sortedSetGetCount($key)
    {
      return $this->_query($this->_getSortedSetGetCountQuery($key));
    }

    // ##########################################

    /**
     * @param $key
     * @param array $values
     * @return array
     */
    private function _getSortedSetDeleteValueMultiQuery($key, array $values)
    {
      return array_merge(['ZREM', $key], $values);
    }

    // ##########################################

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function sortedSetDeleteValue($key, $value)
    {
      return $this->_query($this->_getSortedSetDeleteValueMultiQuery($key, [$value]));
    }

    // ##########################################

    /**
     * @param $key
     * @param array $values
     * @return mixed
     */
    public function sortedSetDeleteValueMulti($key, array $values)
    {
      return $this->_query($this->_getSortedSetDeleteValueMultiQuery($key, $values));
    }

    // ##########################################

    /**
     * @param $key
     * @param string $scoreStart
     * @param string $scoreEnd
     * @return array
     */
    private function _getSortedSetGetRangeCountQuery($key, $scoreStart = '-inf', $scoreEnd = '+inf')
    {
      return ['ZCOUNT', $key, $scoreStart, $scoreEnd];
    }

    // ##########################################

    /**
     * @param $key
     * @param string $scoreStart
     * @param string $scoreEnd
     * @return mixed
     */
    public function sortedSetGetRangeCount($key, $scoreStart = '-inf', $scoreEnd = '+inf')
    {
      return $this->_query($this->_getSortedSetGetRangeCountQuery($key, $scoreStart, $scoreEnd));
    }

    // ##########################################

    /**
     * @param $key
     * @param string $scoreStart
     * @param string $scoreEnd
     * @return array
     */
    private function _getSortedSetGetRangeValuesQuery($key, $scoreStart, $scoreEnd)
    {
      return ['ZRANGE', $key, $scoreStart, $scoreEnd];
    }

    // ##########################################

    /**
     * @param $key
     * @param string $scoreStart
     * @param string $scoreEnd
     * @return mixed
     */
    public function sortedSetGetRangeValues($key, $scoreStart, $scoreEnd)
    {
      return $this->_query($this->_getSortedSetGetRangeValuesQuery($key, $scoreStart, $scoreEnd));
    }

    // ##########################################

    /**
     * @param $key
     * @param string $scoreStart
     * @param string $scoreEnd
     * @return array
     */
    private function _getSortedSetGetRangeValuesWithScoresQuery($key, $scoreStart, $scoreEnd)
    {
      return ['ZRANGE', $key, $scoreStart, $scoreEnd, 'WITHSCORES'];
    }

    // ##########################################

    /**
     * @param $key
     * @param string $scoreStart
     * @param string $scoreEnd
     * @return mixed
     */
    public function sortedSetGetRangeValuesWithScores($key, $scoreStart, $scoreEnd)
    {
      $response = $this->_query($this->_getSortedSetGetRangeValuesWithScoresQuery($key, $scoreStart, $scoreEnd));

      if($response)
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
    private function _getSortedSetGetValueIndexQuery($key, $value)
    {
      return ['ZRANK', $key, $value];
    }

    // ##########################################

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function sortedSetGetValueIndex($key, $value)
    {
      return $this->_query($this->_getSortedSetGetValueIndexQuery($key, $value));
    }

    // ##########################################

    /**
     * @param $key
     * @param $value
     * @return array
     */
    private function _getSortedSetGetValueScoreQuery($key, $value)
    {
      return ['ZSCORE', $key, $value];
    }

    // ##########################################

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function sortedSetGetValueScore($key, $value)
    {
      return $this->_query($this->_getSortedSetGetValueScoreQuery($key, $value));
    }

    // ##########################################

    /**
     * @param $key
     * @return Redis
     */
    public function sortedSetDelete($key)
    {
      $response = $this->keyDelete($key);

      return $this;
    }

    // ##########################################

    /**
     * @param array $keys
     * @return Redis
     */
    public function sortedSetDeleteMulti(array $keys)
    {
      $response = $this->keyDeleteMulti($keys);

      return $this;
    }
  }
