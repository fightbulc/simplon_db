<?php

  namespace Simplon\Db;

  class CouchbaseLib
  {
    /**
     * @var \Couchbase
     */
    private $_instance;

    // ########################################

    /**
     * @return \Couchbase
     */
    public function getInstance()
    {
      return $this->_instance;
    }

    // ########################################

    /**
     * @param $server
     * @param $port
     * @param $userName
     * @param $password
     * @param $bucket
     */
    public function __construct($server, $port, $userName, $password, $bucket)
    {
      $this->_instance = new \Couchbase($server . ':' . $port, $userName, $password, $bucket);
    }

    // ########################################

    /**
     * @param $data
     * @return string
     */
    protected function _jsonEncode($data)
    {
      return json_encode($data);
    }

    // ########################################

    /**
     * @param $json
     * @return mixed
     */
    protected function _jsonDecodeAsArray($json)
    {
      return json_decode($json, TRUE);
    }

    // ########################################

    /**
     * @param string $cacheId
     * @return array
     */
    public function get($cacheId)
    {
      $result = array();

      $jsonData = $this
        ->getInstance()
        ->get($cacheId);

      if(! empty($jsonData))
      {
        $result = $this->_jsonDecodeAsArray($jsonData);
      }

      return $result;
    }

    // ########################################

    /**
     * @param $cacheIds
     * @return array
     */
    public function getMulti($cacheIds)
    {
      $jsonData = $this
        ->getInstance()
        ->getMulti($cacheIds);

      return $this->_jsonDecodeAsArray($jsonData);
    }

    // ########################################

    /**
     * @param $cacheId
     * @param $data
     * @param int $expireSeconds
     * @return mixed
     */
    public function set($cacheId, $data, $expireSeconds = 0)
    {
      $jsonData = $this->_jsonEncode($data);

      return $this
        ->getInstance()
        ->set($cacheId, $jsonData, $expireSeconds);
    }

    // ########################################

    /**
     * @param string    $cacheId
     * @param array     $data
     * @param int       $expireSeconds
     */
    public function setUnique($cacheId, $data, $expireSeconds = 1)
    {
      $jsonData = $this->_jsonEncode($data);

      $this
        ->getInstance()
        ->add($cacheId, $jsonData, $expireSeconds);
    }

    // ########################################

    /**
     * @param array $data
     * @param int   $expireSeconds
     */
    public function setMulti($data, $expireSeconds = 1)
    {
      $jsonData = $this->_jsonEncode($data);

      $this
        ->getInstance()
        ->setMulti($jsonData, $expireSeconds);
    }

    // ########################################

    /**
     * @param string $cacheId
     * @param int    $expireSeconds
     * @return bool
     */
    public function keepKeyAlive($cacheId, $expireSeconds = 1)
    {
      return $this
        ->getInstance()
        ->touch($cacheId, $expireSeconds);
    }

    // ########################################

    /**
     * @param $cacheId
     * @return mixed
     */
    public function delete($cacheId)
    {
      return $this
        ->getInstance()
        ->delete($cacheId);
    }

    // ########################################

    /**
     * @return bool
     */
    public function flush()
    {
      return $this
        ->getInstance()
        ->flush();
    }
  }