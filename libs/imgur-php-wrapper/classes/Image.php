<?php
/**
 * PHP Imgur wrapper 0.1
 * Imgur API wrapper for easy use.
 * @author Vadim Kr.
 * @copyright (c) 2013 bndr
 * @license http://creativecommons.org/licenses/by-sa/3.0/legalcode
 */

namespace Imgur;
class Image
{

    /**
     * @var
     */
    protected $conn;
    /**
     * @var string
     */
    protected $endpoint;
    /**
     * @var string
     */
    protected $id;

    /**
     * @param string $id
     * @param $connection
     * @param string $endpoint
     */
    function __construct($id, $connection, $endpoint)
    {
        $this->conn = $connection;
        $this->endpoint = $endpoint;
        $this->id = $id;
    }

    /**
     * Get message by id
     * @return mixed
     */
    function get()
    {

        $uri = $this->endpoint . "/image/" . $this->id;
        return $this->conn->request($uri);

    }

    /**
     * Update message by id
     * @param $options
     * @return mixed
     */
    function update($options)
    {
        $uri = $this->endpoint . "/image/" . $this->id;
        return $this->conn->request($uri, $options, "PUT");
    }

    /**
     * Delete message by id
     * @return mixed
     */
    function delete()
    {
        $uri = $this->endpoint . "/image/" . $this->id;
        return $this->conn->request($uri, array("delete" => true), "DELETE");
    }

    /**
     * Favorite message by id
     * @return mixed
     */
    function favorite()
    {
        $uri = $this->endpoint . "/image/" . $this->id . "/favorite";
        return $this->conn->request($uri, array('favorite' => true), "POST");
    }
}
