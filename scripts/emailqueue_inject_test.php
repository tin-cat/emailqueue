<html>
<head><title>EMailqueue - PHP Inject class test</title></head>
<body>
EMailqeue - PHP Inject class test
<hr>
<?

    define("LIB_DIR", "lib");
    include LIB_DIR."/database/database.inc.php";
	include LIB_DIR."/database/dbsource_mysql.inc.php";

    include "emailqueue_inject.class.php";
    
    $emailqueue_inject = new emailqueue_inject("localhost", "user", "password", "emailqueue");
    
    $result = $emailqueue_inject->inject
    (
        null, // foreign_id_a
        null, // foreign_id_b
        null, // priority
        true, // is_inmediate
        null, // date_queued
        true, // is_html
        "from@email.com", // from
        "From name", // from_name
        "to@email.com", // to
        "replyto@email.com", // replyto
        "Reply to name", // replyto_name
        "Test subject", // subject
        "<html><body>Test <b><i>HTML</i></b> message</body></html>", // content
        "Alternative non-html text", // content_non_html
        false // list_unsubscribe_url
    );
    
    if($result)
        echo "Message correctly queued.<br>";
    else
        echo "Error while queing message.<br>";
    
    $emailqueue_inject->destroy();

?>
</body>
</html>
