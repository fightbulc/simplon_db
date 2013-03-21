<?php

    namespace Simplon\Db\Library;

    class Memcached
    {
        /**
         * @var \Memcached
         */
        private $_instance;

        // ########################################

        /**
         * @return \Memcached
         */
        public function getInstance()
        {
            return $this->_instance;
        }

        // ########################################

        /**
         * @param $server
         * @param $port
         * @param $userName
         * @param $password
         * @param $bucket
         */
        public function __construct($server, $port, $userName, $password, $bucket)
        {
            $this->_instance = new \Memcached();

            $this->_instance->setOption(\Memcached::SERIALIZER_JSON, TRUE);
            $this->_instance->setOption(\Memcached::OPT_COMPRESSION, FALSE);
            $this->_instance->setOption(\Memcached::OPT_CONNECT_TIMEOUT, 500);
            $this->_instance->setOption(\Memcached::OPT_POLL_TIMEOUT, 500);
            $this->_instance->setOption(\Memcached::OPT_TCP_NODELAY, TRUE);
            $this->_instance->setOption(\Memcached::OPT_NO_BLOCK, TRUE);

            if(! count($this->_instance->getServerList()))
            {
                $this->_instance->addServer($server, $port);
            }
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
         * @param string $cacheId
         * @return array
         */
        public function get($cacheId)
        {
            $jsonData = $this
                ->getInstance()
                ->get($cacheId);

            if(empty($jsonData))
            {
                return FALSE;
            }

            return $this->_jsonDecodeAsArray($jsonData);
        }

        // ########################################

        /**
         * @param array $cacheIds
         * @return array
         */
        public function getMulti(array $cacheIds)
        {
            $jsonData = $this
                ->getInstance()
                ->getMulti($cacheIds);

            return $this->_jsonDecodeAsArray($jsonData);
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

            $this
                ->getInstance()
                ->set($cacheId, $jsonData, $expireSeconds);
        }

        // ########################################

        /**
         * @param array $data
         * @param int $expireSeconds
         */
        public function setMulti(array $data, $expireSeconds = 1)
        {
            $jsonData = array();

            foreach($data as $key => $val)
            {
                $jsonData[$key] = $this->_jsonEncode($val);
            }

            $this
                ->getInstance()
                ->setMulti($jsonData, 0, $expireSeconds);
        }

        // ########################################

        /**
         * @param $cacheId
         * @return mixed
         */
        public function delete($cacheId)
        {
            return $this
                ->getInstance()
                ->delete($cacheId);
        }

        // ########################################

        /**
         * @param int $delayInSeconds
         * @return bool
         */
        public function flush($delayInSeconds = 0)
        {
            return $this
                ->getInstance()
                ->flush($delayInSeconds);
        }
    }
