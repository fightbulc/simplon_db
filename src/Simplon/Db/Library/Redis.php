<?php

  namespace Simplon\Db\Library;

  class Redis
  {
    /** @var Phpiredis */
    protected $_redisInstance;

    /** @var bool */
    protected $_enablePipeline = FALSE;

    /** @var array */
    protected $_pipelineQueue = array();

    /** @var array */
    protected $_responseQueue = array();

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
     * @return bool|mixed
     */
    protected function _query($commandArgs)
    {
      if($commandArgs === FALSE)
      {
        return FALSE;
      }

      $response = phpiredis_command_bs($this->_getRedisInstance(), $commandArgs);

      if(is_array($response) || substr($response, 0, 2) !== 'ERR')
      {
        return $response;
      }

      return FALSE;
    }

    // ##########################################

    /**
     * @return array
     */
    protected function _pipelineExecute()
    {
      $_pipeline = $this->_pipelineGetQueue();

      $requestResponsesMulti = [
        'errors'    => [],
        'responses' => [],
      ];

      // run through all commands
      $responsesMulti = phpiredis_multi_command_bs($this->_getRedisInstance(), $_pipeline);

      // build request/response array
      foreach($responsesMulti as $index => $response)
      {
        $_requestKey = json_encode($_pipeline[$index]);

        if(is_array($response) || substr($response, 0, 3) !== 'ERR')
        {
          $requestResponsesMulti['responses'][$_requestKey] = $response;
          continue;
        }

        $requestResponsesMulti['error'][$_requestKey] = $response;
      }

      // reset request/response queues
      $this->_pipelineResetQueue();

      // disable pipeline
      $this->_pipelineEnable(FALSE);

      return $requestResponsesMulti;
    }

    // ##########################################

    /**
     * @param bool $use
     * @return Redis
     */
    protected function _pipelineEnable($use = TRUE)
    {
      $this->_enablePipeline = $use === TRUE ? TRUE : FALSE;

      return $this;
    }

    // ##########################################

    /**
     * @return bool
     */
    protected function _pipelineIsEnabled()
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
     * @return Redis
     */
    protected function _pipelineAddQueueItem($cmdArgs)
    {
      if($cmdArgs !== FALSE)
      {
        $this->_pipelineQueue[] = $cmdArgs;
      }

      return $this;
    }

    // ##########################################

    /**
     * @return Redis
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
     * @return Redis
     */
    protected function _getDbSelectQuery($dbId)
    {
      return array('SELECT', (string)$dbId);
    }

    // ##########################################

    /**
     * @param $dbId
     * @return bool|mixed
     */
    public function dbSelect($dbId)
    {
      $response = $this->_query($this->_getDbSelectQuery($dbId));

      if($response != FALSE)
      {
        return $response;
      }

      return FALSE;
    }

    // ##########################################

    /**
     * @param $password
     * @return array
     */
    protected function _getDbAuthQuery($password)
    {
      return array('AUTH', $password);
    }

    // ##########################################

    /**
     * @param $password
     * @return bool|mixed
     */
    public function dbAuth($password)
    {
      $response = $this->_query($this->_getDbAuthQuery($password));

      if($response != FALSE)
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
     * @return bool|mixed
     */
    public function dbFlush($confirm = FALSE)
    {
      if($confirm === TRUE)
      {
        $response = $this->_query($this->_getDbFlushQuery());

        if($response != FALSE)
        {
          return $response;
        }
      }

      return FALSE;
    }

    // ##########################################

    /**
     * @param array $keys
     * @return array
     */
    protected function _getKeyDeleteMultiQuery(array $keys)
    {
      return array_merge(['DEL'], $keys);
    }

    // ##########################################

    /**
     * @param $key
     * @return bool|mixed
     */
    public function keyDelete($key)
    {
      $response = $this->_query($this->_getKeyDeleteMultiQuery([$key]));

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
    public function keyDeleteMulti(array $keys)
    {
      $response = $this->_query($this->_getKeyDeleteMultiQuery($keys));

      if($response != FALSE)
      {
        return $response;
      }

      return FALSE;
    }

    // ##########################################

    /**
     * @param $key
     * @param $seconds
     * @return array|bool
     */
    protected function _getKeySetExpireQuery($key, $seconds = -1)
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
     * @return bool|mixed
     */
    public function keySetExpire($key, $seconds = -1)
    {
      $response = $this->_query($this->_getKeySetExpireQuery($key, $seconds));

      if($response != FALSE)
      {
        return $response;
      }

      return FALSE;
    }

    // ##########################################

    /**
     * @param array $keys
     * @param $seconds
     * @return array|bool
     */
    public function keySetExpireMulti(array $keys, $seconds = -1)
    {
      if($seconds > 0)
      {
        $this->_pipelineEnable(TRUE);

        foreach($keys as $key)
        {
          $this->_pipelineAddQueueItem($this->_getKeySetExpireQuery($key, $seconds));
        }

        $response = $this->_pipelineExecute();

        if($response != FALSE)
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
      $response = $this->_query($this->_getKeysGetCount());

      if($response != FALSE)
      {
        return $response;
      }

      return FALSE;
    }

    // ##########################################

    /**
     * @param $pattern
     * @return array
     */
    protected function _getKeysGetByPatternQuery($pattern)
    {
      return ['KEYS', $pattern];
    }

    // ##########################################

    /**
     * @param $pattern
     * @return bool|mixed
     */
    public function keysGetByPattern($pattern)
    {
      $response = $this->_query($this->_getKeysGetByPatternQuery($pattern));

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
    protected function _getKeyExistsQuery($key)
    {
      return ['EXISTS', $key];
    }

    // ##########################################

    /**
     * @param $key
     * @return bool|mixed
     */
    public function keyExists($key)
    {
      $response = $this->_query($this->_getKeyExistsQuery($key));

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
    protected function _getKeyGetExpirationQuery($key)
    {
      return ['TTL', $key];
    }

    // ##########################################

    /**
     * @param $key
     * @return bool|mixed
     */
    public function keyGetExpiration($key)
    {
      $response = $this->_query($this->_getKeyGetExpirationQuery($key));

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
    protected function _getKeyRenameQuery($key)
    {
      return ['RENAMENX', $key];
    }

    // ##########################################

    /**
     * @param $key
     * @return bool|mixed
     */
    public function keyRename($key)
    {
      $response = $this->_query($this->_getKeyRenameQuery($key));

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
    protected function _getKeyRemoveExpirationQuery($key)
    {
      return ['PERSIST', $key];
    }

    // ##########################################

    /**
     * @param $key
     * @return bool|mixed
     */
    public function keyRemoveExpiration($key)
    {
      $response = $this->_query($this->_getKeyRemoveExpirationQuery($key));

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
    protected function _getKeyIncrementByQuery($key, $value = 1)
    {
      return ['INCRBY', $key, $value];
    }

    // ##########################################

    /**
     * @param $key
     * @param int $value
     * @return bool|mixed
     */
    public function keyIncrementBy($key, $value = 1)
    {
      $response = $this->_query($this->_getKeyIncrementByQuery($key, $value));

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
    protected function _getKeyDecrementByQuery($key, $value = 1)
    {
      return ['DECRBY', $key, $value];
    }

    // ##########################################

    /**
     * @param $key
     * @param int $value
     * @return bool|mixed
     */
    public function keyDecrementBy($key, $value = 1)
    {
      $response = $this->_query($this->_getKeyDecrementByQuery($key, $value));

      if($response != FALSE)
      {
        return $response;
      }

      return FALSE;
    }

    // ##########################################

    /**
     * @param $key
     * @param $offset
     * @param $value
     * @return array
     */
    protected function _getBitSetQuery($key, $offset, $value)
    {
      return array('SETBIT', $key, $offset, $value);
    }

    // ##########################################

    /**
     * @param $key
     * @param $offset
     * @param $value
     * @return bool|mixed
     */
    public function bitSet($key, $offset, $value)
    {
      $response = $this->_query($this->_getBitSetQuery($key, $offset, $value));

      if($response != FALSE)
      {
        return $response;
      }

      return FALSE;
    }

    // ##########################################

    /**
     * @param $key
     * @param $offset
     * @return array
     */
    protected function _getBitGetQuery($key, $offset)
    {
      return array('GETBIT', $key, $offset);
    }

    // ##########################################

    /**
     * @param $key
     * @param $offset
     * @return bool|mixed
     */
    public function bitGet($key, $offset)
    {
      $response = $this->_query($this->_getBitGetQuery($key, $offset));

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
    protected function _getBitGetAllQuery($key)
    {
      return ['GET', $key];
    }

    // ##########################################

    /**
     * @param $key
     * @return bool|mixed
     */
    public function bitGetAll($key)
    {
      $response = $this->_query($this->_getBitGetAllQuery($key));

      if($response != FALSE)
      {
        return $response;
      }

      return FALSE;
    }

    // ##########################################

    /**
     * @param $key
     * @param int $start
     * @param $end
     * @return array
     */
    protected function _getBitCountQuery($key, $start = 0, $end = -1)
    {
      return array('BITCOUNT', $key, $start, $end);
    }

    // ##########################################

    /**
     * @param $key
     * @param int $start
     * @param $end
     * @return bool|mixed
     */
    public function bitCount($key, $start = 0, $end = -1)
    {
      $response = $this->_query($this->_getBitCountQuery($key, $start, $end));

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
    public function bitDelete($key)
    {
      $response = $this->keyDelete($key);

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
    public function bitDeleteMulti(array $keys)
    {
      $response = $this->keyDeleteMulti($keys);

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
     * @param $expire
     * @return array
     */
    protected function _getStringSetQuery($key, $value, $expire = -1)
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
     * @return bool|mixed
     */
    public function stringSet($key, $value, $expire = -1)
    {
      $response = $this->_query($this->_getStringSetQuery($key, $value, $expire));

      if($response != FALSE)
      {
        return $response;
      }

      return FALSE;
    }

    // ##########################################

    /**
     * @param array $pairs
     * @param $expire
     * @return Redis
     */
    public function stringSetMulti(array $pairs, $expire = -1)
    {
      $this->_pipelineEnable(TRUE);

      foreach($pairs as $key => $value)
      {
        $this->_pipelineAddQueueItem($this->_getStringSetQuery($key, $value, $expire));
      }

      $response = $this->_pipelineExecute();

      if(empty($response['errors']))
      {
        return TRUE;
      }

      return FALSE;
    }

    // ##########################################

    /**
     * @param $key
     * @return string
     */
    protected function _getStringGetQuery($key)
    {
      return array('GET', $key);
    }

    // ##########################################

    /**
     * @param $key
     * @return bool|mixed
     */
    public function stringGetData($key)
    {
      $response = $this->_query($this->_getStringGetQuery($key));

      if($response != FALSE)
      {
        return $response;
      }

      return FALSE;
    }

    // ##########################################

    /**
     * @param array $keys
     * @return array
     */
    protected function _getStringGetMultiQuery(array $keys)
    {
      return array_merge(['MGET'], $keys);
    }

    // ##########################################

    /**
     * @param array $keys
     * @return bool|mixed
     */
    public function stringGetDataMulti(array $keys)
    {
      $response = $this->_query($this->_getStringGetMultiQuery($keys));

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
    public function stringDelete($key)
    {
      $response = $this->keyDelete($key);

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
    public function stringDeleteMulti(array $keys)
    {
      $response = $this->keyDeleteMulti($keys);

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
      $response = $this->_query($this->_getListUnshiftMultiQuery($key, [$value]));

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
      $response = $this->_query($this->_getListUnshiftMultiQuery($key, $values));

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
      $this->_pipelineEnable(TRUE);

      foreach($listPairs as $listKey => $listValue)
      {
        $this->_pipelineAddQueueItem($this->_getListUnshiftMultiQuery($listKey, [$listValue]));
      }

      $response = $this->_pipelineExecute();

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
      $this->_pipelineEnable(TRUE);

      foreach($listPairs as $listKey => $listValues)
      {
        $this->_pipelineAddQueueItem($this->_getListUnshiftMultiQuery($listKey, $listValues));
      }

      $response = $this->_pipelineExecute();

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
      $response = $this->_query($this->_getListPushMultiQuery($key, [$value]));

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
      $response = $this->_query($this->_getListUnshiftMultiQuery($key, $values));

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
      $this->_pipelineEnable(TRUE);

      foreach($listPairs as $listKey => $listValue)
      {
        $this->_pipelineAddQueueItem($this->_getListPushMultiQuery($listKey, [$listValue]));
      }

      $response = $this->_pipelineExecute();

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
      $this->_pipelineEnable(TRUE);

      foreach($listPairs as $listKey => $listValues)
      {
        $this->_pipelineAddQueueItem($this->_getListPushMultiQuery($listKey, $listValues));
      }

      $response = $this->_pipelineExecute();

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
      $response = $this->_query($this->_getListShiftQuery($key));

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
      $response = $this->_query($this->_getListPopQuery($key));

      if($response != FALSE)
      {
        return $response;
      }

      return FALSE;
    }

    // ##########################################

    /**
     * @param $key
     * @param $start
     * @param $end
     * @return array
     */
    protected function _getListGetValuesByRangeQuery($key, $start, $end)
    {
      return ['LRANGE', $key, (string)$start, (string)$end];
    }

    // ##########################################

    /**
     * @param $key
     * @param $start
     * @param $end
     * @return bool|mixed
     */
    public function listGetDataByRange($key, $start, $end)
    {
      $response = $this->_query($this->_getListGetValuesByRangeQuery($key, $start, $end));

      if($response != FALSE)
      {
        return $response;
      }

      return FALSE;
    }

    // ##########################################

    /**
     * @param array $keys
     * @param $start
     * @param $end
     * @return array|bool
     */
    public function listMultiGetDataByRange(array $keys, $start, $end)
    {
      $this->_pipelineEnable(TRUE);

      foreach($keys as $key)
      {
        $this->_pipelineAddQueueItem($this->_getListGetValuesByRangeQuery($key, $start, $end));
      }

      $response = $this->_pipelineExecute();

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
      $response = $this->_query($this->_getListGetValuesByRangeQuery($key, 0, - 1));

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
      $this->_pipelineEnable(TRUE);

      foreach($keys as $key)
      {
        $this->_pipelineAddQueueItem($this->_getListGetValuesByRangeQuery($key, 0, - 1));
      }

      $response = $this->_pipelineExecute();

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
      $response = $this->_query($this->_getListGetCountQuery($key));

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
      $response = $this->_query($this->_getListSetAtIndexQuery($key, $index, $value));

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
    protected function _getListGetFromIndexQuery($key, $index, $value)
    {
      return ['LINDEX', $key, $index, $value];
    }

    // ##########################################

    /**
     * @param $key
     * @param $index
     * @param $value
     * @return bool|mixed
     */
    public function listGetFromIndex($key, $index, $value)
    {
      $response = $this->_query($this->_getListGetFromIndexQuery($key, $index, $value));

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
      $response = $this->_query($this->_getListTrimQuery($key, $index, $value));

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
      $response = $this->keyDelete($key);

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
      $response = $this->keyDeleteMulti($keys);

      if($response != FALSE)
      {
        return $response;
      }

      return FALSE;
    }

    // ##########################################

    /**
     * @param $hashKey
     * @param $fieldId
     * @param $value
     * @return array
     */
    protected function _getHashSetFieldQuery($hashKey, $fieldId, $value)
    {
      return ['HSET', $hashKey, $fieldId, $value];
    }

    // ##########################################

    /**
     * @param $hashKey
     * @param $fieldId
     * @param int $value
     * @return bool|mixed
     */
    public function hashSetField($hashKey, $fieldId, $value = 1)
    {
      $response = $this->_query($this->_getHashSetFieldQuery($hashKey, $fieldId, (string)$value));

      if($response != FALSE)
      {
        return $response;
      }

      return FALSE;
    }

    // ##########################################

    /**
     * @param $hashKey
     * @param $pairs
     * @return array
     */
    protected function _getHashSetFieldsMultiQuery($hashKey, $pairs)
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
     * @return array|bool
     */
    public function hashSetFieldsMulti($hashKey, $pairs, $expire = -1)
    {
      $this->_pipelineEnable(TRUE);

      $this->_pipelineAddQueueItem($this->_getHashSetFieldsMultiQuery($hashKey, $pairs));
      $this->_pipelineAddQueueItem($this->_getKeySetExpireQuery($hashKey, $expire));

      $response = $this->_pipelineExecute();

      if(empty($response['errors']))
      {
        return TRUE;
      }

      return FALSE;
    }

    // ##########################################

    /**
     * @param $hashKey
     * @param $fieldId
     * @return array
     */
    protected function _getHashGetFieldQuery($hashKey, $fieldId)
    {
      return ['HGET', $hashKey, $fieldId];
    }

    // ##########################################

    /**
     * @param $hashKey
     * @param $fieldId
     * @return bool|mixed
     */
    public function hashGetField($hashKey, $fieldId)
    {
      $response = $this->_query($this->_getHashGetFieldQuery($hashKey, $fieldId));

      if($response != FALSE)
      {
        return $response;
      }

      return FALSE;
    }

    // ##########################################

    /**
     * @param $hashKey
     * @param $fieldIds
     * @return array
     */
    protected function _getHashGetFieldsMultiQuery($hashKey, $fieldIds)
    {
      return array_merge(['HMGET', $hashKey], $fieldIds);
    }

    // ##########################################

    /**
     * @param $hashKey
     * @param $fieldIds
     * @return bool|mixed
     */
    public function hashGetFieldsMulti($hashKey, $fieldIds)
    {
      $response = $this->_query($this->_getHashGetFieldsMultiQuery($hashKey, $fieldIds));

      if($response != FALSE)
      {
        return $response;
      }

      return FALSE;
    }

    // ##########################################

    /**
     * @param $hashKey
     * @return array
     */
    protected function _getHashDataQuery($hashKey)
    {
      return array_merge(['HGETALL', $hashKey]);
    }

    // ##########################################

    /**
     * @param $hashKey
     * @return array|bool
     */
    public function hashGetData($hashKey)
    {
      $response = $this->_query($this->_getHashDataQuery($hashKey));

      if($response != FALSE)
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
      $this->_pipelineEnable(TRUE);

      foreach($hashKeys as $hashKey)
      {
        $this->_pipelineAddQueueItem($this->_getHashDataQuery($hashKey));
      }

      $response = $this->_pipelineExecute();

      if(empty($response['errors']))
      {
        return TRUE;
      }

      return FALSE;
    }

    // ##########################################

    /**
     * @param $hashKey
     * @param $fieldId
     * @return array
     */
    protected function _getHashFieldExistsQuery($hashKey, $fieldId)
    {
      return ['HEXISTS', $hashKey, $fieldId];
    }

    // ##########################################

    /**
     * @param $hashKey
     * @param $fieldId
     * @return bool|mixed
     */
    public function hashFieldExists($hashKey, $fieldId)
    {
      $response = $this->_query($this->_getHashFieldExistsQuery($hashKey, $fieldId));

      if($response != FALSE)
      {
        return $response;
      }

      return FALSE;
    }

    // ##########################################

    /**
     * @param $hashKey
     * @param $fieldIds
     * @return array
     */
    protected function _getHashDeleteFieldMultiQuery($hashKey, array $fieldIds)
    {
      return array_merge(['HDEL', $hashKey], $fieldIds);
    }

    // ##########################################

    /**
     * @param $hashKey
     * @param $fieldId
     * @return bool|mixed
     */
    public function hashDeleteField($hashKey, $fieldId)
    {
      $response = $this->hashDeleteFieldMulti($hashKey, [$fieldId]);

      if($response != FALSE)
      {
        return $response;
      }

      return FALSE;
    }

    // ##########################################

    /**
     * @param $hashKey
     * @param array $fieldIds
     * @return bool|mixed
     */
    public function hashDeleteFieldMulti($hashKey, array $fieldIds)
    {
      $response = $this->_query($this->_getHashDeleteFieldMultiQuery($hashKey, $fieldIds));

      if($response != FALSE)
      {
        return $response;
      }

      return FALSE;
    }

    // ##########################################

    /**
     * @param $hashKey
     * @return array
     */
    protected function _getHashGetKeysQuery($hashKey)
    {
      return ['HKEYS', $hashKey];
    }

    // ##########################################

    /**
     * @param $hashKey
     * @return bool|mixed
     */
    public function hashGetKeys($hashKey)
    {
      $response = $this->_query($this->_getHashGetKeysQuery($hashKey));

      if($response != FALSE)
      {
        return $response;
      }

      return FALSE;
    }

    // ##########################################

    /**
     * @param $hashKey
     * @return array
     */
    protected function _getHashGetValuesQuery($hashKey)
    {
      return ['HVALS', $hashKey];
    }

    // ##########################################

    /**
     * @param $hashKey
     * @return bool|mixed
     */
    public function hashGetValues($hashKey)
    {
      $response = $this->_query($this->_getHashGetValuesQuery($hashKey));

      if($response != FALSE)
      {
        return $response;
      }

      return FALSE;
    }

    // ##########################################

    /**
     * @param $hashKey
     * @return array
     */
    protected function _getHashGetFieldsCountQuery($hashKey)
    {
      return ['HLEN', $hashKey];
    }

    // ##########################################

    /**
     * @param $hashKey
     * @return bool|mixed
     */
    public function hashGetFieldsCount($hashKey)
    {
      $response = $this->_query($this->_getHashGetFieldsCountQuery($hashKey));

      if($response != FALSE)
      {
        return $response;
      }

      return FALSE;
    }

    // ##########################################

    /**
     * @param $hashKey
     * @param $value
     * @return array
     */
    protected function _getHashIncrementByQuery($hashKey, $value)
    {
      return ['HINCRBY', $hashKey, $value];
    }

    // ##########################################

    /**
     * @param $hashKey
     * @param $value
     * @return bool|mixed
     */
    public function hashIncrementBy($hashKey, $value)
    {
      $response = $this->_query($this->_getHashIncrementByQuery($hashKey, $value));

      if($response != FALSE)
      {
        return $response;
      }

      return FALSE;
    }

    // ##########################################

    /**
     * @param $hashKey
     * @param $value
     * @return array
     */
    protected function _getHashDecrementByQuery($hashKey, $value)
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
     * @return bool|mixed
     */
    public function hashDecrementBy($hashKey, $value)
    {
      $response = $this->_query($this->_getHashDecrementByQuery($hashKey, $value));

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
    public function hashDelete($key)
    {
      $response = $this->keyDelete($key);

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
    public function hashDeleteMulti(array $keys)
    {
      $response = $this->keyDeleteMulti($keys);

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
      $response = $this->_query($this->_getSetAddMultiQuery($key, [$value]));

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
      $response = $this->_query($this->_getSetAddMultiQuery($key, $values));

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
      $response = $this->_query($this->_getSetGetCountQuery($key));

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
      $response = $this->_query($this->_getSetGetDifferenceMultiQuery($setKeyA, [$setKeyB]));

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
      $response = $this->_query($this->_getSetGetDifferenceMultiQuery($setKeyA, $setKeyN));

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
      $response = $this->_query($this->_getSetStoreDifferenceMultiQuery($storeSetKey, $setKeyA, [$setKeyB]));

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
      $response = $this->_query($this->_getSetStoreDifferenceMultiQuery($storeSetKey, $setKeyA, $setKeyN));

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
      $response = $this->_query($this->_getSetGetIntersectionMultiQuery($setKeyA, [$setKeyB]));

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
      $response = $this->_query($this->_getSetGetIntersectionMultiQuery($setKeyA, $setKeyN));

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
      $response = $this->_query($this->_getSetStoreIntersectionMultiQuery($storeSetKey, $setKeyA, [$setKeyB]));

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
      $response = $this->_query($this->_getSetStoreIntersectionMultiQuery($storeSetKey, $setKeyA, $setKeyN));

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
      $response = $this->_query($this->_getSetValueExistsQuery($key, $value));

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
      $response = $this->_query($this->_getSetGetValuesQuery($key));

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
      $this->_pipelineEnable(TRUE);

      foreach($keys as $key)
      {
        $this->_pipelineAddQueueItem($this->_getSetGetValuesQuery($key));
      }

      $response = $this->_pipelineExecute();

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
      $response = $this->_query($this->_getSetMoveValueQuery($setKeySource, $setKeyDestination, $value));

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
      $response = $this->_query($this->_getSetPopRandomValueQuery($key));

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
      $response = $this->_query($this->_getSetGetRandomValuesQuery($key, $amount));

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
      $response = $this->_query($this->_getSetDeleteValueMultiQuery($key, [$value]));

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
      $response = $this->_query($this->_getSetDeleteValueMultiQuery($key, $values));

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
      $response = $this->_query($this->_getSetGetMergeMultiQuery($setKeyA, [$setKeyB]));

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
      $response = $this->_query($this->_getSetGetMergeMultiQuery($setKeyA, $setKeyN));

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
      $response = $this->_query($this->_getSetStoreMergeMultiQuery($setKeyA, [$setKeyB]));

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
      $response = $this->_query($this->_getSetStoreMergeMultiQuery($setKeyA, $setKeyN));

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
      $response = $this->keyDelete($key);

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
      $response = $this->keyDeleteMulti($keys);

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
     * @return array
     */
    protected function _getSortedSetAddMultiQuery($key, array $scoreValuePairs)
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
     * @return bool|mixed
     */
    public function sortedSetAdd($key, $score, $value)
    {
      $scoreValuePair = [
        'score' => $score,
        'value' => $value
      ];

      $response = $this->_query($this->_getSortedSetAddMultiQuery($key, $scoreValuePair));

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
    public function sortedSetAddMulti($key, array $scoreValuePairs)
    {
      $response = $this->_query($this->_getSortedSetAddMultiQuery($key, $scoreValuePairs));

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
      $response = $this->_query($this->_getSortedSetGetCountQuery($key));

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
      $response = $this->_query($this->_getSortedSetDeleteValueMultiQuery($key, [$value]));

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
      $response = $this->_query($this->_getSortedSetDeleteValueMultiQuery($key, $values));

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
      $response = $this->_query($this->_getSortedSetGetRangeCountQuery($key, $scoreStart, $scoreEnd));

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
    protected function _getSortedSetGetRangeValuesQuery($key, $scoreStart, $scoreEnd)
    {
      return ['ZRANGE', $key, $scoreStart, $scoreEnd];
    }

    // ##########################################

    /**
     * @param $key
     * @param $scoreStart
     * @param $scoreEnd
     * @return bool|mixed
     */
    public function sortedSetGetRangeValues($key, $scoreStart, $scoreEnd)
    {
      $response = $this->_query($this->_getSortedSetGetRangeValuesQuery($key, $scoreStart, $scoreEnd));

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
    protected function _getSortedSetGetRangeValuesWithScoresQuery($key, $scoreStart, $scoreEnd)
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
    public function sortedSetGetRangeValuesWithScores($key, $scoreStart, $scoreEnd)
    {
      $response = $this->_query($this->_getSortedSetGetRangeValuesWithScoresQuery($key, $scoreStart, $scoreEnd));

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
    protected function _getSortedSetGetValueIndexQuery($key, $value)
    {
      return ['ZRANK', $key, $value];
    }

    // ##########################################

    /**
     * @param $key
     * @param $value
     * @return bool|mixed
     */
    public function sortedSetGetValueIndex($key, $value)
    {
      $response = $this->_query($this->_getSortedSetGetValueIndexQuery($key, $value));

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
    protected function _getSortedSetGetValueScoreQuery($key, $value)
    {
      return ['ZSCORE', $key, $value];
    }

    // ##########################################

    /**
     * @param $key
     * @param $value
     * @return bool|mixed
     */
    public function sortedSetGetValueScore($key, $value)
    {
      $response = $this->_query($this->_getSortedSetGetValueScoreQuery($key, $value));

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
      $response = $this->keyDelete($key);

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
      $response = $this->keyDeleteMulti($keys);

      if($response != FALSE)
      {
        return $response;
      }

      return FALSE;
    }
  }
