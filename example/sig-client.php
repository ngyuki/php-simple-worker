<?php
namespace ngyuki\SimpleWorker;

require __DIR__ . '/../vendor/autoload.php';

$client = new SimpleWorkerClient();
$client->send();
