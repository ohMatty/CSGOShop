<?php
/**
 * PHP Imgur wrapper 0.1
 * Imgur API wrapper for easy use.
 * @author Vadim Kr.
 * @copyright (c) 2013 bndr
 * @license http://creativecommons.org/licenses/by-sa/3.0/legalcode
 */

namespace Imgur;
class Connect
{

    /**
     * @var array
     */
    protected $options;
    /**
     * @var string
     */
    protected $api_key;
    /**
     * @var string
     */
    protected $api_secret;
    /**
     * @var string
     */
    protected $api_endpoint;
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
     * @param string $api_key
     * @param string $api_secret
     */
    function __construct($api_key, $api_secret)
    {
        $this->api_key = $api_key;
        $this->api_secret = $api_secret;
    }

    /**
     * Set Access Data. Used for authorization
     * @param $accessToken
     * @param $refreshToken
     */
    function setAccessData($accessToken, $refreshToken)
    {
        $this->access_token = $accessToken;
        $this->refresh_token = $refreshToken;
    }

    /**
     * Make request to Imgur API endpoint
     * @param $endpoint
     * @param mixed $options
     * @param string $type
     * @return mixed
     * @throws Exception
     */
    function request($endpoint, $options = FALSE, $type = "GET")
    {
        $headers = (!$this->access_token) ? array('Authorization: CLIENT-ID ' . $this->api_key) : array("Authorization: Bearer " . $this->access_token);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if ($options) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $options);
        }
        if (($data = curl_exec($ch)) === FALSE) {
            throw new Exception(curl_error($ch));
        }

        curl_close($ch);
        return json_decode($data, true);

    }

}


