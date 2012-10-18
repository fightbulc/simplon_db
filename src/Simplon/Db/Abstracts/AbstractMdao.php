<?php

  namespace Simplon\Db\Abstracts;

  class AbstractMdao
  {
    /**
     * @var string
     */
    public $_cacheId;

    /**
     * @var array
     */
    public $_data = array();

    // ##########################################

    /**
     * @param string $key
     * @param $val
     * @return AbstractVo
     */
    public function setByKey($key, $val)
    {
      $this->_data[$key] = $val;

      return $this;
    }

    // ##########################################

    /**
     * @param $key
     * @return mixed
     */
    public function getByKey($key)
    {
      if(array_key_exists($key, $this->_data))
      {
        return $this->_data[$key];
      }

      return FALSE;
    }

    // ##########################################

    /**
     * @return bool
     */
    public function hasData()
    {
      $data = $this->getData();

      return ! empty($data) ? TRUE : FALSE;
    }

    // ##########################################

    /**
     * @param array $data
     * @return AbstractVo
     */
    public function setData($data)
    {
      if(is_array($data))
      {
        $this->_data = $data;
      }

      return $this;
    }

    // ##########################################

    /**
     * @return array
     */
    public function getData()
    {
      return $this->_data;
    }

    // ##########################################

    /**
     * @return \Simplon\Lib\Db\CouchbaseLib
     */
    protected function getCouchbaseInstance()
    {
      return \Simplon\Lib\Db\DbFactory::Couchbase();
    }

    // ##########################################

    /**
     * @param string $cacheId
     */
    public function setCacheId($cacheId)
    {
      $appName = $this->getConfigByKey(array('appName'));
      $this->_cacheId = $appName . '_' . $cacheId;
    }

    // ##########################################

    /**
     * @return string
     */
    public function getCacheId()
    {
      return $this->_cacheId;
    }

    // ##########################################

    /**
     * @return array
     */
    public function getFromCache()
    {
      $data = $this
        ->getCouchbaseInstance()
        ->get($this->getCacheId());

      $this->setData($data);
    }

    // ##########################################

    /**
     * @return string
     */
    public function save()
    {
      return $this
        ->getCouchbaseInstance()
        ->set($this->getCacheId(), $this->getData());
    }
  }
