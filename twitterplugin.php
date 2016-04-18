<?php
/*
Plugin Name:  Twitter Feed
Description: This plugin uses twitter api to get recent feeds on Twitter website and display on the wordpress page. 
Author: Yeeun You
Author URI: http://www.emilyyou.com
Version: 0.1
*/

//session will hold username throughtout the session period
session_start(); 
//connects with twitter authentication library folder
require_once("twitteroauth/twitteroauth/twitteroauth/twitteroauth.php");
	

//creates class for the plugin 
class TwitterPlugin{
	//authentication information
	public $twitteruser = 'twitter';
	public $consumerKey = "consumerKey";
	public $consumerSecret = "consumerSecret";
	public $accesstoken = "accessToken";
	public $accesstokenSecret = "accessTokenSecret";

	public function __construct(){
		//will be used to display feeds 
		add_shortcode("twitter", array($this, "getTwitFeed"));
		//this is for admin page
		add_action("admin_menu", array($this, "twitter_manage_options"));
	}

	//establishes connection 
	public function getConnectionWithAccessToken($ck, $cs, $ot, $ots){
		$connection = new TwitterOAuth($ck, $cs, $ot, $ots);
		return $connection;
	}

	public function twitter_manage_options() {
		//page title //menu title //capability //slug  //function
		add_menu_page("Twitter Feed Options", "Twitter Feed Options", "manage_options", "twitter-options", 	array($this, "show_twitter_options") );
	}

	public function getTwitFeed(){  
		//once connection is successful, calls get method and receives array data 
		$connection = $this->getConnectionWithAccessToken($this->consumerKey, $this->consumerSecret, $this->accesstoken, $this->accesstokenSecret);
		$tweets 	= $connection->get("https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name=". get_option('username' , 'Twitter') ."&count=5");
		$output     = '';
		$feed   	= array(); 
		if(is_array($tweets) || is_object($tweets))
		{ 
			if($tweets['errors'])
			{
				$output .= '<br/><br/><strong>Sorry the twitter username does not exist</strong>';
			}
			else
			{
				foreach ($tweets as $t) 
				{
					if(!empty($t['entities']['media'][0]))
					{
						$output .= '<a href="'. $t['entities']['media'][0]['url'].'"><section><img style="border-radius:50%;" src="'.$t['user']['profile_image_url'].'" alt="'.$t['user']['screen_name'].' pictures"> <strong> ' . $t['user']['screen_name']. ' - ' . $t['user']['location'] . '</strong><br/>';
						$output .= $t['text'] . " <br/><img style='width: 60%;' src='" . $t['entities']['media'][0]['media_url'] . "'/>";
						$output .= '<span class="dashicons dashicons-twitter"></span>' . $t['retweet_count'] . "</section></a><br/>";
					}
					else
					{
						$output .= '<a href="https://twitter.com/'. $t['user']['screen_name'].'"><section><img style="border-radius:50%;" src="'.$t['user']['profile_image_url'].'" alt="'.$t['user']['screen_name'].' pictures"> <strong> ' . $t['user']['screen_name']. ' - ' . $t['user']['location'] . '</strong><br/>';
						$output .= $t['text'] . " <br/>";
						$output .= '<span class="dashicons dashicons-twitter"></span>' . $t['retweet_count'] . "</section></a><br/>";
					}	
					//it decides whether to display photos
				}
			}
			return $output;
			//result is in array form
		}
	}

	//here username option can be changed
	//session is updated and grabs new feeds according to the provided username
	public function show_twitter_options(){   
		if ($_SERVER['REQUEST_METHOD'] == "POST")
		{
			if(!empty($_POST['username']))
			{
				update_option('username', $_POST['username']);
				echo 'username updated';
				$tweetoauth = new TwitterPlugin();
			}
			else{
				echo 'update failed';
			}
		} 
		$username = get_option('username', 'Twitter');
?>
		<form  method="POST" action="admin.php?page=twitter-options"> 
				<h2>Twitter Feed Options</h2> 
				Username: <input type="text" name="username" value="<?php echo $username ?>" />
				<input type="submit" value="submit"/>
		</form>
<?php
	}
}

$tweetoauth = new TwitterPlugin();
?>
