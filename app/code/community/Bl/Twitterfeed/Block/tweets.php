<?php

$TwitterOAuthLibPath = Mage::getModuleDir('', 'Bl_Twitterfeed') . DS . 'lib' . DS .'twitteroauth/twitteroauth/twitteroauth.php';
require_once($TwitterOAuthLibPath);

class Bl_Twitterfeed_Block_Tweets extends Mage_Core_Block_Template
{

    // Builds a list of tweets for the specified account per configurations in sys/config
    public function getTweets()
    {

        $twitter_user_id = Mage::getStoreConfig('twitterfeed/general/username');
        $tweets_to_display = Mage::getStoreConfig('twitterfeed/general/tweet_count');
        $ignore_replies = false;
        $include_rts = false;
        $date_format = 'g:i A M jS';
        $twitter_style_dates = true;

        $consumerkey = Mage::getStoreConfig('twitterfeed/api/consumer_key');
        $consumersecret = Mage::getStoreConfig('twitterfeed/api/consumer_secret');
        $accesstoken = Mage::getStoreConfig('twitterfeed/api/access_token');
        $accesstokensecret = Mage::getStoreConfig('twitterfeed/api/access_token_secret');

        $tweet_found = false;

        date_default_timezone_set('Europe/London');

        // Cache file not found, or old. Authenticae app.
        $twitterOAuthConnection = new TwitterOAuth($consumerkey, $consumersecret, $accesstoken, $accesstokensecret);

        if($twitterOAuthConnection){
            // Get the latest tweets from Twitter
            $get_tweets = $twitterOAuthConnection->get("https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name=".$twitter_user_id."&count=".$tweets_to_display."&include_rts=".$include_rts."&exclude_replies=".$ignore_replies);

            // Error check: Make sure there is at least one item.
            if (count($get_tweets)) {

                // Start tweet_count as zero
                $tweet_count = 0;

                // Open the twitterfeed__tweets element
                $twitter_html = '<ul class="twitterfeed__tweets">';

                // Iterate over tweets.
                foreach($get_tweets as $tweet) {
                    $tweet_found = true;
                    $tweet_count++;
                    $tweet_desc = $tweet->text;

                    // Turns hash tags and account links to actual links
                    $tweet_desc = preg_replace("/((http)+(s)?:\/\/[^<>\s]+)/i", "<a href=\"\\0\" target=\"_blank\">\\0</a>", $tweet_desc );
                    $tweet_desc = preg_replace("/[@]+([A-Za-z0-9-_]+)/", "<a href=\"http://twitter.com/\\1\" target=\"_blank\">\\0</a>", $tweet_desc );
                    $tweet_desc = preg_replace("/[#]+([A-Za-z0-9-_]+)/", "<a href=\"http://twitter.com/search?q=%23\\1\" target=\"_blank\">\\0</a>", $tweet_desc );

                    // replace t.co links with expanded link, if present
                    $entities = $tweet->entities;
                    if(!empty($entities->urls[0]->expanded_url)) {
                        $tweet_desc = str_replace($entities->urls[0]->url, $entities->urls[0]->expanded_url, $tweet_desc);
                    }

                    $media_url = $entities->media[0]->media_url;
                    if($media_url) {
                        $img = "<img src='".$media_url."'>";
                        $twitter_html .= $img;
                    }

                    $tweet_time = strtotime($tweet->created_at);
                    $display_time = date($date_format,$tweet_time);

                    // Render the tweet text into a list item
                    $twitter_html .= '<li class="twitterfeed__tweets__tweet">' . html_entity_decode($tweet_desc) . '<a href="http://twitter.com/'.$twitter_user_id.'"><time>' . $display_time . '</time></a></li>';

                    // Break the loop once we reach the defined tweet limit
                    if ($tweet_count >= $tweets_to_display){
                        break;
                    }
                }

                // Ends twitterfeed__tweets element
                $twitter_html .= '</ul>';

                // returns the completed element
                return $twitter_html;
            }
        }
    }

    // Gets the account user name from sys/config, returns string
    public function getTwitterUser()
    {
        $twitter_user_id = Mage::getStoreConfig('twitterfeed/general/username');
        return $twitter_user_id;
    }

    // Gets active true/false from sys/config, returns 0/1
    public function getIsTwitterfeedActive()
    {
        $twitter_active = Mage::getStoreConfig('twitterfeed/general/activate');
        return $twitter_active;
    }

    // Builds a follow link for the Twitter account specified in sys/config, returns null or a complete html link
    public function getFollowLink()
    {
        $follow_link = '';
        $twitter_user_id = Mage::getStoreConfig('twitterfeed/general/username');

        if(Mage::getStoreConfig('twitterfeed/general/follow_link') == 1) {
            $follow_link = '<a class="twitter-follow-button" data-size="large" data-show-count="false" href="https://twitter.com/' . $twitter_user_id . '" title="Follow ' . $twitter_user_id . ' on Twitter">Follow @' . $twitter_user_id . '</a>';
        }
        return $follow_link;
    }
}
