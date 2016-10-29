<?php

	namespace Data;

	class LoansDB extends \SQLite3
	{
		private $sqlLite = null;

		public function __construct()
		{
			$this->open(PROJECT_ROOT."/Data/loans.db");

			$this->query(
					'CREATE TABLE if not exists loans (loanId INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, ssn TEXT, loanAmount INTEGER, propertyValue INTEGER, loanStatus TEXT, createdTime TEXT, lastUpdatedTime TEXT)'
					);
		}

		public function transactionBegin()
		{
			$this->exec('BEGIN;');
		}

		public function transactionCommit()
		{
			$this->exec('COMMIT;');
		}

		public function transactionRollback()
		{
			$this->exec('ROLLBACK;');
		}
	}
?>
