<?php

  namespace Simplon\Db;

  class MysqlLib extends \EasyPDO
  {
    protected function __construct($connectionString, $username = NULL, $password = NULL)
    {
      define('ERROR_DUPLICATE_KEY', 23000);
      parent::__construct($connectionString, $username, $password);
      $this->PDO->exec('SET NAMES \'utf8\' COLLATE \'utf8_unicode_ci\'');
    }

    // ##########################################

    /**
     * @static
     * @param $server
     * @param $database
     * @param $username
     * @param $password
     * @return \EasyPDO
     */
    public static function Instance($server, $database, $username, $password)
    {
      $connectionString = 'mysql:host=' . $server . ';dbname=' . $database;
      $poolId = $connectionString;

      if(! array_key_exists($poolId, \EasyPDO::$Instance))
      {
        \EasyPDO::$Instance[$poolId] = new MysqlLib($connectionString, $username, $password);

        // we want results back as ASSOC Array
        \EasyPDO::SetFetchMode(\EasyPDO::FETCH_MODE_ASSOCIATIVE_ARRAY);
      }

      return \EasyPDO::$Instance[$poolId];
    }
  }