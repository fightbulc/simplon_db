<?php

  namespace Simplon\Db\Abstracts\DAO;

  abstract class AbstractCouchDAO extends AbstractDAO
  {
    /** @var \Simplon\Db\CouchbaseManager */
    protected $_couchManagerInstance;

    /** @var string */
    protected $_couchIdPrefix = '';

    /** @var string */
    protected $_couchId = '';

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

        if($name == 'couchid_prefix')
        {
          $this->_couchIdPrefix = $value;
          break;
        }
      }
    }

    // ##########################################

    /**
     * @param $couchId
     * @return AbstractCouchDAO
     */
    public function setCouchId($couchId)
    {
      $this->_couchId = $couchId;

      return $this;
    }

    // ##########################################

    /**
     * @return bool|string
     */
    protected function _getCouchId()
    {
      return $this->_couchId;
    }

    // ##########################################

    /**
     * @return string
     */
    protected function _getCouchIdPrefix()
    {
      if(empty($this->_couchIdPrefix))
      {
        return "";
      }

      return $this->_couchIdPrefix . '_';
    }

    // ##########################################

    /**
     * @return bool|string
     */
    protected function _getPrefixedCouchId()
    {
      $couchId = $this->_getCouchId();

      if(empty($couchId))
      {
        return FALSE;
      }

      return $this->_couchIdPrefix . $couchId;
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

      // get prefixed couchId
      $couchIdPrefixed = $this->_getPrefixedCouchId();

      // return if no ID
      if($couchIdPrefixed === FALSE)
      {
        return FALSE;
      }

      // build couch query
      $couchQuery = \Simplon\Db\CouchQueryBuilder::init()
        ->setId($couchIdPrefixed);

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
      $this->_setData($result);

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

      // get prefixed couchId
      $couchIdPrefixed = $this->_getPrefixedCouchId();

      // return if no ID
      if($couchIdPrefixed === FALSE)
      {
        return FALSE;
      }

      // build couch query
      $couchQuery = \Simplon\Db\CouchQueryBuilder::init()
        ->setId($couchIdPrefixed)
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

      return $this;
    }

    // ##########################################

    /**
     * @return bool
     */
    public function delete()
    {
      // get prefixed couchId
      $couchIdPrefixed = $this->_getPrefixedCouchId();

      // return if no ID
      if($couchIdPrefixed === FALSE)
      {
        return FALSE;
      }

      // build couch query
      $couchQuery = \Simplon\Db\CouchQueryBuilder::init()
        ->setId($couchIdPrefixed);

      // remove
      $response = $this
        ->_getCouchManagerInstance()
        ->delete($couchQuery);

      // no result exception
      if($response === FALSE)
      {
        return FALSE;
      }

      return $this;
    }
  }
