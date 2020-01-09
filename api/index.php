<?php

	/*
		EMailqueue
		API
	*/

    include_once dirname(__FILE__)."/../config/application.config.inc.php"; // Include emailqueue configuration.
    include_once dirname(__FILE__)."/../config/db.config.inc.php"; // Include Emailqueue's database connection configuration.
    include_once dirname(__FILE__)."/../scripts/emailqueue_inject.class.php"; // Include Emailqueue's emailqueue_inject class.
    
    $emailqueue_inject = new Emailqueue\emailqueue_inject(EMAILQUEUE_DB_HOST, EMAILQUEUE_DB_UID, EMAILQUEUE_DB_PWD, EMAILQUEUE_DB_DATABASE); // Creates an emailqueue_inject object. Needs the database connection information.
	
	$q = json_decode($_POST["q"], true);
	if (is_null($q))
		apiResult(false, "Can't decode query");

	if (!$q["key"] || $q["key"] == "")
		apiResult(false, "No API key passed");

	if ($q["key"] != API_KEY) {
		sleep(rand(1, 3));
		apiResult(false, "Wrong API key");
	}
	
	if (isset($q["message"]) && isset($q["messages"]))
		apiResult(false, "Both message and messages have been passed, please pass only one of them");

	if (isset($q["message"]))
		$q["messages"][] = $q["message"];
	
	foreach ($messages as $message) {
		if (!$message["from"])
			apiResult(false, "Key \"from\" is required");
		if (!$message["to"])
			apiResult(false, "Key \"to\" is required");
		if (!$message["subject"])
			apiResult(false, "Key \"subject\" is required");
		
		try {
			$result = $emailqueue_inject->inject($message);
		} catch (Exception $e) {
			apiResult(false, $e->getMessage());
		}
	}

	apiResult(true);

	function apiResult($isOk, $description = false) {
		echo json_encode([
			"result" => $isOk,
			"description" => $description
		]);
		die;
	}

?>