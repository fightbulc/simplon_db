<?php

  namespace Simplon\Db\Abstracts\DAO;

  abstract class AbstractCouchDAO extends AbstractDAO
  {
    /** @var \Simplon\Db\CouchbaseManager */
    protected $_couchManagerInstance;

    /** @var string */
    protected $_idReference = '';

    /** @var array */
    protected $_fieldNames = array();

    /** @var array */
    protected $_fieldTypes = array();

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

    /**
     * @param $couchId
     * @return bool|AbstractCouchDAO|AbstractDAO
     */
    public function fetch($couchId)
    {
      // build query
      $couchQuery = \Simplon\Db\CouchQueryBuilder::init()
        ->setId($couchId);

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
    protected function save()
    {
      // prepare data
      $preparedData = $this->_getPreparedCreateUpdateData();

      // build query
      $couchQuery = \Simplon\Db\CouchQueryBuilder::init()
        ->setId($this->_getIdReferenceValue())
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
    protected function _delete()
    {
      $couchQuery = \Simplon\Db\CouchQueryBuilder::init()
        ->setId($this->_getIdReferenceValue());

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
