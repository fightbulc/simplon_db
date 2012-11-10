<?php

  namespace Simplon\Db;

  class SqlManager
  {
    /** @var \Simplon\Db\Library\Mysql */
    protected $_mysqlInstance;

    // ########################################

    /**
     * @param \Simplon\Db\Library\Mysql $mysqlInstance
     */
    public function __construct(\Simplon\Db\Library\Mysql $mysqlInstance)
    {
      $this->_mysqlInstance = $mysqlInstance;
    }

    // ########################################

    /**
     * @return \Simplon\Db\Library\Mysql
     */
    protected function _getSqlInstance()
    {
      return $this->_mysqlInstance;
    }

    // ########################################

    /**
     * @param \Simplon\Db\SqlQueryBuilder $sqlQuery
     * @return null
     */
    public function fetchColumn(\Simplon\Db\SqlQueryBuilder $sqlQuery)
    {
      return $this
        ->_getSqlInstance()
        ->FetchValue($sqlQuery->getQuery(), $sqlQuery->getConditions());
    }

    // ########################################

    /**
     * @param \Simplon\Db\SqlQueryBuilder $sqlQuery
     * @return mixed
     */
    public function fetchRow(\Simplon\Db\SqlQueryBuilder $sqlQuery)
    {
      $result = $this
        ->_getSqlInstance()
        ->FetchArray($sqlQuery->getQuery(), $sqlQuery->getConditions());

      return $result;
    }

    // ########################################

    /**
     * @param \Simplon\Db\SqlQueryBuilder $sqlQuery
     * @return mixed
     */
    public function fetchAll(\Simplon\Db\SqlQueryBuilder $sqlQuery)
    {
      $result = $this
        ->_getSqlInstance()
        ->FetchAll($sqlQuery->getQuery(), $sqlQuery->getConditions());

      return $result;
    }

    // ########################################

    /**
     * @param \Simplon\Db\SqlQueryBuilder $sqlQuery
     * @return bool|null|string
     */
    public function insert(\Simplon\Db\SqlQueryBuilder $sqlQuery)
    {
      $tableName = $sqlQuery->getTableName();
      $data = $sqlQuery->getData();

      if($tableName && ! empty($data))
      {
        // prepare placeholders and values
        $_set = array();
        $_placeholder = array();
        $_values = array();

        foreach($data as $key => $value)
        {
          $_set[] = $key;
          $placeholder_key = ':' . $key;

          // only ID field gets autoincrement
          if(is_null($value))
          {
            $placeholder_key = 'NULL';
          }
          else
          {
            $_values[$key] = $value;
          }

          $_placeholder[] = $placeholder_key;
        }

        $insertString = 'INSERT';

        // insert ignore awareness for tables with unique entries
        if($sqlQuery->getInsertIgnore() === TRUE)
        {
          $insertString = 'INSERT IGNORE';
        }

        // sql statement
        $sql = $insertString . ' INTO ' . $tableName . ' (' . join(',', $_set) . ') VALUES (' . join(',', $_placeholder) . ')';

        // insert data
        $insertId = $this
          ->_getSqlInstance()
          ->ExecuteSQL($sql, $_values);

        return $insertId;
      }

      return FALSE;
    }

    // ########################################

    /**
     * @param \Simplon\Db\SqlQueryBuilder $sqlQuery
     * @return bool|null|string
     */
    public function update(\Simplon\Db\SqlQueryBuilder $sqlQuery)
    {
      $tableName = $sqlQuery->getTableName();
      $newData = $sqlQuery->getData();
      $updateConditions = $sqlQuery->getConditions();

      if($tableName && ! empty($newData) && ! empty($updateConditions))
      {
        // prepare placeholders and values
        $_set = array();
        $_values = array();

        foreach($newData as $key => $value)
        {
          $placeholder_key = ':' . $key;
          $_set[] = $key . '=' . $placeholder_key;
          $_values[$key] = $value;
        }

        // prepare conditions
        $_conditions = array();

        foreach($updateConditions as $key => $value)
        {
          /**
           * Case NULL to enable conditions such as:
           * IN (1,2,3,4,5)
           */
          if(is_null($value))
          {
            $_conditions[] = $key;
          }
          else
          {
            /**
             * wrap key to prevent duplication with $_values keys
             */
            $placeholder_key = ':_simplon_condition_' . $key;
            $_conditions[] = $key . '= ' . $placeholder_key;
            $_values[substr($placeholder_key, 1)] = $value;
          }
        }

        // sql statement
        $sql = 'UPDATE ' . $tableName . ' SET ' . join(',', $_set) . ' WHERE ' . join(' AND ', $_conditions);

        // update data
        return $this
          ->_getSqlInstance()
          ->ExecuteSQL($sql, $_values);
      }

      return FALSE;
    }

    // ########################################

    /**
     * @param \Simplon\Db\SqlQueryBuilder $sqlQuery
     * @return bool|null|string
     */
    public function remove(\Simplon\Db\SqlQueryBuilder $sqlQuery)
    {
      $tableName = $sqlQuery->getTableName();
      $deleteConditions = $sqlQuery->getConditions();

      // remove from sql
      if($tableName && ! empty($deleteConditions))
      {
        // prepare conditions
        $_conditions = array();
        $_values = array();

        foreach($deleteConditions as $key => $value)
        {
          /**
           * Case NULL to enable conditions such as:
           * IN (1,2,3,4,5)
           */
          if(is_null($value))
          {
            $_conditions[] = $key;
          }
          else
          {
            $_conditions[] = $key . '= :' . $key;
            $_values[$key] = $value;
          }
        }

        // sql statement
        $sql = 'DELETE FROM ' . $tableName . ' WHERE ' . join(' AND ', $_conditions);

        // remove data
        return $this
          ->_getSqlInstance()
          ->ExecuteSQL($sql, $_values);
      }

      return FALSE;
    }
  }
