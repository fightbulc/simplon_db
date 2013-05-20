<?php

    namespace Simplon\Db\Library;

    class Mysql extends \EasyPDO
    {
        protected function __construct($connectionString, $username = NULL, $password = NULL)
        {
            define('ERROR_DUPLICATE_KEY', 23000);
            parent::__construct($connectionString, $username, $password);
            $this->PDO->exec('SET NAMES \'utf8\' COLLATE \'utf8_unicode_ci\'');
        }

        // ##########################################

        /**
         * @static
         *
         * @param $server
         * @param $database
         * @param $username
         * @param $password
         *
         * @return \EasyPDO
         */
        public static function Instance($server, $database, $username, $password)
        {
            if (!\EasyPDO::$Instance)
            {
                $connectionString = 'mysql:host=' . $server . ';dbname=' . $database;
                \EasyPDO::$Instance = new Mysql($connectionString, $username, $password);

                // we want results back as ASSOC Array
                \EasyPDO::SetFetchMode(\EasyPDO::FETCH_MODE_ASSOCIATIVE_ARRAY);
            }

            return \EasyPDO::$Instance;
        }
    }