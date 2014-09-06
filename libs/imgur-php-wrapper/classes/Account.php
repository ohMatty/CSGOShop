<?php
/**
 * PHP Imgur wrapper 0.1
 * Imgur API wrapper for easy use.
 * @author Vadim Kr.
 * @copyright (c) 2013 bndr
 * @license http://creativecommons.org/licenses/by-sa/3.0/legalcode
 */

namespace Imgur;
class Account
{

    protected $conn;
    protected $endpoint;
    protected $username;

    /**
     * Constructor
     * @param string $username
     * @param Connect $connection
     * @param string $endpoint
     */
    function __construct($username, $connection, $endpoint)
    {
        $this->conn = $connection;
        $this->endpoint = $endpoint;
        $this->username = $username;
    }

    /**
     * Get basic information about the user
     * @return mixed
     */
    function basic()
    {
        $uri = $this->endpoint . "/account/" . $this->username;
        return $this->conn->request($uri);
    }

    /**
     * Create user. $options are used as post_fields
     * @param array $options
     * @return array mixed
     */
    function create($options)
    {
        $uri = $this->endpoint . "/account/" . $this->username;
        return $this->conn->request($uri, $options, "POST");
    }

    /**
     * @return mixed
     */
    function delete()
    {
        $uri = $this->endpoint . "/account/" . $this->username;
        return $this->conn->request($uri, array("delete" => true), "DELETE");
    }

    /**
     * @param $type
     * @return mixed
     */
    function favorites($type)
    {
        $uri = $this->endpoint . "/account/" . $this->username . ($type == 'gallery' ? "/gallery_favorites" : "/favorites");
        return $this->conn->request($uri);
    }

    /**
     * @param int $page
     * @return mixed
     */
    function submissions($page = 0)
    {
        $uri = $this->endpoint . "/account/" . $this->username . "/submissions/" . $page;
        return $this->conn->request($uri);
    }

    /**
     * @param bool $options
     * @return mixed
     */
    function settings($options = FALSE)
    {
        $uri = $this->endpoint . "/account/" . $this->username . "/settings";
        return $this->conn->request($uri, $options, ($options == FALSE) ? "GET" : "POST");
    }

    /**
     * @return mixed
     */
    function stats()
    {
        $uri = $this->endpoint . "/account/" . $this->username . "/stats";
        return $this->conn->request($uri);
    }

    /**
     * @param int $page
     * @return mixed
     */
    function albums($page = 0)
    {
        $uri = $this->endpoint . "/account/" . $this->username . "/albums/" . $page;
        return $this->conn->request($uri);
    }

    /**
     * @param int $page
     * @return mixed
     */
    function images($page = 0)
    {
        $uri = $this->endpoint . "/account/" . $this->username . "/images/" . $page;
        return $this->conn->request($uri);
    }

    /**
     * @param $new
     * @return mixed
     */
    function notifications($new)
    {
        $uri = $this->endpoint . "/account/" . $this->username . "/notifications?new=" . ($new == true) ? "true" : "false";
        return $this->conn->request($uri);
    }

    /**
     * @param $new
     * @return mixed
     */
    function messages($new)
    {
        $uri = $this->endpoint . "/account/" . $this->username . "/notifications/messages?new=" . ($new == true) ? "true" : "false";
        return $this->conn->request($uri);
    }

    /**
     * @param $options
     * @return mixed
     */
    function send_message($options)
    {
        $uri = $this->endpoint . "/account/" . $this->username . "/message";
        return $this->conn->request($uri, $options, "POST");
    }

    /**
     * @param $new
     * @return mixed
     */
    function replies($new)
    {
        $uri = $this->endpoint . "/account/" . $this->username . "/notifications/replies?new=" . ($new == true) ? "true" : "false";
        return $this->conn->request($uri);
    }


}

