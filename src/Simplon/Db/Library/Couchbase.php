<?php

    namespace Simplon\Db\Library;

    class Couchbase
    {
        /** @var \Couchbase */
        protected $_instance;

        // ########################################

        /**
         * @param $server
         * @param $port
         * @param $username
         * @param $password
         * @param $bucket
         */
        public function __construct($server, $port, $username, $password, $bucket)
        {
            $this->_instance = new \Couchbase($server . ':' . $port, $username, $password, $bucket);
        }

        // ########################################

        /**
         * @return \Couchbase
         */
        protected function _getCouchbaseInstance()
        {
            return $this->_instance;
        }

        // ########################################

        /**
         * @param $data
         * @return string
         */
        protected function _jsonEncode($data)
        {
            return json_encode($data);
        }

        // ########################################

        /**
         * @param $json
         * @return mixed
         */
        protected function _jsonDecodeAsArray($json)
        {
            return json_decode($json, TRUE);
        }

        // ########################################

        /**
         * @param $cacheId
         * @return bool|mixed
         */
        public function fetch($cacheId)
        {
            $jsonData = $this
                ->_getCouchbaseInstance()
                ->get($cacheId);

            if(! empty($jsonData))
            {
                return $this->_jsonDecodeAsArray($jsonData);
            }

            return FALSE;
        }

        // ########################################

        /**
         * @param $cacheIds
         * @return bool|mixed
         */
        public function fetchMulti($cacheIds)
        {
            $jsonData = $this
                ->_getCouchbaseInstance()
                ->getMulti($cacheIds);

            if(! empty($jsonData))
            {
                return $this->_jsonDecodeAsArray($jsonData);
            }

            return FALSE;
        }

        // ########################################

        /**
         * @param $cacheId
         * @param $data
         * @param int $expireSeconds
         * @return mixed
         */
        public function set($cacheId, $data, $expireSeconds = 0)
        {
            $jsonData = $this->_jsonEncode($data);

            return $this
                ->_getCouchbaseInstance()
                ->set($cacheId, $jsonData, $expireSeconds);
        }

        // ########################################

        /**
         * @param string $cacheId
         * @param array $data
         * @param int $expireSeconds
         */
        public function setUnique($cacheId, $data, $expireSeconds = 0)
        {
            $jsonData = $this->_jsonEncode($data);

            return $this
                ->_getCouchbaseInstance()
                ->add($cacheId, $jsonData, $expireSeconds);
        }

        // ########################################

        /**
         * @param array $data
         * @param int $expireSeconds
         */
        public function setMulti($data, $expireSeconds = 0)
        {
            $jsonData = array();

            foreach($data as $key => $val)
            {
                $jsonData[$key] = $this->_jsonEncode($val);
            }

            return $this
                ->_getCouchbaseInstance()
                ->setMulti($jsonData, $expireSeconds);
        }

        // ########################################

        /**
         * @param string $cacheId
         * @param int $expireSeconds
         * @return bool
         */
        public function keepKeyAlive($cacheId, $expireSeconds = 0)
        {
            return $this
                ->_getCouchbaseInstance()
                ->touch($cacheId, $expireSeconds);
        }

        // ########################################

        /**
         * @param $cacheId
         * @return mixed
         */
        public function delete($cacheId)
        {
            return $this
                ->_getCouchbaseInstance()
                ->delete($cacheId);
        }

        // ########################################

        /**
         * @return bool
         */
        public function flush()
        {
            $result = $this
                ->_getCouchbaseInstance()
                ->flush();

            if($result !== FALSE)
            {
                return TRUE;
            }

            return FALSE;
        }

        // ########################################

        /**
         * @param $designDocName
         * @param $viewName
         * @param array $filterOptions
         * @return mixed
         */
        public function getView($designDocName, $viewName, $filterOptions = array())
        {
            return $this
                ->_getCouchbaseInstance()
                ->view($designDocName, $viewName, $filterOptions);
        }
    }