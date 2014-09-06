<?php
/**
 * PHP Imgur wrapper 0.1
 * Imgur API wrapper for easy use.
 * @author Vadim Kr.
 * @copyright (c) 2013 bndr
 * @license http://creativecommons.org/licenses/by-sa/3.0/legalcode
 */

namespace Imgur;
class Notification
{

    /**
     * @var
     */
    protected $conn;

    /**
     * @param $connection
     * @param $endpoint
     */
    function __construct($connection, $endpoint)
    {
        $this->conn = $connection;
        $this->endpoint = $endpoint;
    }

    /**
     * Get all notifications. Either new or all notifications.
     * @param boolean $new
     * @return mixed
     */
    function all($new)
    {
        $uri = $this->endpoint . "/notification?new=" . ($new == true) ? "true" : "false";
        return $this->conn->request($uri);
    }

    /**
     * Get single notification data
     * @param string $id
     * @return mixed
     */
    function single($id)
    {
        $uri = $this->endpoint . "/notification/" . $id;
        return $this->conn->request($uri);
    }

    /**
     * Mark notification as read by id
     * @param string $id
     * @return mixed
     */
    function mark_as_read($id)
    {
        $uri = $this->endpoint . "/notification/" . $id;
        return $this->conn->request($uri, array("mark_as_read" => true), "PUT");
    }


}