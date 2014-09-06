<?php
/**
 * PHP Imgur wrapper 0.1
 * Imgur API wrapper for easy use.
 * @author Vadim Kr.
 * @copyright (c) 2013 bndr
 * @license http://creativecommons.org/licenses/by-sa/3.0/legalcode
 */

//Include the main file
include("Imgur.php");
//Set up your api key and secret
$api_key = "";
$api_secret = "";

$imgur = new Imgur($api_key, $api_secret);


/*-----------------------------------------------------------------------------------*/
/* Authorization
/*-----------------------------------------------------------------------------------*/
//We create a new instance of imgur class.
// Imgur oAuth will return code as GET parameter after the user allows access
if (isset($_GET['code'])) {
    $tokens = $imgur->authorize(false, $_GET['code']); //First parameter is for refresh_tokens
    //The user is authorized. You can save the $tokens['refresh_token'] and $tokens['access_token'] for future use;
} else {
    // GET parameter doesn't exist, so we will have to ask user to allow access for our application
    $imgur->authorize();
}
/*-----------------------------------------------------------------------------------*/
/* Account
/*-----------------------------------------------------------------------------------*/

$imgur->account("username")->basic();
$imgur->account("username")->albums($page = 0);
$imgur->account("username")->images($page = 0);
$imgur->account("new_username")->create($options); //$options will be used as post fields. For more info go to Imgur API Docs.
//....

/*-----------------------------------------------------------------------------------*/
/* Images
/*-----------------------------------------------------------------------------------*/

$imgur->image("image_id")->get();
$imgur->image("image_id")->delete();
$imgur->image('image_id')->update($options);
$imgur->image("image_id")->favorite();
//....

/*-----------------------------------------------------------------------------------*/
/* Comments
/*-----------------------------------------------------------------------------------*/

$imgur->comment("comment_id")->get();
$imgur->comment("comment_id")->delete();
$imgur->comment("comment_id")->report();
$imgur->comment("comment_id")->vote($type); // "up" or "down";
$imgur->comment("comment_id")->replies();
$imgur->comment("comment_id")->reply_create($options);
//....

/*-----------------------------------------------------------------------------------*/
/* Messages (User must be logged in);
/*-----------------------------------------------------------------------------------*/

$imgur->message()->messages();
$imgur->message()->single($id);
$imgur->message()->create($options);
$imgur->message()->delete($id);
$imgur->message()->message_count();
$imgur->message()->get_thread($id);
//...

/*-----------------------------------------------------------------------------------*/
/* Gallery
/*-----------------------------------------------------------------------------------*/

$imgur->gallery()->get($section, $sort, $page); //More on the parameters at Imgur API docs
$imgur->gallery()->comments($id, $type); //$type = "image" | "album";
$imgur->gallery()->search($str);
$imgur->gallery()->remove($id);
$imgur->gallery()->submit($id, $options, $type);
$imgur->gallery()->votes($id, $type);
$imgur->gallery()->vote($id, $type, $vote);
//...

/*-----------------------------------------------------------------------------------*/
/* Uploading
/*-----------------------------------------------------------------------------------*/

$imgur->upload()->file("/path/to/file", $postfields); //Postfields look in http://api.imgur.com/endpoints/image#image-upload
$imgur->upload()->string("base64encodedstring,$postfields");
$imgur->upload()->url("http://urlofimage.com", $postfields);


/*-----------------------------------------------------------------------------------*/
/* Notifications
/*-----------------------------------------------------------------------------------*/

$imgur->notification()->all();
$imgur->notification()->single($id);
$imgur->notification()->mark_as_read($id);
