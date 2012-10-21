<?php

  namespace Simplon\Db\Abstracts;

  class AbstractDbCacheQuery
  {
    /**
     * @var bool
     */
    protected $useCache = FALSE;

    /**
     * Cache invalidation in seconds
     *
     * @var int
     */
    protected $cacheExpiration = 0; // = persistent

    /**
     * @var string
     */
    protected $cacheId;

    /**
     * @var bool
     */
    protected $cacheReIndex;

    /**
     * @var string
     */
    protected $sqlTable;

    /**
     * @var string
     */
    protected $sqlQuery;

    /**
     * @var string
     */
    protected $preparedSqlQuery;

    /**
     * @var bool
     */
    protected $sqlInsertIgnore = FALSE;

    /**
     * @var array
     */
    protected $data = array();

    /**
     * @var array
     */
    protected $sqlConditions = array();

    // ##########################################

    /**
     * @return AbstractDbCacheQuery
     */
    public static function init()
    {
        return new AbstractDbCacheQuery();
    }

    // ##########################################

    /**
     * @param bool $useCache
     * @return AbstractDbCacheQuery
     */
    public function setCacheUse($useCache)
    {
      $this->useCache = $useCache;

      return $this;
    }

    /**
     * @return bool
     */
    public function getUseCache()
    {
      return $this->useCache;
    }

    // ##########################################

    /**
     * @param $cacheExpires int
     * @return AbstractDbCacheQuery
     */
    public function setCacheExpiration($cacheExpires)
    {
      $this->cacheExpiration = $cacheExpires;

      return $this;
    }

    /**
     * @return int
     */
    public function getCacheExpiration()
    {
      return $this->cacheExpiration;
    }

    // ##########################################

    /**
     * @param $cacheId
     * @return AbstractDbCacheQuery
     */
    public function setCacheId($cacheId)
    {
      $this->cacheId = $cacheId;

      return $this;
    }

    /**
     * @return string
     */
    public function getCacheId()
    {
      return $this->cacheId;
    }

    // ##########################################

    /**
     * @param $sqlConditions array
     * @return AbstractDbCacheQuery
     */
    public function setSqlConditions($sqlConditions)
    {
      $this->sqlConditions = $sqlConditions;

      return $this;
    }

    /**
     * @param $sqlCondition
     * @return bool
     */
    public function removeSqlCondition($sqlCondition)
    {
      if(isset($this->sqlConditions[$sqlCondition]))
      {
        unset($this->sqlConditions[$sqlCondition]);

        return TRUE;
      }

      return FALSE;
    }

    /**
     * @return array
     */
    public function getSqlConditions()
    {
      return $this->sqlConditions;
    }

    /**
     * @return bool
     */
    public function hasSqlConditions()
    {
      return count($this->getSqlConditions()) > 0 ? TRUE : FALSE;
    }

    // ##########################################

    /**
     * @param $sqlQuery string
     * @return AbstractDbCacheQuery
     */
    public function setSqlQuery($sqlQuery)
    {
      $this->sqlQuery = $sqlQuery;
      $this->_setPreparedSqlQuery($sqlQuery);

      return $this;
    }

    /**
     * @return string
     */
    public function getSqlQuery()
    {
      return $this->_getPreparedSqlQuery();
    }

    // ##########################################

    /**
     * @param $sqlQuery
     * @return AbstractDbCacheQuery
     */
    protected function _setPreparedSqlQuery($sqlQuery)
    {
      $this->preparedSqlQuery = $sqlQuery;

      return $this;
    }

    /**
     * @return string
     */
    protected function _getPreparedSqlQuery()
    {
      foreach($this->sqlConditions as $key => $val)
      {
        if(strpos($this->preparedSqlQuery, '{{' . $key . '}}') !== FALSE)
        {
          $this->preparedSqlQuery = str_replace('{{' . $key . '}}', $val, $this->preparedSqlQuery);
          $this->removeSqlCondition($key);
        }
      }

      return $this->preparedSqlQuery;
    }

    // ##########################################

    /**
     * @param array $data
     * @return AbstractDbCacheQuery
     */
    public function setData($data)
    {
      $this->data = $data;

      return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
      return $this->data;
    }

    // ##########################################

    /**
     * @param $sqlTable string
     * @return AbstractDbCacheQuery
     */
    public function setSqlTable($sqlTable)
    {
      $this->sqlTable = $sqlTable;

      return $this;
    }

    /**
     * @return string
     */
    public function getSqlTable()
    {
      return $this->sqlTable;
    }

    // ##########################################

    /**
     * @param $cacheReIndex
     * @return AbstractDbCacheQuery
     */
    public function setCacheReIndex($cacheReIndex)
    {
      $this->cacheReIndex = $cacheReIndex;

      return $this;
    }

    /**
     * @return boolean
     */
    public function getCacheReIndex()
    {
      return $this->cacheReIndex;
    }

    // ##########################################

    /**
     * @param $sqlInsertIgnore
     * @return AbstractDbCacheQuery
     */
    public function setSqlInsertIgnore($sqlInsertIgnore)
    {
      $this->sqlInsertIgnore = $sqlInsertIgnore;

      return $this;
    }

    /**
     * @return boolean
     */
    public function getSqlInsertIgnore()
    {
      return $this->sqlInsertIgnore;
    }
  }
