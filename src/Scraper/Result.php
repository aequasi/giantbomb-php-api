<?php
namespace Scraper;

class Result
{

    /**
     * @var Fetcher
     */
    protected $fetcher;

    /**
     * @var array
     */
    protected $results;

    function __construct(Fetcher $fetcher, array $response)
    {
        $this->fetcher = $fetcher;
        $this->results = $response['results'];
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->results;
    }
}
