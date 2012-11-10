<?php

  namespace Simplon\Db\Abstracts\DAO;

  abstract class AbstractDAO
  {
    /** @var array */
    protected $_data = array();

    /** @var string */
    protected $_idReference = '';

    /** @var array */
    protected $_fieldNames = array();

    /** @var array */
    protected $_fieldTypes = array();

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
    protected function _setData(array $rawData)
    {
      $this->_data = $rawData;

      return $this;
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
     * @return bool
     */
    protected function _getByKey($key)
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

        if($name == 'id_reference')
        {
          $this->_idReference = $value;
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
      $_data = array();
      $fieldNames = $this->_getFieldReferenceNames();

      foreach($fieldNames as $key)
      {
        $_data[$key] = $this->_getByKey($key);
      }

      return $_data;
    }
  }
