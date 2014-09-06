<?php
/**
 * PHP Imgur wrapper 0.1
 * Imgur API wrapper for easy use.
 * @author Vadim Kr.
 * @copyright (c) 2013 bndr
 * @license http://creativecommons.org/licenses/by-sa/3.0/legalcode
 */

namespace Imgur;
class Upload
{

    /**
     * @var Connect $conn;
     */
    protected $conn;
    /**
     * @var  string $endpoint
     */
    protected $endpoint;

    /**
     * Main constructor
     * @param $connection
     * @param $endpoint
     */
    function __construct($connection, $endpoint)
    {
        $this->conn = $connection;
        $this->endpoint = $endpoint;
    }


    /**
     * Upload a file
     * @param $filep
     * @param array $options
     */
    function file($filep, $options = array())
    {
        $uri = $this->endpoint . "/upload";
        $options['image'] = '@'.$filep;
        return $this->conn->request($uri, $options, "POST");
    }

    /**
     * Upload a base64 encoded string
     * @param $base64
     * @param array $options
     */
    function string($base64, $options = array())
    {
        $uri = $this->endpoint . "/upload";
        $options['image'] = $base64;
        return $this->conn->request($uri, $options, "POST");
    }

    /**
     * Upload file from url
     * @param $url
     * @param array $options
     */
    function url($url, $options = array())
    {
        $uri = $this->endpoint . "/upload";
        $options['image'] = $url;
        return $this->conn->request($uri, $options, "POST");
    }
}