const express = require('express');
const app = express();
require('dotenv').config();
const Twit = require('twit');
// const T = new Twit({
//   consumer_key:         process.env.CONSUMER_KEY,
//   consumer_secret:      process.env.CONSUMER_SECRET,
//   access_token:         process.env.ACCESS_KEY,
//   access_token_secret:  process.env.ACCESS_SECRET,
//   timeout_ms:           60*1000,  // optional HTTP request timeout to apply to all requests.
//   strictSSL:            true,     // optional - requires SSL certificates to be valid.
// });

// regex (s|S)hould (i|I) .* or .*\?

function shuffle(a) {
  for (let i = a.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [a[i], a[j]] = [a[j], a[i]];
  }
}

function getMyLastTweet(){
  let shouldbot_id = 3314653662;
  let options = {
    user_id:shouldbot_id,
    count: 1
  };
  T.get('statuses/user_timeline', options,(err,data,response)=>{
    console.log(data.length);
    checkForMentions(data);
  });
}

function checkForMentions(myLastTweet){ // Check for @ mentions since ShouldBot's last tweet
  let options = {
    since_id:myLastTweet.id_str,
    count: 10
  };
  T.get('statuses/mentions_timeline', options,(err,data,response)=>{
    console.log(data.length);
    if(data.length>0){
      for(let i=0;i<data.length;i++){
        buildReply(data[i]);
      }
    }
  });
}

function passedBanlist(str){
  str = str.toLowerCase();
  //let banlist = process.env.BANLIST.split(",");
  let banlist = ['lkjsdf','adsadsfdfsa']
  for (let i = 0; i < banlist.length; ++i) {
    if (str.indexOf(banlist[i]) > -1) {
      return false; // String is present
    }
  }
  return true; // No banlisted strings are present
}

function fixPunctuation(str){
  while (str.indexOf(", or ") > -1) { // Remove Oxford commas. Convert ", or " to " or "
    str = str.replace(", or "," or ");
  }
  while (str.indexOf(", ") > -1) { // Convert ", " to " or "
    str = str.replace(", "," or ");
  }
  while (str.indexOf("@shouldbot") > -1) { // Convert "@shouldbot" to ""
    str = str.replace("@shouldbot","");
  }
  while (str.indexOf("?") > -1) { // Convert "?" to ""
    str = str.replace("?","");
  }
  while (str.indexOf(".") > -1) { // Convert "." to ""
    str = str.replace(".","");
  }
  while (str.indexOf("!") > -1) { // Convert "!" to ""
    str = str.replace("!","");
  }
  return str
}

function fixPronouns(str){
  if (str.indexOf(" my") > -1) { // Replace " my" with " your"
    str = str.replace(" my"," your");
  }else if (str.indexOf("your") > -1) { // If there is no " my", replace "your" with "my"
    str = str.replace("your","my");
  }
  if (str.indexOf(" me ") > -1) { // Replace " me " with " you "
    str = str.replace(" me "," you ");
  }else if (str.indexOf(" you ") > -1) { // If there is no " me ", replace " you " with " me "
    str = str.replace(" you "," me ");
  }
  if (str.indexOf(" u ") > -1) { // Replace " u " with " me "
    str = str.replace(" u "," me ");
  }
  if (str.indexOf("should i ") > -1) { // Replace "should i " with "you should "
    str = str.replace("should i ","you should ");
  }
  if (str.indexOf("i should ") > -1) { // Replace "i should " with "you should "
    str = str.replace("i should ","you should ");
  }
  if (str.indexOf("should i ") > -1) { // Replace "should i " with "I should "
    str = str.replace("should i ","I should ");
  }
  if (str.indexOf("will i") > -1) { // Replace "will i" with "you will"
    str = str.replace("will i","you will");
  }
  if (str.indexOf(" i ") > -1) { // Replace " i " with " you "
    str = str.replace(" i "," you ");
  }
  if (str.indexOf("is it") > -1) { // Replace "is it" with "it is"
    str = str.replace("is it","it is");
  }
  if (str.indexOf("is that") > -1) { // Replace "is that" with "that is"
    str = str.replace("is that","that is");
  }
  if (str.indexOf("should we ") > -1) { // Replace "should we " with "you should "
    str = str.replace("should we ","you should ");
  }
  if (str.indexOf("would that") > -1) { // Replace "would that" with "that would"
    str = str.replace("would that","that would");
  }
  if (str.indexOf(" here ") > -1) { // Replace " here " with " there "
    str = str.replace(" here "," there ");
  }
  if (str.indexOf(" this ") > -1) { // Replace " this " with " that "
    str = str.replace(" this "," that ");
  }
  return str
}

function twitterCallback(err, data, response) {
  if(err){
    console.log("Oof. Error. "+err)
  } else {
    console.log("It worked.")
  }
}

function tweetReply(originalTweetObj,replyStr){
  console.log("tweetReply")
  let tweetObj = {
    status: replyStr,
    in_reply_to_status_id: originalTweetObj.id
  };
  T.post('statuses/update', tweetObj, twitterCallback)
}

function buildReply(atMentionObj){
  if(!passedBanlist(atMentionObj.text)){
    console.log("- Failed Banlist - "+atMentionObj.text);
    return // no reply tweet
  }
  let lower_atMentionStr = atMentionObj.text.toLowerCase();
  if(lower_atMentionStr.indexOf(" or ") == -1){
    console.log("- Failed, Missing Keyword - "+atMentionObj.text);
    return // no reply tweet
  }
  lower_atMentionStr = fixPunctuation(lower_atMentionStr);
  let myArray = lower_atMentionStr.split(" or ");// Convert to array, splitting at " or "
  for(let i=0;i<myArray.length;i++){
    myArray[i] = myArray[i].trim();
    myArray[i] = fixPronouns(myArray[i]);
  }
  shuffle(myArray)
  let randomReply = "¯\_(ツ)_/¯";
  for(let i=0;i<myArray.length;i++){
    if(myArray[i].length > 0){
      randomReply = myArray[i];
      break
    }
  }
  if(randomReply.length > 260){ // If tweet is somehow too long, shorten it.
    randomReply = randomReply.slice(0,260);
  }
  randomReply = ".@"+atMentionObj.user.screen_name+" "+randomReply; // Prepend the @ mention-er's username
  randomReply+=" https://twitter.com/"+atMentionObj.user.screen_name+"/status/"+atMentionObj.id_str; // Append the URL of the @ mention tweet
  console.log(randomReply);
  //tweetReply(atMentionObj,randomReply);
}


app.listen(
  process.env.PORT || 3000,
  ()=>console.log("bot running\n"+
  buildReply({
    text:"Should I apple, bananaa, coconut, dragonfruit, foo, bar, baz or     ?",
    id_str:122333444455555,
    user:{
      screen_name:"foobarbazz"
    }
  }))
);
