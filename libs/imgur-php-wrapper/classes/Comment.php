<?php
/**
 * PHP Imgur wrapper 0.1
 * Imgur API wrapper for easy use.
 * @author Vadim Kr.
 * @copyright (c) 2013 bndr
 * @license http://creativecommons.org/licenses/by-sa/3.0/legalcode
 */

namespace Imgur;
class Comment
{

    /**
     * @var Connect;
     */
    protected $conn;
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
        $this->id = ($id) ? $id : "";
    }

    /**
     * Get comment by id
     * @return mixed
     */
    function get()
    {
        $uri = $this->endpoint . "/comment/" . $this->id;
        return $this->conn->request($uri);
    }

    /**
     * Create new comment
     * @param $options
     * @return mixed
     */
    function create($options)
    {
        $uri = $this->endpoint . "/comment/" . $this->id;
        return $this->conn->request($uri, $options, "POST");

    }

    /**
     * Delete comment
     * @return mixed
     */
    function delete()
    {

        $uri = $this->endpoint . "/comment/" . $this->id;
        return $this->conn->request($uri, array("delete" => true), "DELETE");

    }

    /**
     * Get all replies to comment with id
     * @return mixed
     */
    function replies()
    {
        $uri = $this->endpoint . "/comment/" . $this->id . "/replies";
        return $this->conn->request($uri);

    }

    /**
     * Vote on comment. Up or Down
     * @param $type
     * @return mixed
     */
    function vote($type)
    {
        $uri = $this->endpoint . "/comment/" . $this->id . "/vote/" . $type;
        return $this->conn->request($uri, array('vote' => true), "POST");
    }

    /**
     * Report comment
     * @return mixed
     */
    function report()
    {
        $uri = $this->endpoint . "/comment/" . $this->id . "/report/";
        return $this->conn->request($uri, array('report' => true), "POST");
    }

    /**
     * Create a reply to a comment
     * @param $options
     * @return mixed
     */
    function reply_create($options)
    {
        $uri = $this->endpoint . "/comment/" . $this->id;
        return $this->conn->request($uri, $options, "POST");
    }

}
