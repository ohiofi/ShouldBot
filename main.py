from mastodon import Mastodon
import time
from dotenv import load_dotenv
import os
import random
import json

load_dotenv()
banlist = json.loads(os.getenv("banlist"))

mastodon = Mastodon(
    client_id=os.getenv("client_key"),
    client_secret=os.getenv("client_secret"),
    access_token=os.getenv("access_token"),
    api_base_url="https://mastodon.social"
)

def build_reply(status_object, banlist):
    if not passed_banlist(status_object.content, banlist):
        print("- Failed Banlist - " + status_object.content)
        return None # no reply tweet
    
    lower_at_mention_str = status_object.content.lower()
    if lower_at_mention_str.find(" or ") == -1:
        print("- Failed, Missing Keyword - " + status_object.content)
        return None # no reply tweet
    
    lower_at_mention_str = fix_punctuation(lower_at_mention_str)
    my_array = lower_at_mention_str.split(" or ")  # Convert to array, splitting at " or "
    
    for i in range(len(my_array)):
        my_array[i] = fix_pronouns(my_array[i].strip())
    
    random.shuffle(my_array)
    random_reply = "¯\\_(ツ)_/¯"
    
    for item in my_array:
        if len(item) > 0:
            random_reply = item
            break
    
    if len(random_reply) > 450:  # On standard Mastodon instances, the default character limit for posts is 500 characters
        random_reply = random_reply[:450]
    
    #random_reply = ".@" + status_object.user.screen_name + " " + random_reply  # Prepend the @ mention-er's username
    random_reply += " " + status_object.url  # Append the URL of the @ mention tweet
    
    
    # tweet_reply(at_mention_obj, random_reply)
    return random_reply

def fix_pronouns(text):
    if text.find(" my") > -1:  # Replace " my" with " your"
        text = text.replace(" my", " your")
    elif text.find("your") > -1:  # If there is no " my", replace "your" with "my"
        text = text.replace("your", "my")
    
    if text.find(" me ") > -1:  # Replace " me " with " you "
        text = text.replace(" me ", " you ")
    elif text.find(" you ") > -1:  # If there is no " me ", replace " you " with " me "
        text = text.replace(" you ", " me ")
    
    if text.find(" u ") > -1:  # Replace " u " with " me "
        text = text.replace(" u ", " me ")
    
    if text.find("should i ") > -1:  # Replace "should i " with "you should "
        text = text.replace("should i ", "you should ")
    
    if text.find("i should ") > -1:  # Replace "i should " with "you should "
        text = text.replace("i should ", "you should ")
    
    if text.find("should i ") > -1:  # Replace "should i " with "I should "
        text = text.replace("should i ", "I should ")
    
    if text.find("will i") > -1:  # Replace "will i" with "you will"
        text = text.replace("will i", "you will")
    
    if text.find(" i ") > -1:  # Replace " i " with " you "
        text = text.replace(" i ", " you ")
    
    if text.find("is it") > -1:  # Replace "is it" with "it is"
        text = text.replace("is it", "it is")
    
    if text.find("is that") > -1:  # Replace "is that" with "that is"
        text = text.replace("is that", "that is")
    
    if text.find("should we ") > -1:  # Replace "should we " with "you should "
        text = text.replace("should we ", "you should ")
    
    if text.find("would that") > -1:  # Replace "would that" with "that would"
        text = text.replace("would that", "that would")
    
    if text.find(" here ") > -1:  # Replace " here " with " there "
        text = text.replace(" here ", " there ")
    
    if text.find(" this ") > -1:  # Replace " this " with " that "
        text = text.replace(" this ", " that ")
    
    return text

def fix_punctuation(text):
    while text.find(", or ") > -1:  # Remove Oxford commas. Convert ", or " to " or "
        text = text.replace(", or ", " or ")
    
    while text.find(", ") > -1:  # Convert ", " to " or "
        text = text.replace(", ", " or ")
    
    while text.find("@shouldbot") > -1:  # Convert "@shouldbot" to ""
        text = text.replace("@shouldbot", "")
    
    while text.find("?") > -1:  # Convert "?" to ""
        text = text.replace("?", "")
    
    while text.find(".") > -1:  # Convert "." to ""
        text = text.replace(".", "")
    
    while text.find("!") > -1:  # Convert "!" to ""
        text = text.replace("!", "")
    
    return text


def getFollowingPosts(mastodon):
    following_posts = set()
    # Get the accounts the bot is following
    following_accounts = mastodon.account_following(my_account.id)
    # Get posts from followed accounts
    for following_account in following_accounts:
        for post in mastodon.account_statuses(following_account):
            following_posts.add(post.id)
    return following_posts

def getAtMentions(mastodon):
    # Get mentions and extract post IDs
    at_mentions = set()
    notifications = mastodon.notifications()
    for notification in notifications:
        if notification["type"] == "mention":
            at_mentions.add(notification["status"]["id"])
    return at_mentions

def passed_banlist(str, banlist):
    for each in banlist:
        if each in str.lower():
            return False
    return True


# Read old posts from file
def read_old_posts(filename="old_posts.txt"):
    if not os.path.exists(filename):
        return set()
    with open(filename, "r") as file:
        return set(line.strip() for line in file.readlines())
    
# Write new posts to file
def write_new_posts(new_posts, filename="old_posts.txt"):
    with open(filename, "a") as file:
        for post in new_posts:
            file.write(f"{post}\n")




# Get bot account
my_account = mastodon.account_verify_credentials()

# Read old post IDs
old_posts = read_old_posts()

at_mentions_and_following_posts = getAtMentions(mastodon).union(getFollowingPosts(mastodon))

new_replied_posts = set()

# Reply to new posts and mentions
for post_id in at_mentions_and_following_posts:
    if str(post_id) not in old_posts:  # Convert post_id to str for consistency
        if random.random() < 0.01: # 1% chance of skipping in case of replybot + replybot loop
            new_replied_posts.add(str(post_id))
            continue
        
        post_object = mastodon.status(post_id)
        reply_message = build_reply(post_object, banlist)
        if reply_message:
            mastodon.status_post(reply_message, in_reply_to_id=post_id)
        new_replied_posts.add(str(post_id))

# Write newly replied posts to file
if new_replied_posts:
    write_new_posts(new_replied_posts)