<?php /* Configuration file for LionWiki. */
$WIKI_TITLE = 'My new wiki'; // name of the site

// SHA1 hash of password. If empty (or commented out), no password is required
// $PASSWORD = sha1("my_password");

$TEMPLATE = 'templates/dandelion.html'; // presentation template

// if true, you need to fill password for reading pages too
// before setting to true, read http://lionwiki.0o.cz/index.php?page=UserGuide%3A+How+to+use+PROTECTED_READ
$PROTECTED_READ = false;

$NO_HTML = true; // XSS protection
$LANG="en";

$START_PAGE="blog";
$BLOG_ARCHIVE="archive";
$BLOG_FULL="true";
$BLOG_COUNT=5;
$BLOG_RSS=8;
$DATE_FORMAT="F j, Y \\a\\t g:i A T";
$BLOG_MORE_TEXT="Read more...";
$BLOG_ARCHIVE_TEXT="You can find all blog posts ";
$BLOG_ARCHIVE_LINKTEXT="in archive";
?>