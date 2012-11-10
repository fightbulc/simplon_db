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

    /** @var array */
    protected $_fieldNames = array();

    /** @var array */
    protected $_fieldTypes = array();

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

    protected function _getFieldReferences()
    {
      $reflector = new \ReflectionClass($this);
      $properties = $reflector->getConstants();

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

        elseif(substr($name, 0, 6) === 'field_')
        {
          list($fieldType, $fieldName) = explode(':', $value);
          $this->_fieldNames[$value] = $fieldName;
          $this->_fieldTypes[$fieldName] = $fieldType;
        }
      }
    }

    // ##########################################

    /**
     * @param $fieldTypeName
     * @return mixed
     */
    protected function _getFieldName($fieldTypeName)
    {
      if(strpos($fieldTypeName, ':') !== FALSE)
      {
        list($type, $name) = explode(':', $fieldTypeName);

        return $name;
      }

      return $fieldTypeName;
    }

    // ##########################################

    /**
     * @param $key
     * @param $value
     * @return AbstractDAO|AbstractSqlDAO
     */
    protected function _setByKey($key, $value)
    {
      $key = $this->_getFieldName($key);
      $this->_data[$key] = $value;

      return $this;
    }

    // ##########################################

    /**
     * @param $key
     * @return bool|int|string
     */
    protected function _getByKey($key)
    {
      $key = $this->_getFieldName($key);
      $value = parent::_getByKey($key);
      $type = $this->_fieldTypes[$key];

      return $this->_castTypeValue($type, $value);
    }

    // ##########################################

    /**
     * @param $type
     * @param $value
     * @return int|string
     */
    protected function _castTypeValue($type, $value)
    {
      switch($type)
      {
        case 'i':
          $value = (int)$value;
          break;

        default:
          $value = (string)$value;
      }

      return $value;
    }

    // ##########################################

    /**
     * @return array
     */
    protected function _getFieldReferenceNames()
    {
      return $this->_fieldNames;
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
    protected function _get($fieldValue, $fieldTypeName)
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
     * @return bool
     */
    protected function _create()
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
      $this->_setByKey($this->_idReference, $insertId);

      return $this;
    }

    // ##########################################

    /**
     * @return bool
     */
    protected function _update()
    {
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
     * @return bool
     */
    protected function _delete()
    {
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

    // ##########################################

    /**
     * @return array
     */
    public function export()
    {
      $_data = array();
      $fieldNames = $this->_getFieldReferenceNames();

      foreach($fieldNames as $key)
      {
        $_data[$key] = $this->_getByKey($key);
      }

      return $_data;
    }
  }
