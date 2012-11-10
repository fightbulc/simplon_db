<?php

  namespace Simplon\Db;

  class CouchbaseManager
  {
    /** @var \Simplon\Db\Library\Couchbase */
    protected $_couchbaseInstance;

    // ########################################

    /**
     * @param Library\Couchbase $instance
     */
    public function __construct(\Simplon\Db\Library\Couchbase $instance)
    {
      $this->_couchbaseInstance = $instance;
    }

    // ########################################

    /**
     * @return Library\Couchbase
     */
    protected function _getCouchbaseInstance()
    {
      return $this->_couchbaseInstance;
    }

    // ########################################

    /**
     * @param CouchQueryBuilder $couchQuery
     * @return array
     */
    public function fetch(CouchQueryBuilder $couchQuery)
    {
      $result = $this
        ->_getCouchbaseInstance()
        ->fetch($couchQuery->getId());

      return $result;
    }

    // ########################################

    /**
     * @param CouchQueryBuilder $couchQuery
     * @return array
     */
    public function fetchMulti(CouchQueryBuilder $couchQuery)
    {
      $result = $this
        ->_getCouchbaseInstance()
        ->fetchMulti($couchQuery->getIdsMany());

      return $result;
    }

    // ########################################

    /**
     * @param CouchQueryBuilder $couchQuery
     * @return mixed
     */
    public function set(CouchQueryBuilder $couchQuery)
    {
      $result = $this
        ->_getCouchbaseInstance()
        ->set($couchQuery->getId(), $couchQuery->getData());

      return $result;
    }

    // ########################################

    /**
     * @param CouchQueryBuilder $couchQuery
     * @return mixed
     */
    public function setMulti(CouchQueryBuilder $couchQuery)
    {
      $result = $this
        ->_getCouchbaseInstance()
        ->setMulti($couchQuery->getDataMany());

      return $result;
    }

    // ########################################

    /**
     * @param CouchQueryBuilder $couchQuery
     * @return mixed
     */
    public function delete(CouchQueryBuilder $couchQuery)
    {
      $result = $this
        ->_getCouchbaseInstance()
        ->delete($couchQuery->getId());

      return $result;
    }

    // ########################################

    /**
     * @param CouchQueryBuilder $couchQuery
     * @return mixed
     */
    public function getView(CouchQueryBuilder $couchQuery)
    {
      $result = $this
        ->_getCouchbaseInstance()
        ->getView($couchQuery->getViewDocName(), $couchQuery->getViewId(), $couchQuery->getViewFilter());

      return $result;
    }
  }
