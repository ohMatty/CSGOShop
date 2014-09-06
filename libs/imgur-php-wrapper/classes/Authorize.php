<?php
/**
 * PHP Imgur wrapper 0.1
 * Imgur API wrapper for easy use.
 * @author Vadim Kr.
 * @copyright (c) 2013 bndr
 * @license http://creativecommons.org/licenses/by-sa/3.0/legalcode
 */

namespace Imgur;
class Authorize
{

    /**
     * @var string
     */
    /**
     * @var string
     */
    protected $api_key, $api_secret;
    /**
     * @var Connect
     */
    protected $conn;
    /**
     * @var string
     */
    protected $access_token;
    /**
     * @var string
     */
    protected $refresh_token;
    /**
     * @var string
     */
    protected $oauth = "https://api.imgur.com/oauth2";

    /**
     * Constructor
     * @param Connect $conn
     * @param string $key
     * @param string $secret
     */
    function __construct($conn, $key, $secret)
    {
        $this->api_key = $key;
        $this->api_secret = $secret;
        $this->conn = $conn;
    }

    /**
     * Set Access data for future uses.
     * @param $accessToken
     * @param $refreshToken
     */
    function setAccessData($accessToken, $refreshToken)
    {
        $this->access_token = $accessToken;
        $this->refresh_token = $refreshToken;
    }

    /**
     * Exchange authorization code for an access token
     * @param string $code
     * @return Array $response
     */
    function getAccessToken($code)
    {
        $uri = $this->oauth . "/token/";
        $options = array(
            'client_id' => $this->api_key,
            'client_secret' => $this->api_secret,
            'grant_type' => 'authorization_code',
            'code' => $code
        );
        $response = ($code) ? $this->conn->request($uri, $options, "POST") : null;

        return $response;
    }

    /**
     * Exchange the refresh token for access token
     * @param string $refresh_token
     * @return Array $response
     */
    function refreshAccessToken($refresh_token)
    {

        $uri = $this->oauth . "/token/";
        $options = array(
            'client_id' => $this->api_key,
            'client_secret' => $this->api_secret,
            'grant_type' => 'refresh_token',
            'refresh_token' => $refresh_token
        );

        $response = ($refresh_token) ? $this->conn->request($uri, $options, "POST") : null;

        return $response;
    }

    /**
     * Show the authorization page to the user
     */
    function getAuthorizationCode()
    {
        $uri = $this->oauth . "/authorize/" . "?client_id=" . $this->api_key . "&response_type=code&state=initializing";

        echo "<!doctype html><html><head><meta charset='utf-8'></head>
            <body><a href='{$uri}' target='_blank'>Click this link to authorize the application to access your Imgur data</a><br>
               </body></html>";

        exit;

    }

}

