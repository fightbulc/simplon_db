<?php

    require __DIR__ . '/../vendor/autoload.php';

    // ############################################

    class UserCouchDao extends \Simplon\Db\Abstracts\DAO\AbstractCouchDAO
    {
        const ID_REFERENCE = 'id';

        const FIELD_ID = 's:id';
        const FIELD_USERNAME = 's:username';
        const FIELD_CREATED = 's:created';
        const FIELD_UPDATED = 's:updated';

        /**
         * @param $value
         *
         * @return UserCouchDao
         */
        public function setId($value)
        {
            $this->_setByKey(UserCouchDao::FIELD_ID, $value);

            return $this;
        }

        public function getId()
        {
            return $this->_getByKey(UserCouchDao::FIELD_ID);
        }

        /**
         * @param $value
         *
         * @return UserCouchDao
         */
        public function setUsername($value)
        {
            $this->_setByKey(UserCouchDao::FIELD_USERNAME, $value);

            return $this;
        }

        public function getUsername()
        {
            return $this->_getByKey(UserCouchDao::FIELD_USERNAME);
        }

        /**
         * @param $value
         *
         * @return UserCouchDao
         */
        public function setCreated($value)
        {
            $this->_setByKey(UserCouchDao::FIELD_CREATED, $value);

            return $this;
        }

        public function getCreated()
        {
            return $this->_getByKey(UserCouchDao::FIELD_CREATED);
        }

        /**
         * @param $value
         *
         * @return UserCouchDao
         */
        public function setUpdated($value)
        {
            $this->_setByKey(UserCouchDao::FIELD_UPDATED, $value);

            return $this;
        }

        public function getUpdated()
        {
            return $this->_getByKey(UserCouchDao::FIELD_UPDATED);
        }
    }

    // ##########################################

    $instance = \Simplon\Db\DbInstance::Couchbase('127.0.0.1', '8091', 'rootuser', 'rootuser', 'beatguide');
    $manager = new \Simplon\Db\CouchbaseManager($instance);
    $userCouchDao = new UserCouchDao($manager);

    // fetch
    $result = $userCouchDao->fetch('tino');
    echo '<pre>';
    var_dump($result);
    echo '</pre>';

    // create
    if ($result === FALSE)
    {
        $userCouchDao
            ->setId('tino')
            ->setUsername('fightbulc')
            ->setCreated(time())
            ->setUpdated(time())
            ->save();

        var_dump($userCouchDao->export());
    }

    // update
    else
    {
        $userCouchDao
            ->setUpdated(time())
            ->save();

        var_dump($userCouchDao->export());
    }

    if (array_key_exists('delete', $_GET))
    {
        $userCouchDao->delete();
        echo "<hr>DELETED";
    }
