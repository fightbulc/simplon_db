<?php

    namespace Simplon\Db\Library\Redis;

    class RedisHelperCommands
    {
        /**
         * @param $amount
         * @param $multiplier
         *
         * @return mixed
         */
        protected function _getExpireBySeconds($amount, $multiplier)
        {
            return $amount * $multiplier;
        }

        // ######################################

        /**
         * @param $minutes
         *
         * @return mixed
         */
        public function getExpireByMinutes($minutes)
        {
            return $this->_getExpireBySeconds($minutes, 60);
        }

        // ######################################

        /**
         * @param $hours
         *
         * @return mixed
         */
        public function getExpireByHours($hours)
        {
            return $this->_getExpireBySeconds($hours, 60 * 60);
        }

        // ######################################

        /**
         * @param $days
         *
         * @return mixed
         */
        public function getExpireByDays($days)
        {
            return $this->_getExpireBySeconds($days, 60 * 60 * 24);
        }

        // ######################################

        /**
         * @param $weeks
         *
         * @return mixed
         */
        public function getExpireByWeeks($weeks)
        {
            return $this->_getExpireBySeconds($weeks, 60 * 60 * 24 * 7);
        }
    }