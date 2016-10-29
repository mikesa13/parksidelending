<?php

	namespace Classes;

	use \Data\LoanRow;

	class Loans
	{
		private $request = null;
		private $db = null;

		public function __construct(
			$request,
			$db
			)
		{
			$this->request = $request;
			$this->db = $db;
		}

		//
		// performAction
		//     Dispatches REST APIs invoked by web client to the appropriate method handler.
		//
		// Input Params:
		//     $request:      has info about the REST request, including the http method and parameters.
		//     $db:           Database connection.
		//
		// Output Params:
		//     $data:         Data returned to the web client containing the results of the method.
		//     $resultString: error message on failure, informational/warning message on success
		//
		// Returns:
		//     true:         Success
		//     false:        Failure
		//
		public function performAction(
			&$data,
			&$resultString
			)
		{
 			DBG_ENTER(DBGZ_LOANS, __METHOD__, "httpMethod={$this->request->httpMethod}");

			$result = false;

			switch ($this->request->httpMethod)
			{
				case 'post':
					$result = $this->CreateLoan(
							isset($this->request->parameters['ssn']) ? $this->request->parameters['ssn'] : null,
							isset($this->request->parameters['propertyValue']) ? $this->request->parameters['propertyValue'] : null,
							isset($this->request->parameters['loanAmount']) ? $this->request->parameters['loanAmount'] : null,
							$loanRow,
							$resultString
							);

					if ($result)
					{
						$data = array(
								"loanId" => $loanRow->getLoanId(),
								"propertyValue" => $loanRow->getPropertyValue(),
								"loanAmount" => $loanRow->getLoanAmount(),
								"loanStatus" => $loanRow->getLoanStatus(),
								"createdTime" => $loanRow->getCreatedTime(),
								"lastUpdatedTime" => $loanRow->getLastUpdatedTime()
								);
					}

					break;

				case 'get':
					$loanId = $this->request->urlElements[0];

					$result = $this->GetLoanInfo(
							$loanId,
							$loanRow,
							$resultString
							);

					if ($result)
					{
						$data = $loanRow;
					}

					break;

				case 'delete':
					$loanId = $this->request->urlElements[0];

					$result = $this->DeleteLoan(
							$loanId,
							$resultString
							);

					if ($result)
					{
						$data = null;
					}

					break;

				default:
					$resultString = "method unsupported";
					break;
			}

			DBG_RETURN_BOOL(DBGZ_LOANS, __METHOD__, $result);
			return $result;
		}

		private function SubmitLoanForApproval(
			$loanRow,
			&$resultString
			)
		{
			DBG_ENTER(DBGZ_LOANS, __METHOD__);

			$ltv = 100 * ($loanRow->getLoanAmount() / $loanRow->getPropertyValue());

			if ($ltv > MAX_LTV)
			{
				$loanRow->setLoanStatus(LOAN_STATUS_REJECTED);
				$resultString = "LTV is too high";
			}
			else
			{
				$loanRow->setLoanStatus(LOAN_STATUS_APPROVED);
			}

			$loanRow->setLastUpdatedTime(date("Y-m-d H:i:s"));

			$result = $loanRow->CommitChangedFields($dbError);

			if (!$result)
			{
				$resultString = "dbError $dbError";
			}

			DBG_RETURN_BOOL(DBGZ_LOANS, __METHOD__, $result);
			return $result;
		}

		//
		// CreateLoan
		//     Creates a new loan and approves/rejects based on the LTV.
		//
		// Input Params:
		//     $ssn:           Customer's social security number.
		//     $propertyValue: The value of the property to be used for collateral.
		//     $loanAmount:    The amount the customer would like to borrow.
		//
		// Output Params:
		//     $loanRow:      associative array of loan fields
		//     $resultString: error message on failure, informational/warning message on success
		//
		// Returns:
		//     true:  success
		//     false: failed
		//
		public function CreateLoan(
			$ssn,
			$propertyValue,
			$loanAmount,
			&$loanRow,
			&$resultString
			)
		{
			DBG_ENTER(DBGZ_LOANS, __METHOD__, "ssn=$ssn, propertyValue=$propertyValue, loanAmount=$loanAmount");

			$result = false;

			// Validate parameters
			$validParameters = true;

			if ($ssn === null)
			{
				$resultString = "Missing required parameter 'ssn'";
				DBG_ERR(DBGZ_LOANS, __METHOD__, $resultString);

				$validParameters = false;
			}
			else if ($propertyValue === null)
			{
				$resultString = "Missing required parameter 'propertyValue'";
				DBG_ERR(DBGZ_LOANS, __METHOD__, $resultString);

				$validParameters = false;
			}
			else if (intval($propertyValue, 10) === 0)
			{
				$resultString = "Invalid value for 'propertyValue'";
				DBG_ERR(DBGZ_LOANS, __METHOD__, $resultString);

				$validParameters = false;
			}
			else if ($loanAmount === null)
			{
				$resultString = "Missing required parameter 'loanAmount'";
				DBG_ERR(DBGZ_LOANS, __METHOD__, $resultString);

				$validParameters = false;
			}
			else if (intval($loanAmount, 10) === 0)
			{
				$resultString = "Invalid value for 'loanAmount'";
				DBG_ERR(DBGZ_LOANS, __METHOD__, $resultString);

				$validParameters = false;
			}

			if ($validParameters)
			{
				$now = date("Y-m-d H:i:s");

				$result = LoanRow::Create(
						$this->db,
						$ssn,
						$propertyValue,
						$loanAmount,
						LOAN_STATUS_PENDING,
						$now,    // createdtime
						$now,    // lastUpdatedTime
						$loanRow,
						$dbError
						);

				if ($result)
				{
					$result = $this->SubmitLoanForApproval($loanRow, $resultString);
				}
				else
				{
					$resultString = "dbError $dbError";
					DBG_ERR(DBGZ_LOANS, __METHOD__, $resultString);
				}
			}

			DBG_RETURN_BOOL(DBGZ_LOANS, __METHOD__, $result);
			return $result;
		}

		//
		// GetLoanInfo
		//     Gets information about a loan.
		//
		// Input Params:
		//     $loanId:      Identifier of the loan
		//
		// Output Params:
		//     $loanRow:      associative array of loan fields
		//     $resultString: error message on failure, informational/warning message on success
		//
		// Returns:
		//     true:  success
		//     false: failed
		//
		public function GetLoanInfo(
			$loanId,
			&$loanRow,
			&$resultString
			)
		{
			DBG_ENTER(DBGZ_LOANS, __METHOD__, "loanId=$loanId");

			$result = false;

			// Validate parameters
			$validParameters = true;

			if ($loanId === null)
			{
				$resultString = "Missing required parameter 'loanId'";
				DBG_ERR(DBGZ_LOANS, __METHOD__, $resultString);

				$validParameters = false;
			}

			if ($validParameters)
			{
				// First the loan record in the database.
				$loanRow = LoanRow::FindOne(
						$this->db,
						NULL,
						array("loanId=$loanId"),
						NULL,
						ROW_ASSOCIATIVE,
						$dbError
						);

				if ($loanRow)
				{
					$result = true;
				}
				else
				{
					if ($dbError == 0)
					{
						$resultString = "not found";
						DBG_ERR(DBGZ_LOANS, __METHOD__, $resultString);
					}
					else
					{
						$resultString = "dbError $dbError";
						DBG_ERR(DBGZ_LOANS, __METHOD__, $resultString);
					}
				}
			}

			DBG_RETURN_BOOL(DBGZ_LOANS, __METHOD__, $result);
			return $result;
		}

		//
		// Delete
		//     Deletes the given loan.
		//
		// Input Params:
		//     $loanId :     Identifier of the loan
		//
		// Output Params:
		//     $resultString: error message on failure, informational/warning message on success
		//
		// Returns:
		//     true:  success
		//     false: failed
		//
		private function DeleteLoan(
			$loanId,
			&$resultString
			)
		{
			DBG_ENTER(DBGZ_LOANS, __METHOD__, "loanId=$loanId");

			$result = false;

			// Validate parameters
			$validParameters = true;

			if ($loanId === null)
			{
				$resultString = "Missing required parameter 'loanId'";
				DBG_ERR(DBGZ_LOANS, __METHOD__, $resultString);

				$validParameters = false;
			}

			if ($validParameters)
			{
				$result = LoanRow::Delete($this->db, array("loanId=$loanId"), $dbError);

				if (!$result)
				{
					$resultString = "dbError $dbErrod";
					DBG_ERR(DBGZ_LOANS, __METHOD__, $resultString);
				}
			}

			DBG_RETURN_BOOL(DBGZ_LOANS, __METHOD__, $result);
			return $result;
		}

	}
?>
