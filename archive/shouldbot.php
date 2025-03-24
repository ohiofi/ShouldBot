<?php

// ShouldBot
//
// A PHP-based Twitterbot that uses the TwitterOAuth library
// When a user asks it a "this or that" question, it splits the text and randomly replies with either "this" or "that"
//
// Previously this bot contained behaviors that violated the Twitter ToS, these behaviors have been removed.
//  • This bot will no longer auto-favorite tweets
//  • This bot will no longer auto-follow users
//  • This bot will no longer auto-retweet
//  • This bot will no longer auto-reply based on keyword searches

$consumer_key = "xxxxxSECRETxxxxx";
$consumer_secret = "xxxxxSECRETxxxxx";
$access_key = "xxxxxSECRETxxxxx";
$access_secret = "xxxxxSECRETxxxxx";

require_once('twitteroauth.php');// Use the twitteroauth library
$twitter = new TwitterOAuth ($consumer_key ,$consumer_secret , $access_key , $access_secret );// Connect to Twitter using TwitterOAuth library

$randomReply = array();// Create a new arry called randomReply
$newTweetCount = 0;// Set the tweets count to zero
$lastTweetScreenName = NULL;

$myUserInfo = $twitter->get('account/verify_credentials');// Get ShouldBot's info
$myLastTweet = $twitter->get('statuses/user_timeline', array('user_id' => $myUserInfo->id_str, 'count' => 1));// Use ShouldBot's info to get ShouldBot's last tweet

// Check for @ mentions since ShouldBot's last tweet
$search = $twitter->get('statuses/mentions_timeline', array('count' => 10, 'since_id' => $myLastTweet[0]->id_str));// Get new @mentions since the user's last tweet

