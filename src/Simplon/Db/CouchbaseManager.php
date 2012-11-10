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
      return $this
        ->_getCouchbaseInstance()
        ->fetch($couchQuery->getId());
    }

    // ########################################

    /**
     * @param CouchQueryBuilder $couchQuery
     * @return array
     */
    public function fetchMulti(CouchQueryBuilder $couchQuery)
    {
      return $this
        ->_getCouchbaseInstance()
        ->fetchMulti($couchQuery->getIdsMany());
    }

    // ########################################

    /**
     * @param CouchQueryBuilder $couchQuery
     * @return mixed
     */
    public function set(CouchQueryBuilder $couchQuery)
    {
      return $this
        ->_getCouchbaseInstance()
        ->set($couchQuery->getId(), $couchQuery->getData());
    }

    // ########################################

    /**
     * @param CouchQueryBuilder $couchQuery
     * @return mixed
     */
    public function setMulti(CouchQueryBuilder $couchQuery)
    {
      return $this
        ->_getCouchbaseInstance()
        ->setMulti($couchQuery->getDataMany());
    }

    // ########################################

    /**
     * @param CouchQueryBuilder $couchQuery
     * @return mixed
     */
    public function delete(CouchQueryBuilder $couchQuery)
    {
      return $this
        ->_getCouchbaseInstance()
        ->delete($couchQuery->getId());
    }

    // ########################################

    /**
     * @param CouchQueryBuilder $couchQuery
     * @return mixed
     */
    public function getView(CouchQueryBuilder $couchQuery)
    {
      return $this
        ->_getCouchbaseInstance()
        ->getView($couchQuery->getViewDocName(), $couchQuery->getViewId(), $couchQuery->getViewFilter());
    }
  }
