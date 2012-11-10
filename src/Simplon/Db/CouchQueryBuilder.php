<?php

  namespace Simplon\Db;

  class CouchQueryBuilder
  {
    /** @var string */
    protected $_cacheId = '';

    /** @var array */
    protected $_cacheIdsMany = array();

    /** @var array */
    protected $_data = array();

    /** @var array */
    protected $_dataMany = array();

    /** @var int */
    protected $_expirationInSeconds = 1;

    /** @var string */
    protected $_viewDocName = '';

    /** @var string */
    protected $_viewId = '';

    /** @var array */
    protected $_viewFilter = array();

    // ##########################################

    /**
     * @return CouchQueryBuilder
     */
    public static function init()
    {
      return new CouchQueryBuilder();
    }

    // ##########################################

    /**
     * @param $cacheId
     * @return CouchQueryBuilder
     */
    public function setId($cacheId)
    {
      $this->_cacheId = $cacheId;

      return $this;
    }

    // ##########################################

    /**
     * @return mixed
     */
    public function getId()
    {
      return $this->_cacheId;
    }

    // ##########################################

    /**
     * @param array $cacheIdsMany
     * @return CouchQueryBuilder
     */
    public function setIdsMany(array $cacheIdsMany)
    {
      $this->_cacheIdsMany = $cacheIdsMany;

      return $this;
    }

    // ##########################################

    /**
     * @return mixed
     */
    public function getIdsMany()
    {
      return $this->_cacheIdsMany;
    }

    // ##########################################

    /**
     * @param $data
     * @return CouchQueryBuilder
     */
    public function setData($data)
    {
      $this->_data = $data;

      return $this;
    }

    // ##########################################

    /**
     * @return mixed
     */
    public function getData()
    {
      return $this->_data;
    }

    // ##########################################

    /**
     * @param $dataMany
     * @return CouchQueryBuilder
     */
    public function setDataMany($dataMany)
    {
      $this->_dataMany = $dataMany;

      return $this;
    }

    // ##########################################

    /**
     * @return mixed
     */
    public function getDataMany()
    {
      return $this->_dataMany;
    }

    // ##########################################

    /**
     * @param $expirationInSeconds
     * @return CouchQueryBuilder
     */
    public function setExpirationInSeconds($expirationInSeconds)
    {
      $this->_expirationInSeconds = $expirationInSeconds;

      return $this;
    }

    // ##########################################

    /**
     * @return mixed
     */
    public function getExpirationInSeconds()
    {
      return $this->_expirationInSeconds;
    }

    // ##########################################

    /**
     * @param $name
     * @return CouchQueryBuilder
     */
    public function setViewDocName($name)
    {
      $this->_viewDocName = $name;

      return $this;
    }

    // ##########################################

    /**
     * @return mixed
     */
    public function getViewDocName()
    {
      return $this->_viewDocName;
    }

    // ##########################################

    /**
     * @param $id
     * @return CouchQueryBuilder
     */
    public function setViewId($id)
    {
      $this->_viewId = $id;

      return $this;
    }

    // ##########################################

    /**
     * @return mixed
     */
    public function getViewId()
    {
      return $this->_viewId;
    }

    // ##########################################

    /**
     * @param array $filter
     * @return CouchQueryBuilder
     */
    public function setViewFilter(array $filter)
    {
      $this->_viewFilter = $filter;

      return $this;
    }

    // ##########################################

    /**
     * @return mixed
     */
    public function getViewFilter()
    {
      return $this->_viewFilter;
    }
  }
