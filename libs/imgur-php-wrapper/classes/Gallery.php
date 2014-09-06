<?php
/**
 * PHP Imgur wrapper 0.1
 * Imgur API wrapper for easy use.
 * @author Vadim Kr.
 * @copyright (c) 2013 bndr
 * @license http://creativecommons.org/licenses/by-sa/3.0/legalcode
 */

namespace Imgur;
class Gallery
{

    /**
     * @var
     */
    protected $conn;

    /**
     * Cosntructor
     * @param $connection
     * @param string $endpoint
     */
    function __construct($connection, $endpoint)
    {
        $this->conn = $connection;
        $this->endpoint = $endpoint;
    }

    /**
     * Get data from gallery
     *
     * @param string $section  hot | top | user - defaults to hot
     * @param string $sort     viral | time - defaults to viral
     * @param int $page        integer - the data paging number
     * @return mixed
     */
    function get($section, $sort, $page)
    {
        $uri = $this->endpoint . "/gallery/" . $section . "/" . $sort . "/" . $page;
        return $this->conn->request($uri);
    }

    /**
     * Get subreddit gallery
     * @param $subreddit
     * @param $sort
     * @param $page
     * @param bool $window
     * @return mixed
     */
    function subreddit_gallery($subreddit, $sort, $page, $window = false)
    {
        $uri = $this->endpoint . "/gallery/r/" . $subreddit . "/" . $sort . ($window !== false ? "/" . $window : "") . "/" . $page;
        return $this->conn->request($uri);
    }

    /**
     * Get subreddit image
     * @param $subreddit
     * @param $id
     * @return mixed
     */
    function subreddit_image($subreddit, $id)
    {
        $uri = $this->endpoint . "/gallery/r/" . $subreddit . "/" . $id;
        return $this->conn->request($uri);
    }

    /**
     * Search for a string in gallery
     * @param string $str
     * @return mixed
     */
    function search($str)
    {
        $uri = $this->endpoint . "/gallery/search?q=" . $str;
        return $this->conn->request($uri);
    }

    /**
     * Get random images from gallery. Pagination is available
     * @param int $page
     * @return mixed
     */
    function random($page = 0)
    {
        $uri = $this->endpoint . "/gallery/random/random/" . $page;
        return $this->conn->request($uri);
    }

    /**
     * Submit Image | Album to gallery
     * @param string $id
     * @param $options
     * @param string $type
     * @return mixed
     */
    function submit($id, $options, $type = "image")
    {
        $uri = $this->endpoint . "/gallery/" . $type . "/" . $id;
        return $this->conn->request($uri, $options, "POST");
    }

    /**
     * Remove image from gallery
     * @param string $id
     * @return mixed
     */
    function remove($id)
    {
        $uri = $this->endpoint . "/gallery/" . $id;
        return $this->conn->request($uri, array("remove" => true), "DELETE");
    }

    /**
     * Get album information in gallery
     * @param string $id
     * @return mixed
     */
    function album_info($id)
    {
        $uri = $this->endpoint . "/gallery/album/" . $id;
        return $this->conn->request($uri);
    }

    /**
     * Get Image information in gallery
     * @param string $id
     * @return mixed
     */
    function image_info($id)
    {
        $uri = $this->endpoint . "/gallery/image/" . $id;
        return $this->conn->request($uri);
    }

    /**
     * Report Image | Album
     * @param string $id
     * @param string $type
     * @return mixed
     */
    function report($id, $type = "image")
    {
        $uri = $this->endpoint . "/gallery/" . $type . "/" . $id . "/report";
        return $this->conn->request($uri, array("report" => true), "POST");

    }

    /**
     *  Get votes for Image | Album
     * @param string $id
     * @param string $type
     * @return mixed
     */
    function votes($id, $type = "image")
    {
        $uri = $this->endpoint . "/gallery/" . $type . "/" . $id . "/votes";
        return $this->conn->request($uri);
    }


    /**
     * Vote on Image | ALbum. Votes can be either up or down.
     * @param string $id
     * @param string $type
     * @param string $vote
     * @return mixed
     */
    function vote($id, $type = "image", $vote = "up")
    {
        $uri = $this->endpoint . "/gallery/" . $type . "/" . $id . "/vote/" . $vote;
        return $this->conn->request($uri, array("vote" => true), "POST");
    }

    /**
     * Get comments for Image | Album
     * @param string $id
     * @param string $type
     * @return mixed
     */
    function comments($id, $type)
    {
        $uri = $this->endpoint . "/gallery/" . $type . "/" . $id . "/comments";
        return $this->conn->request($uri);
    }

    /**
     * Get a comment to an image in gallery
     * @param $image_id
     * @param $type
     * @param $comment_id
     * @return mixed
     */
    function comment($image_id, $type, $comment_id)
    {
        $uri = $this->endpoint . "/gallery/" . $type . "/" . $image_id . "/comment/" . $comment_id;
        return $this->conn->request($uri);
    }
}

