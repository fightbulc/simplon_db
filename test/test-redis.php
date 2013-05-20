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

    $runs = 3;
    $sets = 1000;

    // ############################################

    console('+ Writing <strings> WITHOUT pipeline (runs=' . $runs . ', sets=' . $sets . ')');
    $summary = 0;

    for ($t = 1; $t <= $runs; $t++)
    {
        $start = microtime(TRUE);

        for ($i = 0; $i < $sets; $i++)
        {
            $response = $redisManager
                ->getRedisInstance()
                ->stringSet('string:pipe:OFF:' . $t . '_' . $i, 'hello world ' . $i);
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

    console('+ Writing <strings> WITH pipeline (runs=' . $runs . ', sets=' . $sets . ')');
    $summary = 0;

    for ($t = 1; $t <= $runs; $t++)
    {
        $start = microtime(TRUE);
        $pairs = [];

        for ($i = 0; $i < $sets; $i++)
        {
            $pairs['string:pipe:ON:' . $t . ':' . $i] = 'hello world ' . $i;
        }

        $response = $redisManager
            ->getRedisInstance()
            ->stringSetMulti($pairs);

        $finish = microtime(TRUE);
        $timeTaken = $finish - $start;
        $summary += $timeTaken;

        console('time taken: ' . $timeTaken . ' seconds');
    }

    $summaryPipeOn = ($summary / $runs);

    console('avg. time taken: ' . $summaryPipeOn . ' seconds');
    console('--------------------------------------');
    console('Pipeline = ' . ($summaryPipeOff / $summaryPipeOn) . 'x faster');
    console('');
    console('');

    // ############################################

    console('+ Reading <strings> WITHOUT pipeline (runs=' . $runs . ', sets=' . $sets . ')');
    $summary = 0;

    for ($t = 1; $t <= $runs; $t++)
    {
        $start = microtime(TRUE);

        for ($i = 0; $i < $sets; $i++)
        {
            $response = $redisManager
                ->getRedisInstance()
                ->stringGetData('string:pipe:OFF:' . $t . ':' . $i);
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

    console('+ Reading <strings> WITH pipeline (runs=' . $runs . ', sets=' . $sets . ')');
    $summary = 0;

    for ($t = 1; $t <= $runs; $t++)
    {
        $start = microtime(TRUE);
        $pairs = [];

        for ($i = 0; $i < $sets; $i++)
        {
            $pairs[] = 'string:pipe:ON:' . $t . ':' . $i;
        }

        $response = $redisManager
            ->getRedisInstance()
            ->stringGetDataMulti($pairs);

        $finish = microtime(TRUE);
        $timeTaken = $finish - $start;
        $summary += $timeTaken;

        console('time taken: ' . $timeTaken . ' seconds');
    }

    $summaryPipeOn = ($summary / $runs);

    console('avg. time taken: ' . $summaryPipeOn . ' seconds');
    console('--------------------------------------');
    console('Pipeline = ' . ($summaryPipeOff / $summaryPipeOn) . 'x faster');
    console('');
    console('');

    // ############################################

    $sets = 10000; // friends lists

    console('+ Writing <lists> WITHOUT pipe (runs=' . $runs . ', sets=' . $sets . ')');
    $summary = 0;

    for ($t = 1; $t <= $runs; $t++)
    {
        $start = microtime(TRUE);

        for ($i = 0; $i < $sets; $i++)
        {
            $response = $redisManager
                ->getRedisInstance()
                ->listUnshiftValue('list:pipe:OFF:' . $t . ':' . $i, 'hello world ' . $i);
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

    console('+ Writing <lists> WITH pipe (runs=' . $runs . ', sets=' . $sets . ')');
    $summary = 0;

    for ($t = 1; $t <= $runs; $t++)
    {
        $start = microtime(TRUE);
        $pairs = [];

        for ($i = 0; $i < $sets; $i++)
        {
            $pairs['list:pipe:ON:' . $t . ':' . $i] = 'hello world ' . $i;
        }

        $response = $redisManager
            ->getRedisInstance()
            ->listMultiUnshiftValue($pairs);

        $finish = microtime(TRUE);
        $timeTaken = $finish - $start;
        $summary += $timeTaken;

        console('time taken: ' . $timeTaken . ' seconds');
    }

    $summaryPipeOn = ($summary / $runs);

    console('avg. time taken: ' . $summaryPipeOn . ' seconds');
    console('--------------------------------------');
    console('Pipe = ' . ($summaryPipeOff / $summaryPipeOn) . 'x faster');
    console('');
    console('');

    // ############################################

    $sets = 10000; // friends lists

    console('+ Reading <lists> by range WITHOUT pipe (runs=' . $runs . ', sets=' . $sets . ')');
    $summary = 0;

    for ($t = 1; $t <= $runs; $t++)
    {
        $start = microtime(TRUE);

        for ($i = 0; $i < $sets; $i++)
        {
            $response = $redisManager
                ->getRedisInstance()
                ->listGetDataByRange('list:pipe:OFF:' . $t . ':' . $i, 0, 500);
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

    console('+ Reading <lists> by range WITH pipe (runs=' . $runs . ', sets=' . $sets . ')');
    $summary = 0;

    for ($t = 1; $t <= $runs; $t++)
    {
        $start = microtime(TRUE);
        $pairs = [];

        for ($i = 0; $i < $sets; $i++)
        {
            $pairs[] = 'list:pipe:ON:' . $t . ':' . $i;
        }

        $response = $redisManager
            ->getRedisInstance()
            ->listMultiGetDataByRange($pairs, 0, 50);

        $finish = microtime(TRUE);
        $timeTaken = $finish - $start;
        $summary += $timeTaken;

        console('time taken: ' . $timeTaken . ' seconds');
    }

    $summaryPipeOn = ($summary / $runs);

    console('avg. time taken: ' . $summaryPipeOn . ' seconds');
    console('--------------------------------------');
    console('Pipe = ' . ($summaryPipeOff / $summaryPipeOn) . 'x faster');
    console('');
    console('');

    // ############################################

    function console($line)
    {
        echo "$line\n";
    }