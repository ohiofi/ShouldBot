from mastodon import Mastodon
import time
from dotenv import load_dotenv
import os
import random

load_dotenv()

mastodon = Mastodon(
    client_id=os.getenv("client_key"),
    client_secret=os.getenv("client_secret"),
    access_token=os.getenv("access_token"),
    api_base_url="https://mastodon.social"
)

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

# Get the accounts the bot is following
following_accounts = mastodon.account_following(my_account.id)

# Read old post IDs
old_posts = read_old_posts()

at_mentions_and_following_posts = set()

# Get mentions and extract post IDs
notifications = mastodon.notifications()
for notification in notifications:
    if notification["type"] == "mention":
        at_mentions_and_following_posts.add(notification["status"]["id"])

# Get posts from followed accounts
for following_account in following_accounts:
    for post in mastodon.account_statuses(following_account):
        at_mentions_and_following_posts.add(post.id)

new_replied_posts = set()

# Reply to new posts and mentions
for post_id in at_mentions_and_following_posts:
    if str(post_id) not in old_posts:  # Convert post_id to str for consistency
        mastodon.status_post("message", in_reply_to_id=post_id)
        new_replied_posts.add(str(post_id))

# Write newly replied posts to file
if new_replied_posts:
    write_new_posts(new_replied_posts)