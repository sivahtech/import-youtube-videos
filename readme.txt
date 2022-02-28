=== Import Youtube Videos ===
Contributors: Sivahtech
Donate link: https://sivahtech.com/donate/
Tags: YouTube, import, video, channel, episodes, embed, videos, sync
Requires at least: 4.8
Tested up to: 5.9
Requires PHP: 7.1
Stable tag: 1.0.1
License: GPLv3 or later


A simple YouTube video importer plugin. Import YouTube videos automatically to your WordPress site.

== Description ==
This Plugin Import video from youtube channel to custom post videos created by this plugin. With this plugin you can YouTube channels with your WordPress website. This helps to easily import YouTube videos into WordPress as Custom posts(videos). 
The plugin can import all your YouTube videos or you can select the video by date or you can select video by using preview button.If you can import without preview then it will import all video from the selected channel. Before you start use this plugin please add google youtube api key in setting otherwise it will not work as shown in scrrenshot 1.
This plugin create a option under tools in admin name import youtube here you can select your api key and channel.


== Installation ==

1. Install directly from your WordPress dashboard or upload the plugin files to the `/wp-content/plugins/Import Youtube` directory.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Run a new import via the "Tools -> Import Youtube" section in your WordPress admin panel.
4. Add your youtube api key in setting tab.
5. add your channel details here and click on preview to select video from a list of videos

== Frequently Asked Questions ==

= The import failed or takes too much time to process? =
You can run the improter multiple times, as it will never import the same post twice. Once all videos are imported, only future posts would be added, assuming you selected the continuous import option.

= What is an "API Key"? =
To import your videos from YouTube, you'll have to create a key and save it in the plugin's settings.
An "API Key" is like a password that allows you to search, process and import videos from YouTube.
It is required by YouTube/Google that you have a valid key before you can import any data from their platform.

= Do I need to add a YouTube API key? =
Yes, a valid YouTube API key is required, you can create a key on the Google Cloud Platform.

= Is there a limit on the number of imported videos =
Yes. Google limits your API key with a daily quota.
If you're trying to import a large channel with thousands of videos, you can ask Google for an increase -
[https://support.google.com/youtube/contact/yt_api_form](https://support.google.com/youtube/contact/yt_api_form)

= The import does not work for my YouTube feed =
First, make sure your server is up to date with the requirements - we recommend PHP 7.1 or above.
Second, feel free to contact us if you encounter any issues.

== Screenshots ==

1. Show setting option where you can insert your api key.
2. Show setting for your youtube channel.
3.show list of video that you view after click on preview you can select video that you want to import from here .

== Changelog ==

= 1.0.1 =


= 1.0.0 =
* Initial Release.
