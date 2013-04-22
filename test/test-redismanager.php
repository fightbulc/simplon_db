<?php

    require __DIR__ . '/../vendor/autoload.php';

    $redis = \Simplon\Db\DbInstance::Redis('localhost', 2);
    $redisManager = new \Simplon\Db\RedisManager($redis);

    // lists
    $listKey = 'list:foo';

    $redisManager
        ->getListCommandsInstance()
        ->listPushValue($listKey, 'bar');

    $data = $redisManager
        ->getListCommandsInstance()
        ->listGetData($listKey);

    $json = json_encode($data);

    var_dump(["LIST VALUE FOR KEY {$listKey}...{$json}"]);
