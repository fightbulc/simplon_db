<?php

  require __DIR__ . '/../vendor/autoload.php';

  echo '<h1>Redis</h1>';

  $redis = \Simplon\Db\DbInstance::Redis('localhost', 0);
  $redisManager = new \Simplon\Db\RedisManager($redis);

  // ############################################

  echo '<h1>Strings</h1>';

  echo '<h3>Set</h3>';
  $response = $redisManager->getRedisInstance()->stringSetMulti(['foobar' => 'hello', 'hello' => 'world']);
  var_dump($response);

  echo '<h3>get</h3>';
  $response = $redisManager->getRedisInstance()->stringGetData('foobar');
  var_dump($response);