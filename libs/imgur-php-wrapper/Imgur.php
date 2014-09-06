<?php
/**
 * PHP Imgur wrapper 0.1
 * Imgur API wrapper for easy use.
 * @author Vadim Kr.
 * @copyright (c) 2013 bndr
 * @license http://creativecommons.org/licenses/by-sa/3.0/legalcode
 */

namespace Imgur;
require_once('classes/Account.php');
require_once('classes/Album.php');
require_once('classes/Authorize.php');
require_once('classes/Comment.php');
require_once('classes/Connect.php');
require_once('classes/Gallery.php');
require_once('classes/Image.php');
require_once('classes/Message.php');
require_once('classes/Notification.php');
require_once('classes/Upload.php');

// TODO: Autoloader doesn't work ):
// spl_autoload_register(function ($cname) {
//     require_once("classes/" . $cname . ".php");
//     throw new Exception("Class " . $cname . " failed to load. Please verify that you uploaded the files correctly.");
// });

class Imgur
{

    /**
     * @var bool|string
     */
    protected $api_key = "";
    /**
     * @var string
     */
    protected $api_secret = "";
    /**
     * @var string
     */
    protected $api_endpoint = "https://api.imgur.com/3";
    /**
     * @var string
     */
    protected $oauth_endpoint = "https://api.imgur.com/oauth2";
    /**
     * @var Connect
     */
    protected $conn;

    /**
     * Imgur Class constructor.
     * @param string $api_key
     * @param string $api_secret
     * @throws
     */
    function __construct($api_key, $api_secret)
    {
        if (!$api_key || !$api_secret) throw Exception("Please provided API key data");

        $this->api_key = $api_key;
        $this->api_secret = $api_secret;
        $this->conn = new Connect($this->api_key, $this->api_secret);
    }

    /**
     * oAuth2 authorization. If the acess_token needs to be refreshed pass $refresh_token as first parameter,
     * if this is the first time getting access_token from user, then set the first parameter to false, pass the auth code
     * in the second.
     * @param bool $refresh_token
     * @param bool $auth_code
     * @return array $tokens
     */
    function authorize($refresh_token = FALSE, $auth_code = FALSE)
    {
        $auth = new Authorize($this->conn, $this->api_key, $this->api_secret);
        $tokens = ($refresh_token) ? $auth->refreshAccessToken($refresh_token) : $auth->getAccessToken($auth_code);
        (!$tokens) ? $auth->getAuthorizationCode() : $this->conn->setAccessData($tokens['access_token'], $tokens['refresh_token']);

        return $tokens;

    }

    /**
     * Upload an image from url, bas64 string or file.
     * @return mixed
     */
    function upload()
    {
        $upload = new Upload($this->conn, $this->api_endpoint);
        return $upload;
    }

    /**
     * Image Wrapper for all image functions
     * @param string $id
     * @return Image
     */
    function image($id = null)
    {
        $image = new Image($id, $this->conn, $this->api_endpoint);
        return $image;
    }

    /**
     * Album wrapper for all album functions.
     * @param string $id
     * @return Album
     */
    function album($id = null)
    {
        $album = new Album($id, $this->conn, $this->api_endpoint);
        return $album;
    }

    /**
     * Account wrapper for all account functions
     * @param string $username
     * @return Account
     */
    function account($username)
    {
        $acc = new Account($username, $this->conn, $this->api_endpoint);
        return $acc;
    }

    /**
     * Gallery wrapper for all functions regarding gallery
     * @return Gallery
     */
    function gallery()
    {
        $gallery = new Gallery($this->conn, $this->api_endpoint);
        return $gallery;
    }

    /**
     * Comment wrapper for all commenting functions
     * @param string $id
     * @return Comment
     */
    function comment($id)
    {
        $comment = new Comment($id, $this->conn, $this->api_endpoint);
        return $comment;
    }

    /**
     * Messages wrapper
     * @return Message
     */
    function message()
    {
        $msg = new Message($this->conn, $this->api_endpoint);
        return $msg;
    }

    /**
     * Notifications wrapper
     * @return mixed
     */
    function notification()
    {
        $notification = new Notification($this->conn, $this->api_endpoint);
        return $notification;
    }

}
