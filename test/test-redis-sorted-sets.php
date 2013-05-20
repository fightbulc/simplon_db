<?php

    require __DIR__ . '/../vendor/autoload.php';

    console('======================================');
    console('REDIS (c-client phpiredis) testing shizzle');
    console('======================================');

    // ############################################

    $redis = \Simplon\Db\DbInstance::Redis('localhost', 1);
    $redisManager = new \Simplon\Db\RedisManager($redis);

    $redisManager
        ->getRedisInstance()
        ->dbFlush(TRUE);

    $runs = 1;
    $friends = 500;

    // ############################################

    console('+ Writing <sortedSets> WITHOUT pipeline (runs=' . $runs . ', friends=' . $friends . ')');
    $summary = 0;

    for ($t = 1; $t <= $runs; $t++)
    {
        $start = microtime(TRUE);

        for ($i = 1; $i <= $friends; $i++)
        {
            $response = $redisManager
                ->getRedisInstance()
                ->sortedSetAddValue('zset:pipe:OFF:' . $t . ':' . $i, 1, '[AID]');
        }

        $finish = microtime(TRUE);
        $timeTaken = $finish - $start;
        $summary += $timeTaken;

        console('time taken: ' . $timeTaken . ' seconds');
    }

    $summaryPipeOff = ($summary / $runs);

    console('avg. time taken: ' . $summaryPipeOff . ' seconds');
    console('--------------------------------------');

    // ++++++++++++++++++++++++++++++++++++++++++++

    console('+ Writing <sortedSets> WITH pipeline (runs=' . $runs . ', friends=' . $friends . ')');
    $summary = 0;

    for ($t = 1; $t <= $runs; $t++)
    {
        $start = microtime(TRUE);
        $pairs = [];

        for ($i = 1; $i <= $friends; $i++)
        {
            $pairs['zset:pipe:OFF:' . $t . ':' . $i] = [1, '[AID]'];
        }

        $response = $redisManager
            ->getRedisInstance()
            ->sortedSetMultiAddValue($pairs);

        $finish = microtime(TRUE);
        $timeTaken = $finish - $start;
        $summary += $timeTaken;

        console('time taken: ' . $timeTaken . ' seconds');
    }

    $summaryPipeOn = ($summary / $runs);

    console('avg. time taken: ' . $summaryPipeOn . ' seconds');
    console('--------------------------------------');
    console('Pipe = ' . ($summaryPipeOff / $summaryPipeOn) . 'x faster');
    console('======================================');
    console('');
    console('');

    // ############################################

    function console($line)
    {
        echo "$line\n";
    }