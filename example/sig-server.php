<?php
namespace ngyuki\SimpleWorker;

foreach (array(__DIR__ . '/../vendor/autoload.php', __DIR__ . '/../../../autoload.php') as $fn)
{
    if (file_exists($fn))
    {
        require $fn;
    }
}

$server = new SimpleWorker();

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
