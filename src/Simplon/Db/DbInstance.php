<?php

  namespace Simplon\Db;

  class DbInstance
  {
    /** Instance pools */

    private static $_mysqlPool = array();
    private static $_couchbasePool = array();
    private static $_memcachePool = array();

    // ########################################

    /**
     * @param $server
     * @param $database
     * @param $username
     * @param $password
     * @return MysqlLib
     */
    public static function MySQL($server, $database, $username, $password)
    {
      $poolId = $server . ':' . $database . ':' . $username . ':' . $password;

      if(! isset(DbInstance::$_mysqlPool[$poolId]))
      {
        DbInstance::$_mysqlPool[$poolId] = MysqlLib::Instance($server, $database, $username, $password);
      }

      return DbInstance::$_mysqlPool[$poolId];
    }

    // ########################################

    /**
     * @param $server
     * @param $port
     * @param $username
     * @param $password
     * @param $bucket
     * @return CouchbaseLib
     */
    public static function Couchbase($server, $port, $username, $password, $bucket)
    {
      $poolId = $server . ':' . $port . ':' . $username . ':' . $password . ':' . $bucket;

      if(! isset(DbInstance::$_couchbasePool[$poolId]))
      {
        DbInstance::$_couchbasePool[$poolId] = new CouchbaseLib($server, $port, $username, $password, $bucket);
      }

      return DbInstance::$_couchbasePool[$poolId];
    }

    // ########################################

    /**
     * @param $server
     * @param $port
     * @param $username
     * @param $password
     * @param $bucket
     * @return MemcachedLib
     */
    public static function Memcached($server, $port, $username, $password, $bucket)
    {
      $poolId = $server . ':' . $port . ':' . $username . ':' . $password . ':' . $bucket;

      if(! isset(DbInstance::$_memcachePool[$poolId]))
      {
        DbInstance::$_memcachePool[$poolId] = new MemcachedLib($server, $port, $username, $password, $bucket);
      }

      return DbInstance::$_memcachePool[$poolId];
    }
  }
