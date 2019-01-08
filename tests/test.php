<?php
/**
 * Created by Roquie.
 * E-mail: roquie0@gmail.com
 * GitHub: Roquie
 * Date: 2019-01-08
 */

require_once __DIR__ . '/../vendor/autoload.php';

// auto tests for dummies :D
// TODO unit testing

$ci = new \Roquie\CircleSdk\CircleCI();
$ci->setUsername('microparts');
$ci->setProject('protocall-build-mock');
$results = $ci->triggerNewJob([
    'CIRCLE_JOB' => 'build',
]);

dump($results);
