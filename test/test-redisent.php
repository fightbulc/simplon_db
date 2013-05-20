<?php

    require __DIR__ . '/../vendor/autoload.php';

    console('======================================');
    console('REDIS (socket redisent) testing shizzle');
    console('======================================');

    // ############################################

    require_once 'Redisent.php';
    $redis = new \Redisent\Redisent('redis://localhost');
    $redis->select(1);
    $redis->flushdb();

    $runs = 5;
    $sets = 100000;

    // ############################################

    // write without pipeline
    console('+ Writing <strings> WITHOUT pipeline (runs=' . $runs . ', sets=' . $sets . ')');
    $summary = 0;

    for ($t = 1; $t <= $runs; $t++)
    {
        $start = microtime(TRUE);

        for ($i = 0; $i < $sets; $i++)
        {
            $response = $redis->set('pipe:OFF:socket:' . $t . '_' . $i, 'hello world ' . $i);
        }

        $finish = microtime(TRUE);
        $timeTaken = $finish - $start;
        $summary += $timeTaken;

        console('time taken: ' . $timeTaken . ' seconds');
    }

    $summaryPipeOff = ($summary / $runs);

    console('avg. time taken: ' . $summaryPipeOff . ' seconds');
    console('--------------------------------------');

    // ############################################

    // write with pipeline
    console('+ Writing <strings> WITH pipeline (runs=' . $runs . ', sets=' . $sets . ')');
    $summary = 0;

    for ($t = 1; $t <= $runs; $t++)
    {
        $start = microtime(TRUE);
        $redis->pipeline();

        for ($i = 0; $i < $sets; $i++)
        {
            $redis->set('pipe:ON:socket:' . $t . '_' . $i, 'hello world ' . $i);
        }

        $response = $redis->uncork();

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

    function console($line)
    {
        echo "$line\n";
    }