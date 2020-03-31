# lionwiki-blog
Fork of [LionWiki](http://lionwiki.0o.cz) 3.2.9 by Adam Zivner, capable working
as a very simple blog CMS.

## Introduction
I was in a need of very simple CMS for blogging, which would use no database
and be easy to use and maintain. At first I tinkered with
[Oddmuse](http://oddmuse.net), which is wiki+blog CMS written in Perl with
content entered in MarkDown. I liked the idea, but the need to do all
changes in Perl (ie. no templates, if you need anything rendered differently
to HTML, you have to redefine the appropriate Perl procedure) was a bit too
challenging for me.

But it inspired me, as a long time LionWiki user, to try to implement
similar functionality as a plugin.

## Changes in this fork against the vanilla LionWiki 3.2.9 distribution
LionWiki was forked in version 3.2.9 as I don't use any newer, because my server
still runs PHP 5.x. Therefore I tried to minimize changes in core files and
do as much as possible in plugins. 

Files with at least one change are:

- index.php 
- config.php 
- plugins/
	- wkp_Blog.php (new file)
	- wkp_Comments.php
	- Comments/template.html

## wkp_Blog.php
The most important code of the blog CMS is in wkp_Blog.php plugin. All
wikipages which have filename in the format YYYYMMDD-HHMM_Some_page_name.txt
are considered to be blog posts. The YYYYMMDD-HHMM is used as the date and
time of publication and stays so no matter the later changes in the pages.

Whenever you save a new page, the plugin does three things:

1. On the page specified as $START_PAGE the list of last $BLOG_COUNT posts
is created. The list is sorted with the newest blog posts on the top and 
contains post title, post publication date and time, link to the full post
and depending on the $BLOG_FULL (TRUE/FALSE) full text of post or just excerpt.
2. On the page specified as $BLOG_ARCHIVE the list of all posts is created,
with the same sorting, but the $BLOG_FULL is ignored and the list contains
always only excerpt of every post.
3. Blog RSS feed is created in var/blog-rss.xml containing [6~last $BLOG_RSS
posts. This count is on purpose different than the $BLOG_COUNT by default,
however nothing prevents you to set both variables to the same value in
config.php. RSS feed also never contains the full post, only excerpt.

Whenever any page is visited, the plugin creates a very simple statistics in
var/stats:

- In file YYYYMMDD-HHMM_Some_page_name.hits.txt contains number of how many
times the page has been rendered in total.
- In file YYYYMMDD-HHMM_Some_page_name.visits.txt contains contains for each
day since publication all IP adresses that visited the page, every IP is for
every day recorded just once. This shows how many unique visitors the page
has per day.

## wkp_Comments.php & Comments/template.html
I modified the wkp_Comments plugin so that it uses a very simple numeric
captcha instead of the original vocabulary one. It's primitive and it works
for me, you can keep the original, if you wish. The comment template was
also modified accordingly and JavaScript usage was removed as I didn't find
it necessary.

## index.php
Just two minor changes from the original:

- If anyone tries to visit page microsoft-Dz, he gets 404 and is redirected
elsewhere (to wikipedia by default). This string in URL triggers malware
monitoring tools on the side of webhosting providers and as on wiki every
page exists even if blank with editing form, I had bad experiences from the
past, when I spent hours convincing admins that my page wasn't hacked. With
this little change it's settled.
- Changed behavior of '''''SOMETEXT''''' which in LionWiki syntax makes text
both bold and italics. It was implemented via clever trick as combination of
'''SOMETEXT''' (bold) and ''SOMETEXT'' (italics), but rendered invalid HTML
<strong><em>SOMETEXT</strong></em>.

## config.php
Nev global variables added and set to some default values. Nothing more.

## New global variables
New global variables introduced (some of them already mentioned above):

- $BLOG_ARCHIVE - page to which blog archive is generated
- $BLOG_FULL - shall $START_PAGE contain full posts or just excerpts?
- $BLOG_COUNT - how many posts shall be on the $START_PAGE?
- $BLOG_RSS - how many posts shall be in the blog-rss.xml?
- $BLOG_MORE_TEXT - text displayed at the end of excerpt which as link
points to the full post (i.e. "READ MORE")
- $BLOG_ARCHIVE_TEXT - text displayed at the end of $START_PAGE which
informs the visitor that all posts are in $BLOG_ARCHIVE
(i.e. "You can find all posts ")
- $BLOG_ARCHIVE_LINK - text of the link for the text from previous variable
(i.e. "in archive")

The content of the last two variables will be rendered to LionWiki syntax as:
'''You can find all posts [in archive|archive].'''

## Conclusion
This code is already used on three blogs:
- [jirka.1-2-8.net](http://jirka.1-2-8.net)
- [i-logout.cz](http://i-logout.cz)
- [vivapowerpc.eu](http://vivapowerpc.eu)

It seems to work. So feel free to grab it and use it. Everything may be
merged to more current LionWiki codebase after I upgrade my PHP some day,
but don't take my word for it.