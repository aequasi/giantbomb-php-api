<?php
/**
 * @author    Aaron Scherer
 * @date      12/28/13
 * @copyright Underground Elephant
 */

namespace Scraper;

use Symfony\Component\Yaml\Yaml;

class Dumper
{

    protected $dir;

    function __construct($dir)
    {
        if (!file_exists($dir)) {
            mkdir($dir, 0775, true);
        }
        $this->dir = $dir;
    }

    public function dump($name, array $data)
    {
        $file = $this->dir . '/' . $name . '.yml';
        if (!file_exists(dirname($file))) {
            mkdir(dirname($file), 0775, true);
        }
        file_put_contents($file, Yaml::dump($data, 5));
    }
}
