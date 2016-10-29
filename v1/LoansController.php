<?php

	use Classes\Request;
	use Classes\Loans;
	use Data\LoansDB;

	require_once __DIR__."//project.php";
	require_once __DIR__."//autoload.php";

	define("dbg_zones",  DBGZ_LOANS | DBGZ_LOANROW);
	define("dbg_levels", DBGL_TRACE | DBGL_INFO | DBGL_WARN | DBGL_ERR | DBGL_EXCEPTION);
	define("dbg_dest",   dbg_dest_log);

	DBG_SET_PARAMS(dbg_zones, dbg_levels, false, false, dbg_dest, dbg_file);

	$httpMethod = isset($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : null;

	$parameters = null;

	//
	// examples:
	//    create a loan:     post /v1/loans with post params ssn, propertyvalue, and loanamount
	//    query loan status: get /v1/loans/loanid
	//

	switch ($httpMethod)
	{
		case "get":
			$parameters = $_GET;
			break;

		case "post":
		case "create":
			$parameters = $_POST;
			break;

		case "put":
			parse_str(file_get_contents('php://input'), $putParams);
			$parameters = $putParams;
			break;
	}

	$urlElements = isset($_SERVER['PATH_INFO']) ? explode("/", trim($_SERVER['PATH_INFO'], "/")) : null;

	DBG_VAR_DUMP(DBGZ_LOANS, "LoansController", "_SERVER['PATH_INFO']", $_SERVER['PATH_INFO']);
	DBG_VAR_DUMP(DBGZ_LOANS, "LoansController", "urlElements", $urlElements);

	$request = new Request($httpMethod, $urlElements, $parameters);

	// Connect to database.
	$loansDB = new LoansDB;

	// Create a Loans object that will handle this request.
	$loans = new Loans($request, $loansDB);

	// And now handle the request.
	$result = $loans->performAction($data, $resultString);

	// Create and render the response that will be sent to the client as JSON content.
	$response = array(
			"result" => ($result ? "success" : "failed"),
			"resultstring" => $resultString,
			"data" => $data
			);

	header('Content-Type: application/json');
	echo json_encode($response);
?>
