<?php
/**
 * PHP Imgur wrapper 0.1
 * Imgur API wrapper for easy use.
 * @author Vadim Kr.
 * @copyright (c) 2013 bndr
 * @license http://creativecommons.org/licenses/by-sa/3.0/legalcode
 */

namespace Imgur;
class Album
{

    /**
     * @var
     */
    protected $conn;
    /**
     * @var
     */
    protected $id;

    /**
     * @param $id
     * @param $connection
     * @param $endpoint
     */
    function __construct($id, $connection, $endpoint)
    {
        $this->conn = $connection;
        $this->endpoint = $endpoint;
        $this->id = $id;
    }

    /**
     * Get Album by id
     * @return mixed
     */
    function get()
    {
        $uri = $this->endpoint . "/album/" . $this->id;
        return $this->conn->request($uri);
    }

    /**
     * Get images from album
     * @return mixed
     */
    function images()
    {
        $uri = $this->endpoint . "/album/" . $this->id . "/images";
        return $this->conn->request($uri);
    }

    /**
     * Create Album
     * @param $options
     * @return mixed
     */
    function create($options)
    {
        $uri = $this->endpoint . "/album/" . $this->id;
        return $this->conn->request($uri, $options, "POST");
    }

    /**
     * Delete album
     * @return mixed
     */
    function delete()
    {
        $uri = $this->endpoint . "/album/" . $this->id;
        return $this->conn->request($uri, array("delete" => true), "DELETE");
    }

    /**
     * Update album information
     * @param $options
     * @return mixed
     */
    function update($options)
    {
        $uri = $this->endpoint . "/album/" . $this->id;
        return $this->conn->request($uri, $options, "PUT");
    }

    /**
     * Favorite an album
     * @return mixed
     */
    function favorite()
    {
        $uri = $this->endpoint . "/album/" . $this->id . "/favorite";
        return $this->conn->request($uri, array("favorite" => true), "POST");
    }

    /**
     * Add images to album
     * @param $ids_array
     * @return mixed
     */
    function add_images($ids_array)
    {
        $uri = $this->endpoint . "/album/" . $this->id;
        return $this->conn->request($uri, $ids_array, "POST");
    }
}
