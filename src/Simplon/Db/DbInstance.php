<?php

    namespace Simplon\Db;

    use Simplon\Db\Library\Couchbase;
    use Simplon\Db\Library\Memcached;
    use Simplon\Db\Library\Mysql;
    use Simplon\Db\Library\Redis\Redis;

    class DbInstance
    {
        /** Instance pools */

        private static $_mysqlPool = array();
        private static $_redisPool = array();
        private static $_couchbasePool = array();
        private static $_memcachedPool = array();

        // ########################################

        /**
         * @param $server
         * @param $database
         * @param $username
         * @param $password
         *
         * @return Mysql
         */
        public static function MySQL($server, $database, $username, $password)
        {
            $poolId = $server . ':' . $database . ':' . $username . ':' . $password;

            if (!isset(DbInstance::$_mysqlPool[$poolId]))
            {
                DbInstance::$_mysqlPool[$poolId] = Mysql::Instance($server, $database, $username, $password);
            }

            return DbInstance::$_mysqlPool[$poolId];
        }

        // ########################################

        /**
         * @param $server
         * @param $database
         * @param int $port
         * @param null $password
         *
         * @return Redis
         */
        public static function Redis($server, $database, $port = 6379, $password = NULL)
        {
            $poolId = $server . ':' . $port . ':' . $database;

            if (!isset(DbInstance::$_mysqlPool[$poolId]))
            {
                DbInstance::$_redisPool[$poolId] = new Redis($server, $database, $port, $password);
            }

            return DbInstance::$_redisPool[$poolId];
        }

        // ########################################

        /**
         * @param $server
         * @param $port
         * @param $username
         * @param $password
         * @param $bucket
         *
         * @return Couchbase
         */
        public static function Couchbase($server, $port, $username, $password, $bucket)
        {
            $poolId = $server . ':' . $port . ':' . $username . ':' . $password . ':' . $bucket;

            if (!isset(DbInstance::$_couchbasePool[$poolId]))
            {
                DbInstance::$_couchbasePool[$poolId] = new Couchbase($server, $port, $username, $password, $bucket);
            }

            return DbInstance::$_couchbasePool[$poolId];
        }

        // ########################################

        /**
         * @param $server
         * @param $port
         * @param $username
         * @param $password
         * @param $bucket
         *
         * @return Memcached
         */
        public static function Memcached($server, $port, $username, $password, $bucket)
        {
            $poolId = $server . ':' . $port . ':' . $username . ':' . $password . ':' . $bucket;

            if (!isset(DbInstance::$_memcachedPool[$poolId]))
            {
                DbInstance::$_memcachedPool[$poolId] = new Memcached($server, $port, $username, $password, $bucket);
            }

            return DbInstance::$_memcachedPool[$poolId];
        }
    }
