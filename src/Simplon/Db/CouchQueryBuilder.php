<?php

    namespace Simplon\Db;

    class CouchQueryBuilder
    {
        /** @var string */
        protected $_id = '';

        /** @var string */
        protected $_idTemplate = '{{id}}';

        /** @var array */
        protected $_idsMany = array();

        /** @var array */
        protected $_data = array();

        /** @var array */
        protected $_dataMany = array();

        /** @var int */
        protected $_expirationInSeconds = 1;

        /** @var string */
        protected $_viewDocName = '';

        /** @var string */
        protected $_viewId = '';

        /** @var array */
        protected $_viewFilter = array();

        /** @var int */
        protected $_flushConfirmation = FALSE;

        // ##########################################

        /**
         * @return CouchQueryBuilder
         */
        public static function init()
        {
            return new CouchQueryBuilder();
        }

        // ##########################################

        /**
         * @param $id
         * @param $idTemplate
         *
         * @return mixed
         */
        protected function _parseIdTemplate($id, $idTemplate)
        {
            return str_replace('{{id}}', $id, $idTemplate);
        }

        // ##########################################

        /**
         * @param $idTemplate
         *
         * @return CouchQueryBuilder
         */
        public function setIdTemplate($idTemplate)
        {
            $this->_idTemplate = $idTemplate;

            return $this;
        }

        // ##########################################

        /**
         * @param $id
         *
         * @return CouchQueryBuilder
         */
        public function setId($id)
        {
            $this->_id = $id;

            return $this;
        }

        // ##########################################

        /**
         * @return mixed
         */
        public function getId()
        {
            return $this->_parseIdTemplate($this->_id, $this->_idTemplate);
        }

        // ##########################################

        /**
         * @param array $idsMany
         *
         * @return CouchQueryBuilder
         */
        public function setIdsMany(array $idsMany)
        {
            $this->_idsMany = $idsMany;

            return $this;
        }

        // ##########################################

        /**
         * @return mixed
         */
        public function getIdsMany()
        {
            $_idsMany = array();

            foreach ($this->_idsMany as $id)
            {
                $_idsMany[] = $this->_parseIdTemplate($id, $this->_idTemplate);
            }

            return $_idsMany;
        }

        // ##########################################

        /**
         * @param $data
         *
         * @return CouchQueryBuilder
         */
        public function setData($data)
        {
            $this->_data = $data;

            return $this;
        }

        // ##########################################

        /**
         * @return mixed
         */
        public function getData()
        {
            return $this->_data;
        }

        // ##########################################

        /**
         * @param $dataMany
         *
         * @return CouchQueryBuilder
         */
        public function setDataMany($dataMany)
        {
            $this->_dataMany = $dataMany;

            return $this;
        }

        // ##########################################

        /**
         * @return mixed
         */
        public function getDataMany()
        {
            return $this->_dataMany;
        }

        // ##########################################

        /**
         * @param $expirationInSeconds
         *
         * @return CouchQueryBuilder
         */
        public function setExpirationInSeconds($expirationInSeconds)
        {
            $this->_expirationInSeconds = $expirationInSeconds;

            return $this;
        }

        // ##########################################

        /**
         * @return mixed
         */
        public function getExpirationInSeconds()
        {
            return $this->_expirationInSeconds;
        }

        // ##########################################

        /**
         * @param $use
         *
         * @return CouchQueryBuilder
         */
        public function setFlushConfirmation($use)
        {
            $this->_flushConfirmation = $use !== TRUE ? FALSE : TRUE;

            return $this;
        }

        // ##########################################

        /**
         * @return bool|int
         */
        public function getFlushConfirmation()
        {
            return $this->_flushConfirmation;
        }

        // ##########################################

        /**
         * @param $name
         *
         * @return CouchQueryBuilder
         */
        public function setViewDocName($name)
        {
            $this->_viewDocName = $name;

            return $this;
        }

        // ##########################################

        /**
         * @return mixed
         */
        public function getViewDocName()
        {
            return $this->_viewDocName;
        }

        // ##########################################

        /**
         * @param $id
         *
         * @return CouchQueryBuilder
         */
        public function setViewId($id)
        {
            $this->_viewId = $id;

            return $this;
        }

        // ##########################################

        /**
         * @return mixed
         */
        public function getViewId()
        {
            return $this->_viewId;
        }

        // ##########################################

        /**
         * @param array $filter
         *
         * @return CouchQueryBuilder
         */
        public function setViewFilter(array $filter)
        {
            $this->_viewFilter = $filter;

            return $this;
        }

        // ##########################################

        /**
         * @return mixed
         */
        public function getViewFilter()
        {
            return $this->_viewFilter;
        }
    }
