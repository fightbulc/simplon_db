<?php

  namespace Simplon\Db\Abstracts;

  abstract class AbstractSqlManager
  {
    /**
     * @return array
     */
    protected function getMysqlConfig()
    {
      return array(
        'server'   => '',
        'database' => '',
        'username' => '',
        'password' => '',
      );
    }

    // ########################################

    /**
     * @return \Simplon\Db\MysqlLib
     */
    private function getSqlInstance()
    {
      $config = $this->getMysqlConfig();

      return \Simplon\Db\DbInstance::MySQL($config['server'], $config['database'], $config['username'], $config['password']);
    }

    // ########################################

    /**
     * @param SqlQueryBuilder $dbCacheQuery
     * @return null
     */
    protected function fetchColumn(SqlQueryBuilder $dbCacheQuery)
    {
      return $this
        ->getSqlInstance()
        ->FetchValue($dbCacheQuery->getQuery(), $dbCacheQuery->getConditions());
    }

    // ########################################

    /**
     * @param SqlQueryBuilder $dbCacheQuery
     * @return mixed
     */
    protected function fetchRow(SqlQueryBuilder $dbCacheQuery)
    {
      $result = $this
        ->getSqlInstance()
        ->FetchArray($dbCacheQuery->getQuery(), $dbCacheQuery->getConditions());

      return $result;
    }

    // ########################################

    /**
     * @param SqlQueryBuilder $dbCacheQuery
     * @return mixed
     */
    protected function fetchAll(SqlQueryBuilder $dbCacheQuery)
    {
      $result = $this
        ->getSqlInstance()
        ->FetchAll($dbCacheQuery->getQuery(), $dbCacheQuery->getConditions());

      return $result;
    }

    // ########################################

    /**
     * @param SqlQueryBuilder $dbCacheQuery
     * @return bool|null|string
     */
    protected function insert(SqlQueryBuilder $dbCacheQuery)
    {
      $tableName = $dbCacheQuery->getTableName();
      $data = $dbCacheQuery->getData();

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
        if($dbCacheQuery->getInsertIgnore() === TRUE)
        {
          $insertString = 'INSERT IGNORE';
        }

        // sql statement
        $sql = $insertString . ' INTO ' . $tableName . ' (' . join(',', $_set) . ') VALUES (' . join(',', $_placeholder) . ')';

        // insert data
        $insertId = $this
          ->getSqlInstance()
          ->ExecuteSQL($sql, $_values);

        return $insertId;
      }

      return FALSE;
    }

    // ########################################

    /**
     * @param SqlQueryBuilder $dbCacheQuery
     * @return bool|null|string
     */
    protected function update(SqlQueryBuilder $dbCacheQuery)
    {
      $tableName = $dbCacheQuery->getTableName();
      $newData = $dbCacheQuery->getData();
      $updateConditions = $dbCacheQuery->getConditions();

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
          ->getSqlInstance()
          ->ExecuteSQL($sql, $_values);
      }

      return FALSE;
    }

    // ########################################

    /**
     * @param SqlQueryBuilder $dbCacheQuery
     * @return bool|null|string
     */
    protected function remove(SqlQueryBuilder $dbCacheQuery)
    {
      $tableName = $dbCacheQuery->getTableName();
      $deleteConditions = $dbCacheQuery->getConditions();

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
          ->getSqlInstance()
          ->ExecuteSQL($sql, $_values);
      }

      return FALSE;
    }
  }
