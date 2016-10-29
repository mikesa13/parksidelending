<?php
	define ("PROJECT_ROOT", __DIR__);
	define ("PROJECT_NAME", "loans");

	require_once PROJECT_ROOT."/dbglog.php";

	define ("dbg_file", PROJECT_ROOT."/logs/".PROJECT_NAME.".log");

	const MAX_LTV = 40;

	// Loan statuses.
	const LOAN_STATUS_PENDING = 0;
	const LOAN_STATUS_APPROVED = 1;
	const LOAN_STATUS_REJECTED = 2;

	define ("ROW_OBJECT",      1);
	define ("ROW_ASSOCIATIVE", 2);

	// Debug zones for database tables.
	define ("DBGZ_LOANROW",          intval(0x8000000000000000));

	// Debug zones for business logic / processes.
	define ("DBGZ_AUTOLOAD",         intval(0x4000000000000000));
	define ("DBGZ_LOANS",            intval(0x2000000000000000));
?>
