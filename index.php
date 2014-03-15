<?php
require __DIR__ . '/vendor/autoload.php';
use Symfony\Component\Yaml\Yaml;

define('APIKEY', 'f0c0548bca5b5c8369862087f880208aa6f7728b');

$start  = time();
$dumper = new \Scraper\Dumper(__DIR__ . '/dumps/' . $start);
$redis  = new \Redis();
$redis->connect('127.0.0.1', 6379);

$types = ['genres', 'platforms', 'accessories', 'games'];

foreach ($types as $type) {
    printf( "\n\n\n\nFetching %s\n_____________________________\n\n\n\n", $type );
    $fetcher = new \Scraper\Fetcher($redis, APIKEY, $type);
    $results = $fetcher->fetch();
    parseAndFetchChildren($dumper, $redis, $type, $results);
}

function parseAndFetchChildren(\Scraper\Dumper $dumper, Redis $redis, $name, $results)
{
    $data = [];
    foreach ($results as $result) {
        $data[] = $result;
        if (isset($result['api_detail_url'])) {
            $matches = [];
            preg_match(
                '/\/api\/(?P<type>\w+)\/?(?P<id>[0-9-]+)?\/$/',
                $result['api_detail_url'],
                $matches
            );
            $fetcher  = new \Scraper\Fetcher($redis, APIKEY, $matches['type'], $matches['id']);
            $typeData = $fetcher->fetch();
            printf("Dumping %s: %s\n\n", $matches['type'], $matches['id']);
            $dumper->dump(
                $matches['type'] . '/' . $matches['id'],
                [$matches['type'] => $typeData->toArray()]
            );
        }
    }

    $dumper->dump($name, [$name => $data]);
}
