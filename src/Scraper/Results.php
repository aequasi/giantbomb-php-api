<?php
namespace Scraper;

class Results implements \Iterator
{

    /**
     * @var Fetcher
     */
    protected $fetcher;

    /**
     * @var array
     */
    protected $results;

    protected $max;

    protected $offset = 0;

    function __construct(Fetcher $fetcher, array $response )
    {
        $this->fetcher = $fetcher;
        $this->results = $response['results'];
        $this->max     = $response['number_of_total_results'];
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->results;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     *
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        return current($this->results);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     *
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        if ($this->offset !== 0 && $this->offset % 100 === 0) {
            $fetcher = new Fetcher(
                $this->fetcher->getRedis(),
                $this->fetcher->getApiKey(),
                $this->fetcher->getDataType()
            );
            $results = $fetcher->fetch($this->offset);
            $this->results = $results->toArray();
            reset($this->results);
        }
        $this->offset++;
        next($this->results);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     *
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return key($this->results);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     *
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     *       Returns true on success or false on failure.
     */
    public function valid()
    {
        return $this->offset < $this->max;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     *
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        reset($this->results);
    }
}
