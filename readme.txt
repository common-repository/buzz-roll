=== Plugin Name ===
Contributors: LinksAlpha
Tags: google buzz, buzz-roll, buzzroll, buzz, social, news, trackbacks, trackback, buddypress
Requires at least: 2.0.2
Tested up to: 2.9.2
Stable tag: 1.0.2

== Description ==

Get Support: support@linksalpha.com

Google Buzz enable your blog. Make it easy for your visitors to share your blog content on fastest growing social hub - Google Buzz.

BuzzRoll Plugin shows Google Buzz Button with Comment Count on each blog post. BuzzRoll also shows Google Buzz Comments on each blog post.

Choose from `6 Google Buzz buttons`. Want more options? Email to support@linksalpha.com

Features:

1. Show Google Buzz Button - `Next to Post Time` or `Comment Count` or `before Post Content` or `after Post Content`
1. Show Google Buzz Comments - `after post content` or `after comment form`
1. `6 Google Buzz buttons` to choose from
1. Popup window for quick sharing on Google Buzz
1. Option to manually position Google Buzz Button and Google Buzz Comments. Check installation instructions.

How is this different from Buzz button by Google:

1. BuzzRoll offers 6 button styles, while Google offers only 1
1. BuzzRoll `also shows comments` from Google Buzz on your blog, while Google's solution does not

**Check out our other plugins that you will find extremely useful:**

* Social Discussions: show popularity of your blog posts on Social Networks including Twitter, Facebook, Google Buzz, Yahoo, and bit.ly. http://wordpress.org/extend/plugins/social-discussions/
* Social Stats: track your blog activity on social networks - monthly/weekly/daily, and track your popular posts. http://wordpress.org/extend/plugins/social-stats/screenshots/
* Network Publisher: auto publish your blog posts to Twitter, Facebook, LinkedIn, Yahoo, Yammer, Identi.ca, and MySpace. http://wordpress.org/extend/plugins/network-publisher/
* Retweeters: shows Twitter users who recently tweeted links from your blog. http://wordpress.org/extend/plugins/retweeters/


== Installation ==

1. Upload buzz-roll.zip to '/wp-content/plugins/' directory and unzip it.
1. Activate the Plugin from "Manage Plugins" window
1. Activate Features: From the Wordpress Plugins side-menu bar, click on the "Buzz-Roll" Plugin link. Once there, choose the required options and click on "Update Options" button.

Manual positioning of features on template:

1. Google Buzz Button: <code><?php buzzroll_load_comment_count(); ?></code>  (example: You can add this to index.php template)
1. Google Buzz Comments for each blog post: <code><?php buzzroll_load_link_comments(); ?></code>  (example: You can add this to single.php template)

== Screenshots ==

1. Wordpress Plugin Configuration widow
2. Google Buzz Comments
3. Google Buzz Button


== Changelog ==

= 1.0.2 =
* Changed post url to Buzz url

= 1.0.1 =
* First release