<?php

    namespace Simplon\Db;

    use Simplon\Db\Library\Couchbase;

    class CouchbaseManager
    {
        /** @var Couchbase */
        protected $_couchbaseInstance;

        // ########################################

        /**
         * @param Library\Couchbase $instance
         */
        public function __construct(Couchbase $instance)
        {
            $this->_couchbaseInstance = $instance;
        }

        // ########################################

        /**
         * @return Library\Couchbase
         */
        protected function _getCouchbaseInstance()
        {
            return $this->_couchbaseInstance;
        }

        // ########################################

        /**
         * @param CouchQueryBuilder $couchQuery
         *
         * @return bool|mixed
         */
        public function fetch(CouchQueryBuilder $couchQuery)
        {
            $result = $this
                ->_getCouchbaseInstance()
                ->fetch($couchQuery->getId());

            if ($result !== FALSE)
            {
                return $result;
            }

            return FALSE;
        }

        // ########################################

        /**
         * @param CouchQueryBuilder $couchQuery
         *
         * @return bool|array
         */
        public function fetchMulti(CouchQueryBuilder $couchQuery)
        {
            $result = $this
                ->_getCouchbaseInstance()
                ->fetchMulti($couchQuery->getIdsMany());

            if ($result !== FALSE)
            {
                return $result;
            }

            return FALSE;
        }

        // ########################################

        /**
         * @param CouchQueryBuilder $couchQuery
         *
         * @return bool|string
         */
        public function set(CouchQueryBuilder $couchQuery)
        {
            $binaryObjectId = $this
                ->_getCouchbaseInstance()
                ->set($couchQuery->getId(), $couchQuery->getData(), $couchQuery->getExpirationInSeconds());

            if (!empty($binaryObjectId))
            {
                return (string)$binaryObjectId;
            }

            return FALSE;
        }

        // ########################################

        /**
         * @param CouchQueryBuilder $couchQuery
         *
         * @return mixed
         */
        public function setMulti(CouchQueryBuilder $couchQuery)
        {
            return $this
                ->_getCouchbaseInstance()
                ->setMulti($couchQuery->getDataMany(), $couchQuery->getExpirationInSeconds());
        }

        // ########################################

        /**
         * @param CouchQueryBuilder $couchQuery
         *
         * @return mixed
         */
        public function delete(CouchQueryBuilder $couchQuery)
        {
            return $this
                ->_getCouchbaseInstance()
                ->delete($couchQuery->getId());
        }

        // ########################################

        /**
         * @param CouchQueryBuilder $couchQuery
         *
         * @return bool
         */
        public function flushBucket(CouchQueryBuilder $couchQuery)
        {
            if ($couchQuery->getFlushConfirmation())
            {
                return $this
                    ->_getCouchbaseInstance()
                    ->flush();
            }

            return FALSE;
        }

        // ########################################

        /**
         * @param CouchQueryBuilder $couchQuery
         *
         * @return mixed
         */
        public function getView(CouchQueryBuilder $couchQuery)
        {
            return $this
                ->_getCouchbaseInstance()
                ->getView($couchQuery->getViewDocName(), $couchQuery->getViewId(), $couchQuery->getViewFilter());
        }
    }
