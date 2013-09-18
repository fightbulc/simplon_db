<?php

    namespace Simplon\Db;

    class SqlQueryHelper
    {
        /**
         * @param $fieldName
         * @param $values
         *
         * @return string
         */
        public static function getInStatementWithIntegers($fieldName, $values)
        {
            return "{$fieldName} IN (" . join(',', $values) . ")";
        }

        // ##########################################

        /**
         * @param $fieldName
         * @param $values
         *
         * @return string
         */
        public static function getInStatementWithStrings($fieldName, $values)
        {
            $_preparedValues = [];

            foreach ($values as $value)
            {
                $_preparedValues[] = "'{$value}'";
            }

            return "{$fieldName} IN (" . join(',', $_preparedValues) . ")";
        }
    }
