<?php

  namespace Simplon\Db;

  class DbInstance
  {
    /** Instance pools */

    private static $_mysqlPool = array();
    private static $_couchbasePool = array();
    private static $_memcachedPool = array();

    // ########################################

    /**
     * @param $server
     * @param $database
     * @param $username
     * @param $password
     * @return \Simplon\Db\Library\Mysql
     */
    public static function MySQL($server, $database, $username, $password)
    {
      $poolId = $server . ':' . $database . ':' . $username . ':' . $password;

      if(! isset(DbInstance::$_mysqlPool[$poolId]))
      {
        DbInstance::$_mysqlPool[$poolId] = \Simplon\Db\Library\Mysql::Instance($server, $database, $username, $password);
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
     * @return \Simplon\Db\Library\Couchbase
     */
    public static function Couchbase($server, $port, $username, $password, $bucket)
    {
      $poolId = $server . ':' . $port . ':' . $username . ':' . $password . ':' . $bucket;

      if(! isset(DbInstance::$_couchbasePool[$poolId]))
      {
        DbInstance::$_couchbasePool[$poolId] = new \Simplon\Db\Library\Couchbase($server, $port, $username, $password, $bucket);
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
     * @return \Simplon\Db\Library\Memcached
     */
    public static function Memcached($server, $port, $username, $password, $bucket)
    {
      $poolId = $server . ':' . $port . ':' . $username . ':' . $password . ':' . $bucket;

      if(! isset(DbInstance::$_memcachedPool[$poolId]))
      {
        DbInstance::$_memcachedPool[$poolId] = new \Simplon\Db\Library\Memcached($server, $port, $username, $password, $bucket);
      }

      return DbInstance::$_memcachedPool[$poolId];
    }
  }
