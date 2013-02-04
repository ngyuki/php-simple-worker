<?php
namespace ngyuki\SimpleWorker;

foreach (array(__DIR__ . '/../vendor/autoload.php', __DIR__ . '/../../../autoload.php') as $fn)
{
    if (file_exists($fn))
    {
        require $fn;
    }
}

$client = new SimpleWorkerClient();
$client->send();