if(count($search) > 0){// If there are more than zero @ mentions since ShouldBot's last tweet

	foreach($search as $tweet) {// Loop the following for each @ mention

    $lower_tweet_str = strtolower((string)$tweet->text);// make it lowercase, because strpos() is faster than stripos()

		if( (($stringPosition = strpos($lower_tweet_str, " or ")) !== FALSE )){// Continue only if the tweet contains "or".

			if( (($stringPosition = strpos($lower_tweet_str, "kill")) == FALSE)
       AND (($stringPosition = strpos($lower_tweet_str, "suicide")) == FALSE)
        AND (($stringPosition = strpos($lower_tweet_str, "die")) == FALSE)){// Continue only if the tweet does NOT contain these blacklisted words

        echo "<br/>";echo $lower_tweet_str;echo "<br/>";// If bot is run manually, user will see the original @ mention tweet.

        if( ($stringPosition = strpos($lower_tweet_str, ", or ")) !== FALSE )// Remove Oxford commas. Convert ", or " to " or "
					$lower_tweet_str = str_replace(", or "," or ",$lower_tweet_str);

				if( ($stringPosition = strpos($lower_tweet_str, ", ")) !== FALSE )// Convert ", " to " or "
					$lower_tweet_str = str_replace(", "," or ",$lower_tweet_str);

				if( ($stringPosition = strpos($lower_tweet_str, "@shouldbot")) !== FALSE )// Convert "@shouldbot" to ""
					$lower_tweet_str = str_replace("@shouldbot","",$lower_tweet_str);

        if( ($stringPosition = strpos($lower_tweet_str, "?")) !== FALSE )// Convert "?" to ""
  				$lower_tweet_str = str_replace("?","",$lower_tweet_str);

        if( ($stringPosition = strpos($lower_tweet_str, ".")) !== FALSE )// Convert "." to ""
    			$lower_tweet_str = str_replace(".","",$lower_tweet_str);

        if( ($stringPosition = strpos($lower_tweet_str, "!")) !== FALSE )// Convert "!" to ""
      		$lower_tweet_str = str_replace("!","",$lower_tweet_str);

				$randomReply = explode(" or ",$lower_tweet_str);// Convert to array, splitting at " or "

				foreach($randomReply as &$randomReplyOption){

					if( ($stringPosition = strpos($randomReplyOption, " my")) !== FALSE ){// Replace " my" with " your"
						$randomReplyOption = str_replace(" my"," your",$randomReplyOption);
					}elseif( ($stringPosition = strpos($randomReplyOption, "your")) !== FALSE )// If there is no " my", replace "your" with "my"
						$randomReplyOption = str_replace("your","my",$randomReplyOption);

					if( ($stringPosition = strpos($randomReplyOption, " me ")) !== FALSE ){// Replace " me " with " you "
						$randomReplyOption = str_replace(" me "," you ",$randomReplyOption);
					}elseif( ($stringPosition = strpos($randomReplyOption, " you ")) !== FALSE )// If there is no " me ", replace " you " with " me "
						$randomReplyOption = str_replace(" you "," me ",$randomReplyOption);

					if( ($stringPosition = strpos($randomReplyOption, " u ")) !== FALSE )// Replace " u " with " me "
						$randomReplyOption = str_replace(" u "," me ",$randomReplyOption);

          if( ($stringPosition = strpos($randomReplyOption, "should i ")) !== FALSE )// Replace "should i " with "you should ".
            $randomReplyOption = str_replace("should i ","you should ",$randomReplyOption);

          if( ($stringPosition = strpos($randomReplyOption, "will i")) !== FALSE )// Replace "will i" with "you will"
            $randomReplyOption = str_replace("will i","you will",$randomReplyOption);

					if( ($stringPosition = strpos($randomReplyOption, " i ")) !== FALSE )// Replace " i " with " you "
						$randomReplyOption = str_replace(" i "," you ",$randomReplyOption);

					if( ($stringPosition = strpos($randomReplyOption, "is it")) !== FALSE )// Replace "is it" with "it is"
						$randomReplyOption = str_replace("is it","it is",$randomReplyOption);

					if( ($stringPosition = strpos($randomReplyOption, "is that")) !== FALSE )// Replace "is that" with "that is"
						$randomReplyOption = str_replace("is that","that is",$randomReplyOption);

					if( ($stringPosition = strpos($randomReplyOption, "should we ")) !== FALSE )// Replace "should we " with "you should "
						$randomReplyOption = str_replace("should we ","you should ",$randomReplyOption);

					if( ($stringPosition = strpos($randomReplyOption, "would that")) !== FALSE )// Replace "would that" with "that would"
						$randomReplyOption = str_replace("would that","that would",$randomReplyOption);

					if( ($stringPosition = strpos($randomReplyOption, " here ")) !== FALSE )// Replace " here " with " there "
						$randomReplyOption = str_replace(" here "," there ",$randomReplyOption);

          if( ($stringPosition = strpos($randomReplyOption, " this ")) !== FALSE )// Replace " this " with " that "
            $randomReplyOption = str_replace(" this "," that ",$randomReplyOption);

					echo "<br/>";echo "• ".$randomReplyOption;echo "<br/>";// If bot is run manually, user will see each randomReplyOption listed

				}// Loop back to replace stuff for each randomReplyOption

		  	shuffle($randomReply);// Shuffle the random reply for each search result

        //The replyTweet is composed of: the @ mention-er's username, a randomReply, and the URL of the @ mention tweet
				$replyTweet = $randomReply[0];// store the first randomReply in the variable replyTweet

				if(strlen($replyTweet) < 1){// if replyTweet is shorter than 1 character, try the next randomReply
					$replyTweet = $randomReply[1];
					if(strlen($replyTweet) < 1)// if STILL shorter than 1 character, then reply with shruggie "¯\_(ツ)_/¯"
						$replyTweet = "¯\_(ツ)_/¯";
				}

				if(strlen($replyTweet) > 116)// If tweet is already too long, shorten it. Is 116 characters still too many???
					$replyTweet = substr($replyTweet, 0, 115);

				$replyTweet = ".@".$tweet->user->screen_name." ".$replyTweet;// Append the @ mention-er's username

        $replyTweet = $replyTweet." https://twitter.com/".$tweet->user->screen_name."/status/".$tweet->id_str;// Append the URL of the @ mention tweet

				$twitter->post('statuses/update', array('status' => $replyTweet,'in_reply_to_status_id' => $tweet->id_str));// Post tweet

				$newTweetCount++;// Add one to output counter

				echo "<br/>";echo $newTweetCount." ".$replyTweet."\n";echo "<br/>";// If bot is run manually, user will see the final tweet.
			}
    } else {// If the original @ mention does NOT contain "or"
      // If the @ mention contains "thank" then reply with "you're welcome"
      // elseif the @ mention contains "should I" & doesn't contain "or" then reply "Ask me like this, '@ShouldBot Should I this OR that'"
      // Else reply "¯\_(ツ)_/¯"
    }
	}
}
echo "<br/>";echo "Success! Check ShouldBot for ".$newTweetCount." new tweets.";// If bot is run manually, user will see this text.
?>
