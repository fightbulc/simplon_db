<?php

  namespace Simplon\Db\Abstracts\DAO;

  class AbstractCouchDAO extends AbstractDAO
  {
    /** @var \Simplon\Db\CouchbaseManager */
    protected $_couchManagerInstance;

    /** @var string */
    protected $_couchId = '';

    /** @var int */
    protected $_expiresTime = '0s';

    // ##########################################

    /**
     * @param \Simplon\Db\CouchbaseManager $couchManagerInstance
     */
    public function __construct(\Simplon\Db\CouchbaseManager $couchManagerInstance)
    {
      $this->_couchManagerInstance = $couchManagerInstance;

      // get field references
      $this->_getFieldReferences();
    }

    // ##########################################

    /**
     * @return \Simplon\Db\CouchbaseManager
     */
    protected function _getCouchManagerInstance()
    {
      return $this->_couchManagerInstance;
    }

    // ##########################################

    /**
     * @param $message
     */
    protected function _throwException($message)
    {
      parent::_throwException(__CLASS__ . ': ' . $message);
    }

    // ##########################################

    protected function _getFieldReferences()
    {
      // get field references
      parent::_getFieldReferences();

      // get id and table reference
      $properties = $this->_getClassProperties();

      foreach($properties as $name => $value)
      {
        $name = strtolower($name);

        if($name == 'expires')
        {
          $this->_expiresTime = $value;
        }
      }
    }

    // ##########################################

    /**
     * @param $userId
     * @return AbstractCouchDAO
     */
    public function setCouchId($userId)
    {
      $this->_couchId = $userId;

      return $this;
    }

    // ##########################################

    /**
     * @return bool|string
     */
    public function getCouchId()
    {
      return $this->_couchId;
    }

    // ##########################################

    /**
     * @return int
     */
    protected function _getExpiresTime()
    {
      $time = $this->_expiresTime;

      if($time === 0)
      {
        return $time;
      }

      // separate timeUnit from interval
      $timeUnit = strtolower(substr($time, - 1, 1));

      // cast interval
      $interval = (int)str_replace($timeUnit, '', $time);

      switch($timeUnit)
      {
        case 'm':
          $timeInSeconds = 60 * $interval;
          break;

        case 'h':
          $timeInSeconds = 60 * 60 * $interval;
          break;

        case 'd':
          $timeInSeconds = 60 * 60 * 24 * $interval;
          break;

        case 'w':
          $timeInSeconds = 60 * 60 * 24 * 7 * $interval;
          break;

        default:
          $timeInSeconds = $interval;
      }

      return $timeInSeconds;
    }

    // ##########################################

    /**
     * @param $couchId
     * @return bool|AbstractCouchDAO|AbstractDAO
     */
    public function fetch($couchId)
    {
      // set couchId
      $this->setCouchId($couchId);

      // build couch query
      $couchQuery = \Simplon\Db\CouchQueryBuilder::init()
        ->setId($this->getCouchId());

      // fetch row
      $result = $this
        ->_getCouchManagerInstance()
        ->fetch($couchQuery);

      // no result exception
      if($result === FALSE)
      {
        return FALSE;
      }

      // set data
      $this->setData($result);

      return $this;
    }

    // ##########################################

    /**
     * @return array
     */
    protected function _getPreparedCreateUpdateData()
    {
      $data = array();
      $fieldReferenceNames = $this->_getFieldReferenceNames();

      foreach($fieldReferenceNames as $key)
      {
        $data[$key] = $this->_getByKey($key);
      }

      return $data;
    }

    // ##########################################

    /**
     * @return bool
     */
    public function save()
    {
      // prepare data
      $preparedData = $this->_getPreparedCreateUpdateData();

      // get couchId
      $couchId = $this->getCouchId();

      // return if no ID
      if($couchId === FALSE)
      {
        return FALSE;
      }

      // build couch query
      $couchQuery = \Simplon\Db\CouchQueryBuilder::init()
        ->setId($couchId)
        ->setExpirationInSeconds($this->_getExpiresTime())
        ->setData($preparedData);

      // save
      $response = $this
        ->_getCouchManagerInstance()
        ->set($couchQuery);

      // no result exception
      if($response === FALSE)
      {
        return FALSE;
      }

      return TRUE;
    }

    // ##########################################

    /**
     * @return bool
     */
    public function delete()
    {
      // get couchId
      $couchId = $this->getCouchId();

      // return if no ID
      if($couchId === FALSE)
      {
        return FALSE;
      }

      // build couch query
      $couchQuery = \Simplon\Db\CouchQueryBuilder::init()
        ->setId($couchId);

      // remove
      $response = $this
        ->_getCouchManagerInstance()
        ->delete($couchQuery);

      // no result exception
      if($response === FALSE)
      {
        return FALSE;
      }

      return TRUE;
    }
  }
