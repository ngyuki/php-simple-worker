<?php
namespace ngyuki\SimpleWorker;

require __DIR__ . '/../vendor/autoload.php';

$server = new SimpleWorkerServer();

$server->setLogger(function ($log) {
    fputs(STDERR, "$log\n");
});

$server->init();

do
{
    for ($i=0; $i<160; $i++)
    {
        echo ".";
        usleep(10000);
    }

    echo  "\n";
}
while ($server->wait(5));

$server->fin();
