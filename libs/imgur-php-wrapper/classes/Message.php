<?php
/**
 * PHP Imgur wrapper 0.1
 * Imgur API wrapper for easy use.
 * @author Vadim Kr.
 * @copyright (c) 2013 bndr
 * @license http://creativecommons.org/licenses/by-sa/3.0/legalcode
 */

namespace Imgur;
class Message
{

    /**
     * @var
     */
    protected $conn;


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
     * Get first messages from ALL conversations
     * @return mixed
     */
    function messages()
    {
        $uri = $this->endpoint . "/messages/";
        return $this->conn->request($uri);
    }

    /**
     * Get single message by id
     * @param string $id
     * @return mixed
     */
    function single($id)
    {
        $uri = $this->endpoint . "/message/" . $id;
        return $this->conn->request($uri);
    }

    /**
     * Get messages IDs
     * @param int $page
     * @return mixed
     */
    function messages_ids($page = 0)
    {
        $uri = $this->endpoint . "/messages/ids/" . $page;
        return $this->conn->request($uri);
    }

    /**
     * Get messages count
     * @return mixed
     */
    function message_count()
    {
        $uri = $this->endpoint . "/messages/count";
        return $this->conn->request($uri);

    }

    /**
     * Get a conversation thread
     * @param string $id
     * @return mixed
     */
    function get_thread($id)
    {
        $uri = $this->endpoint . "/message/" . $id . "/thread";
        return $this->conn->request($uri);

    }

    /**
     * Create a new mesage
     * @param array $options
     * @return mixed
     */
    function create($options)
    {
        $uri = $this->endpoint . "/message/";
        return $this->conn->request($uri, $options, "POST");
    }

    /**
     * Delete a message
     * @param string $id
     * @return mixed
     */
    function delete($id)
    {
        $uri = $this->endpoint . "/message/" . $id;
        return $this->conn->request($uri, array("delete" => true), "DELETE");
    }

    /**
     * Report the sender
     * @param string $username
     * @return mixed
     */
    function report_sender($username)
    {
        $uri = $this->endpoint . "/message/report/" . $username;
        return $this->conn->request($uri, array("report" => true), "POST");
    }

    /**
     * Block sender
     * @param string $username
     * @return mixed
     */
    function block_sender($username)
    {
        $uri = $this->endpoint . "/message/block/" . $username;
        return $this->conn->request($uri, array("block" => true), "POST");
    }


}