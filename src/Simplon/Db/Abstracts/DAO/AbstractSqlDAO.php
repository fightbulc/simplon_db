<?php

  namespace Simplon\Db\Abstracts\DAO;

  abstract class AbstractSqlDAO extends AbstractDAO
  {
    /** @var \Simplon\Db\SqlManager */
    protected $_sqlManagerInstance;

    /** @var string */
    protected $_idReference = '';

    /** @var string */
    protected $_tableName = '';

    // ##########################################

    /**
     * @param \Simplon\Db\SqlManager $sqlManagerInstance
     */
    public function __construct(\Simplon\Db\SqlManager $sqlManagerInstance)
    {
      $this->_sqlManagerInstance = $sqlManagerInstance;

      // get field references
      $this->_getFieldReferences();
    }

    // ##########################################

    /**
     * @param $message
     */
    protected function _throwException($message)
    {
      parent::_throwException(__CLASS__ . ': ' . $message);
    }

    // ##########################################

    /**
     * @return \Simplon\Db\SqlManager
     */
    protected function _getSqlManagerInstance()
    {
      return $this->_sqlManagerInstance;
    }

    // ##########################################

    /**
     * @return string
     */
    protected function _getIdReferenceName()
    {
      if(empty($this->_idReference))
      {
        $this->_throwException(__CLASS__ . ': idReferenceName is missing.');
      }

      return $this->_idReference;
    }

    // ##########################################

    /**
     * @return bool
     */
    protected function _hasIdReferenceName()
    {
      $idName = $this->_getIdReferenceName();

      return ! empty($idName);
    }

    // ##########################################

    /**
     * @return bool|mixed
     */
    protected function _getIdReferenceValue()
    {
      return $this->_getByKey($this->_getIdReferenceName());
    }

    // ##########################################

    /**
     * @return bool
     */
    protected function _hasIdReferenceValue()
    {
      $idValue = $this->_getIdReferenceValue();

      return ! empty($idValue);
    }

    // ##########################################

    protected function _getFieldReferences()
    {
      // get field references
      parent::_getFieldReferences();

      // get id and table reference
      $properties = $this->_getClassProperties();

      foreach($properties as $name => $value)
      {
        $name = strtolower($name);

        if($name == 'id_reference')
        {
          $this->_idReference = $value;
        }

        elseif($name == 'table_name')
        {
          $this->_tableName = $value;
        }
      }
    }

    // ##########################################

    /**
     * @return string
     */
    protected function _getTableReferenceName()
    {
      return $this->_tableName;
    }

    // ##########################################

    /**
     * @return bool
     */
    protected function _hasTableReferenceName()
    {
      return $this->_getTableReferenceName() != '' ? TRUE : FALSE;
    }

    // ##########################################

    /**
     * @return string
     */
    protected function _getQueryTemplate()
    {
      return '
      SELECT
        {{fieldReferenceNames}}

      FROM
        {{tableReferenceName}}

      WHERE
        {{fieldName}} = :fieldValue

      LIMIT 1
      ';
    }

    // ##########################################

    /**
     * @param $fieldName
     * @return mixed|string
     */
    protected function _getParsedQueryTemplate($fieldName)
    {
      $placeholders = array(
        'fieldReferenceNames' => join(', ', $this->_getFieldReferenceNames()),
        'tableReferenceName'  => $this->_getTableReferenceName(),
        'fieldName'           => $fieldName,
      );

      $sqlQueryTemplate = $this->_getQueryTemplate();

      foreach($placeholders as $key => $val)
      {
        $sqlQueryTemplate = str_replace('{{' . $key . '}}', $val, $sqlQueryTemplate);
      }

      return $sqlQueryTemplate;
    }

    // ##########################################

    /**
     * @param $fieldValue
     * @param $fieldTypeName
     * @return AbstractDAO|AbstractSqlDAO
     */
    protected function fetch($fieldValue, $fieldTypeName = 'i:id')
    {
      if($this->_hasTableReferenceName() === FALSE)
      {
        $this->_throwException('Missing TableReferenceName.');
      }

      // get field name
      $fieldName = $this->_getFieldName($fieldTypeName);

      // prepare data
      $parsedSqlQuery = $this->_getParsedQueryTemplate($fieldName);
      $conditions = array('fieldValue' => $fieldValue);

      // build query
      $sqlQuery = \Simplon\Db\SqlQueryBuilder::init()
        ->setQuery($parsedSqlQuery)
        ->setConditions($conditions);

      // fetch row
      $result = $this
        ->_getSqlManagerInstance()
        ->fetchRow($sqlQuery);

      // no result exception
      if($result === FALSE)
      {
        return FALSE;
      }

      // set data
      $this->_setData($result);

      return $this;
    }

    // ##########################################

    /**
     * @return array
     */
    protected function _getPreparedCreateUpdateData()
    {
      $data = array();
      $fieldReferenceNames = $this->_getFieldReferenceNames();

      foreach($fieldReferenceNames as $key)
      {
        $data[$key] = $this->_getByKey($key);
      }

      return $data;
    }

    // ##########################################

    /**
     * @return bool|AbstractSqlDAO
     */
    public function create()
    {
      if($this->_hasTableReferenceName() === FALSE)
      {
        $this->_throwException('Missing TableReferenceName.');
      }

      // prepare data
      $preparedData = $this->_getPreparedCreateUpdateData();

      // build query
      $sqlQuery = \Simplon\Db\SqlQueryBuilder::init()
        ->setTableName($this->_getTableReferenceName())
        ->setData($preparedData);

      // insert into DB
      $insertId = $this
        ->_getSqlManagerInstance()
        ->insert($sqlQuery);

      // no result exception
      if($insertId === FALSE)
      {
        return FALSE;
      }

      // set insert id
      $this->_setByKey($this->_getIdReferenceName(), $insertId);

      return $this;
    }

    // ##########################################

    /**
     * @return bool
     */
    protected function _hasReferenceIdAndValue()
    {
      if(! $this->_hasIdReferenceName() || ! $this->_hasIdReferenceValue())
      {
        return FALSE;
      }

      return TRUE;
    }

    // ##########################################

    /**
     * @return bool|AbstractSqlDAO
     */
    public function update()
    {
      if($this->_hasReferenceIdAndValue() === FALSE)
      {
        $this->_throwException('Missing idReferences and/or idReferenceValue.');
      }

      if($this->_hasTableReferenceName() === FALSE)
      {
        $this->_throwException('Missing TableReferenceName.');
      }

      // prepare data
      $preparedData = $this->_getPreparedCreateUpdateData();
      $conditions = array($this->_getIdReferenceName() => $this->_getIdReferenceValue());

      // build query
      $sqlQuery = \Simplon\Db\SqlQueryBuilder::init()
        ->setTableName($this->_getTableReferenceName())
        ->setConditions($conditions)
        ->setData($preparedData);

      // insert into DB
      $response = $this
        ->_getSqlManagerInstance()
        ->update($sqlQuery);

      // no result exception
      if($response === FALSE)
      {
        return FALSE;
      }

      return $this;
    }

    // ##########################################

    /**
     * @return bool|AbstractSqlDAO
     */
    public function delete()
    {
      if($this->_hasReferenceIdAndValue() === FALSE)
      {
        $this->_throwException('Missing idReferences and/or idReferenceValue.');
      }

      if($this->_hasTableReferenceName() === FALSE)
      {
        $this->_throwException('Missing TableReferenceName.');
      }

      // prepare data
      $conditions = array($this->_getIdReferenceName() => $this->_getIdReferenceValue());

      // build query
      $sqlQuery = \Simplon\Db\SqlQueryBuilder::init()
        ->setTableName($this->_getTableReferenceName())
        ->setConditions($conditions);

      // insert into DB
      $response = $this
        ->_getSqlManagerInstance()
        ->remove($sqlQuery);

      // no result exception
      if($response === FALSE)
      {
        return FALSE;
      }

      return $this;
    }
  }
