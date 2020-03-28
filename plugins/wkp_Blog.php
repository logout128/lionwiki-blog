<?php
/*
 * Blog plugin for LionWiki, 2020 by Logout
 * 
 * With this plugin you can use LionWiki as a blog.
 * All pages with name in format "YYYY-MM-DD_Page_name" are considered as blog posts, other are ignored.
 * List of $BLOG_COUNT blog posts is created on $START_PAGE, either with full content or just title+perex+link to full post (depends on $BLOG_FULL).
 * An archive is created with all blog posts on single page in format title+perex+link to full post, page with archive is named $BLOG_ARCHIVE.
 * Blog posts need to have on first line ! with blog post title and second line is considered being perex.
 * A RSS 2.0 file called blog-rss.xml is created in var with $BLOG_RSS items. Other RSS plugins can work without change.
 *
 */

class Blog {
	var $desc = array(
		array("Blog plugin", "turns LionWiki into a blog", "generates list of specified count of blog posts to start page", "creates archive with all blog posts", "posts on start page can be either full or just title+perex+link to post", "creates RSS for the blog")
	);


	/* Things, that need to be handled when, a new blog post is saved: 
		- START_PAGE page is generated
		- BLOG_ARCHIVE is generated
		- blog-rss.xml is generated
	*/		
	function pageWritten()
	{
		global $WIKI_TITLE, $PG_DIR, $LANG,  $START_PAGE, $PAGE_LINK, $WIKI_TITLE, $WIKI_DESCRIPTION, $VAR_DIR, $DATE_FORMAT;
		global $BLOG_ARCHIVE, $BLOG_FULL, $BLOG_COUNT, $BLOG_RSS, $BLOG_MORE_TEXT, $BLOG_ARCHIVE_TEXT, $BLOG_ARCHIVE_LINKTEXT;
		$SITE_LINK = ($_SERVER["HTTPS"] ? "https://" : "http://") . $_SERVER["SERVER_NAME"]. "/";

		$blog_posts = glob("$PG_DIR"."????????-????*.txt");
		rsort($blog_posts);
		$acode = "\n";
		$bcode = "\n";
	
		// RSS header
		$rcode = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<rss version=\"2.0\" xmlns:atom=\"http://www.w3.org/2005/Atom\">
	<channel>
	<title>$WIKI_TITLE - blog</title>
	<link>$SITE_LINK</link>
	<description>$WIKI_DESCRIPTION</description>
	<language>$LANG</language>\n";
	
		$blog_entries = 0;
		$rss_entries = 0;
	
		foreach ($blog_posts as $single_post) {
			$sp_size = filesize($single_post);
			$sp_file = fopen($single_post, "r");
			$sp_title = substr(preg_replace("/[\r*\n*]/", "", fgets($sp_file)),1);
			$sp_perex = fgets($sp_file);
			$sp_perex = preg_replace("/\r*\n+/", "", $sp_perex);
			$sp_rest = fread($sp_file, $sp_size - ftell($sp_file) + 1);
			fclose($sp_file);
		
			preg_match("/([0-9]{8}\-[0-9]{4})\_(.*)\.txt/ui", $single_post, $sp_parts);
			$sp_date_raw = str_replace("-", " ", substr_replace($sp_parts[1], ":", -2, 0));
			$sp_date = date($DATE_FORMAT, strtotime($sp_date_raw));
		
			$acode .= "![".$sp_title."|".$sp_parts[1]."_".$sp_parts[2]."]\n";
			$acode .= "'''''(".$sp_date.")''''' ".$sp_perex." [$BLOG_MORE_TEXT|".$sp_parts[1]."_".$sp_parts[2]."]\n"; 
			$acode .= "\n";

			if ($blog_entries < $BLOG_COUNT) {
				$bcode .= "![".$sp_title."|".$sp_parts[1]."_".$sp_parts[2]."]\n";	
				if ($BLOG_FULL) {
					$bcode .= $sp_perex."\n".$sp_rest." '''''(".$sp_date.")'''''\n----\n";
				} else {
					$bcode .= "'''''(".$sp_date.")''''' ".$sp_perex." [$BLOG_MORE_TEXT|".$sp_parts[1]."_".$sp_parts[2]."]\n"; 
				}			

				$bcode .= "\n";		
			}

			if ($blog_entries < $BLOG_RSS) {
				$rcode .= "\t<item>\n";
				$rcode .= "\t\t<title>$sp_title</title>\n";
				$rcode .= "\t\t<link>$SITE_LINK".$sp_parts[1]."_".$sp_parts[2]."</link>\n";
				$rcode .= "\t\t<description>".strip_tags($sp_perex)."</description>\n";
				$rcode .= "\t\t<guid isPermaLink=\"false\">".md5($sp_parts[1]."_".$sp_parts[2])."</guid>\n";
				$rcode .= "\t\t<pubDate>".date(DATE_RSS, strtotime($sp_date_raw))."</pubDate>\n";
				$rcode .= "\t</item>\n";		
			}		
		
			$blog_entries++;		
		}

		$bcode .= "'''$BLOG_ARCHIVE_TEXT [$BLOG_ARCHIVE_LINKTEXT|$BLOG_ARCHIVE].'''\n";
		$rcode .= "\n\t</channel>\n</rss>\n";

		// We don't want comments on the front blog page and in archive
		$acode .= "{NO_COMMENTS}\n";
		$bcode .= "{NO_COMMENTS}\n";

		// Write the appropriate files	
		$blog_file = fopen($PG_DIR.$START_PAGE.".txt", "w");
		fputs($blog_file, $bcode);
		fclose($blog_file);

		$archive_file = fopen($PG_DIR.$BLOG_ARCHIVE.".txt", "w");
		fputs($archive_file, $acode);
		fclose($archive_file);

		$rss_file = fopen($VAR_DIR."blog-rss.xml", "w");
		fputs($rss_file, $rcode);
		fclose($rss_file);

		// We gone, by-bye
		return true;
	}
	
	/* Things, that need to be handled, when a blog post is displayed:
		- hit counter is inreased for the post
		- IP + date is saved 
	*/
	
	function formatFinished()
	{	
		global $VAR_DIR, $page;
		
		if (!file_exists($VAR_DIR."stats/")) {
			mkdir($VAR_DIR."stats");
		}
		
		$post_hits = file_exists($VAR_DIR."stats/".$page.".hits.txt") ? file_get_contents($VAR_DIR."stats/".$page.".hits.txt") : 0;
		$post_hits++;
		
		$fo = fopen($VAR_DIR."stats/".$page.".hits.txt", "w");
		fputs($fo, $post_hits);
		fclose($fo);

		$visit_entry = date("Y-m-d")." | ".$_SERVER['REMOTE_ADDR'];
		$visits_need_write = false;
		if (file_exists($VAR_DIR."stats/".$page.".visits.txt")) {
			$post_visits=file_get_contents($VAR_DIR."stats/".$page.".visits.txt");			
			if (strpos(str_replace("\n", "", $post_visits), $visit_entry)===false) {
				$post_visits .= $visit_entry."\n";
				$visits_need_write = true;
			}
		} else {		
			$post_visits = $visit_entry."\n";
			$visits_need_write = true;
		}
		
		$fo = fopen($VAR_DIR."stats/".$page.".visits.txt", "w");
		fputs($fo, $post_visits);
		fclose($fo);		
		
		// We gone, by-bye
		return true;
	}
	
}

?>