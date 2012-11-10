<?php

  namespace Simplon\Db\Abstracts\DAO;

  abstract class AbstractDAO
  {
    /** @var array */
    protected $_data = array();

    /** @var string */
    protected $_idReference = '';

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
      if(! isset($this->_data[$key]))
      {
        return NULL;
      }

      return $this->_data[$key];
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
     * @param $fieldValue
     * @param string $fieldName
     * @return bool|AbstractDAO
     */
    public function fetch($fieldValue, $fieldName = 'id')
    {
      if($fieldName && $fieldValue)
      {
        return $this->_get($fieldValue, $fieldName);
      }

      return FALSE;
    }

    // ##########################################

    /**
     * @param $fieldValue
     * @param $fieldName
     * @return AbstractDAO
     */
    protected function _get($fieldValue, $fieldName)
    {
      return $this;
    }

    // ##########################################

    /**
     * @return bool
     */
    public function create()
    {
      return $this->_create();
    }

    // ##########################################

    /**
     * @return bool
     */
    protected function _create()
    {
      return FALSE;
    }

    // ##########################################

    /**
     * @return bool
     */
    public function update()
    {
      if($this->_hasIdReferenceName() && $this->_hasIdReferenceValue())
      {
        return $this->_update();
      }

      return FALSE;
    }

    // ##########################################

    /**
     * @return bool
     */
    protected function _update()
    {
      return FALSE;
    }

    // ##########################################

    /**
     * @return bool
     */
    public function delete()
    {
      if($this->_hasIdReferenceName() && $this->_hasIdReferenceValue())
      {
        return $this->_delete();
      }

      return FALSE;
    }

    // ##########################################

    /**
     * @return bool
     */
    protected function _delete()
    {
      return FALSE;
    }

    // ##########################################

    /**
     * @return array
     */
    public function export()
    {
      return $this->_data;
    }
  }
