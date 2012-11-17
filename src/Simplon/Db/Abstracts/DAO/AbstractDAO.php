<?php

  namespace Simplon\Db\Abstracts\DAO;

  abstract class AbstractDAO
  {
    /** @var array */
    protected $_data = array();

    /** @var \ReflectionClass */
    protected $_classReflector;

    /** @var array */
    protected $_fieldNames = array();

    /** @var array */
    protected $_fieldTypes = array();

    // ##########################################

    /**
     * @return \App\DAO\User\UserCouchDAO
     */
    public static function init()
    {
      return new static;
    }

    // ##########################################

    /**
     * @param $message
     * @throws \Exception
     */
    protected function _throwException($message)
    {
      throw new \Exception($message, 500);
    }

    // ##########################################

    /**
     * @param array $rawData
     * @return AbstractDAO
     */
    public function setData(array $rawData)
    {
      $this->_data = $rawData;

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
     * @param $key
     * @param $value
     * @return AbstractDAO
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
     * @return array|null|string
     */
    protected function _getByKey($key)
    {
      $key = $this->_getFieldName($key);

      if(! isset($this->_data[$key]))
      {
        return NULL;
      }

      $value = $this->_data[$key];

      if(! is_array($value))
      {
        $value = (string)$value;
      }

      return $value;
    }

    // ##########################################

    /**
     * @param $key
     * @return array|bool|int|null|string
     */
    protected function _getByKeyCasted($key)
    {
      $key = $this->_getFieldName($key);

      if(! isset($this->_data[$key]))
      {
        return NULL;
      }

      $type = $this->_fieldTypes[$key];
      $value = $this->_data[$key];

      return $this->_castTypeValue($type, $value);
    }

    // ##########################################

    /**
     * @return array
     */
    protected function _getClassProperties()
    {
      $reflector = new \ReflectionClass($this);

      return $reflector->getConstants();
    }

    // ##########################################

    protected function _getFieldReferences()
    {
      $properties = $this->_getClassProperties();

      foreach($properties as $name => $value)
      {
        $name = strtolower($name);

        if(substr($name, 0, 6) === 'field_')
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
     * @param $type
     * @param $value
     * @return array|bool|int|string
     */
    protected function _castTypeValue($type, $value)
    {
      switch($type)
      {
        case 'i':
          $value = (int)$value;
          break;

        case 'b':
          $value = (bool)$value;
          break;

        case 'a':
          $value = (array)$value;
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
  }
