<?php
namespace Scraper;

use Redis;

class Fetcher
{

    protected $redis;

    protected $apiKey;

    protected $dataType;

    protected $id;

    public function __construct(Redis $redis, $apiKey, $dataType, $id = '')
    {
        $this->redis    = $redis;
        $this->apiKey   = $apiKey;
        $this->dataType = $dataType;
        $this->id       = $id;
    }

    public function fetch($offset = 0, $attempt = 0)
    {

        $baseParams = ['api_key' => $this->apiKey, 'format' => 'json', 'offset' => $offset];

        $url = 'http://api.giantbomb.com/' . $this->dataType;

        if ($this->id !== '') {
            $url .= '/' . $this->id;
        }

        $params = http_build_query($baseParams);
        $url .= '/?' . $params;

        if ($this->redis->exists($url)) {
            $response = $this->redis->get($url);
            $response = json_decode($response, true);
        }

        if (!isset($response) || !is_array($response) || !array_key_exists('results', $response)) {
            $response = file_get_contents($url);
            $response = json_decode($response, true);
            if (!is_array($response) || !array_key_exists('results', $response)) {
                if ($attempt == 5) {
                    throw new \Exception();
                }

                printf("Fetching from %s failed. Trying again. Attempt number %d\n", $url, $attempt + 1 );
                return $this->fetch($offset, $attempt + 1);
            }
            $this->redis->set($url, $response);
        }
        $results  = $response['results'];

        if (self::isAssociative($results)) {
            return new Result($this, $response);
        } else {
            return new Results($this, $response);
        }
    }

    private static function isAssociative($arr)
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @return mixed
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \Redis
     */
    public function getRedis()
    {
        return $this->redis;
    }
}
