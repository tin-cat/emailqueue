<?

	define(VERSION, "3.0.12");
	define(OFFICIAL_PAGE_URL, "http://lorenzoherrera.github.io/emailqueue/");

	include APP_DIR."config/db.config.inc.php";
	include APP_DIR."config/application.config.inc.php";
	
	date_default_timezone_set(DEFAULT_TIMEZONE);
	
	$now = mktime();
	
	include LIB_DIR."/database/database.inc.php";
	include LIB_DIR."/database/dbsource_mysqli.inc.php";
	$db = new dbsource_mysqli(DB_HOST, DB_UID, DB_PWD, DB_DATABASE);
	if(!$db->connect())
	{
		echo "Cannot connect to database";
		die;
	}
	
	$db->query("set names UTF8");
	
	include "classes/out.class.php";
	$out = new out();
	
	include "classes/html.class.php";
	$html = new html();
	
	include "classes/utils.class.php";
	$utils = new utils();
	
	include "classes/messages.class.php";
	$messages = new messages();

	include "classes/logger.class.php";
	$logger = new logger();
	
	function checkemail($email)
	{
        return preg_match("/^[.\w-]+@([\w-]+\.)+[a-zA-Z]{2,15}$/", $email);
	}
	
	function add_incidence($email_id, $description)
	{
		global $db;
		
		$db->query
		("
			insert			into incidences
			(
				email_id,
				date_incidence,
				description
			)
			values
			(
				".$email_id.",
				'".date("Y-n-j H:i:s")."',
				'".$description."'
			)
		");
	}
	
	function mark_as_sent($email_id)
	{
		global $db;		
		$db->query("update emails set is_sent = 1 where id = ".$email_id);
	}
	
	function cancel($email_id)
	{
		global $db;		
		$db->query("update emails set is_cancelled = 1 where id = ".$email_id);
	}
	
	function uncancel($email_id)
	{
		global $db;		
		$db->query("update emails set is_cancelled = 0 where id = ".$email_id);
	}
	
	function block($email_id)
	{
		global $db;		
		$db->query("update emails set is_blocked = 1 where id = ".$email_id);
	}
	
	function unblock($email_id)
	{
		global $db;		
		$db->query("update emails set is_blocked = 0 where id = ".$email_id);
	}
	
	function setsendingnow($email_id)
	{
		global $db;		
		$db->query("update emails set is_sendingnow = 1 where id = ".$email_id);
	}
	
	function unsetsendingnow($email_id)
	{
		global $db;		
		$db->query("update emails set is_sendingnow = 0 where id = ".$email_id);
	}

	function update_send_count($email_id, $count)
	{
		global $db;		
		$db->query("update emails set send_count = ".$count." where id = ".$email_id);
	}
	
	function update_error_count($email_id, $count)
	{
		global $db;		
		$db->query("update emails set error_count = ".$count." where id = ".$email_id);
	}
	
	function update_sentdate($email_id, $timestamp)
	{
		global $db;		
		$db->query("update emails set date_sent = '".date("Y-n-j H:i:s", $timestamp)."' where id = ".$email_id);
	}
	
    function html_2_txt($html)
    {
        $cols = 80;
        $separator = str_repeat("-", $cols);
    
        $txt = $html;
        
        $txt = preg_replace('/\s\s+/', "", $txt);
        $txt = str_replace("<br>", "\r\n", $txt);
        
        // Delete head section
        $txt = preg_replace('/<head>(.*)<\\/head>/i', "", $txt);
        
        // Other deletions
        $txt = str_replace("&gt;", "", $txt);
        $txt = str_replace("&lt;", "", $txt);
        
        // Convert table tags for a better reading
        $txt = preg_replace('@<table[^>]*?>@si', $separator."\r\n", $txt);
        $txt = str_replace("</td>", " ", $txt);
        $txt = str_replace("</tr>", "\r\n", $txt);
        $txt = preg_replace('@</table>@si', $separator."\r\n", $txt);
        
        // Some other converts
        $txt = preg_replace('@<span[^>]*?>@si', " ", $txt);
        $txt = preg_replace('@</span>@si', " ", $txt);
                
        // Convert links
        $txt = preg_replace('/<a href="(.*?)">(.*)<\\/a>/i', "$1 $2", $txt);
        
        // Strip HTML
        $txt = strip_tags($txt);
        
        // Strip duplicate carriage-returns, tabs, etc.
        $txt = str_replace("\t", "", $txt);
        
        // Fotopunto-specific replacements
        $txt = str_replace("foto  punto", "fotopunto", $txt);
        
        // Format to 80 cols (except lines containing urls)
        $txt = wordwrap($txt, 80, "\n");
        
        $txt = trim($txt);
        return $txt;
    }

?>
