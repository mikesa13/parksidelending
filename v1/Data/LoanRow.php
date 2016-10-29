<?php

	namespace Data;

	class LoanRow
	{
		private $dbcon = null;

		private $loanId = null;
		private $ssn = null;
		private $propertyValue = null;
		private $loanAmount = null;
		private $loanStatus = null;
		private $createdTime = null;
		private $lastUpdatedTime = null;

		private $changedFields = array();

		private function fieldUpdated($fieldName, $fieldValue)
		{
			$this->changedFields[$fieldName] = $fieldValue;
		}

		public function getLoanId()
		{
			return $this->loanId;
		}

		public function getSSN()
		{
			return $this->ssn;
		}

		public function setSSN($value)
		{
			if ($this->ssn != $value)
			{
				$this->ssn = $value;
				$this->fieldUpdated('ssn', $value);
			}
		}

		public function getPropertyValue()
		{
			return $this->propertyValue;
		}

		public function setPropertyValue($value)
		{
			if ($this->propertyValue != $value)
			{
				$this->propertyValue = $value;
				$this->fieldUpdated('propertyValue', $value);
			}
		}

		public function getLoanAmount()
		{
			return $this->loanAmount;
		}

		public function setLoanAmount($value)
		{
			if ($this->loanAmount != $value)
			{
				$this->loanAmount = $value;
				$this->fieldUpdated('loanAmount', $value);
			}
		}

		public function getLoanStatus()
		{
			return $this->loanStatus;
		}

		public function setLoanStatus($value)
		{
			if ($this->loanStatus != $value)
			{
				$this->loanStatus = $value;
				$this->fieldUpdated('loanStatus', $value);
			}
		}

		public function getCreatedTime()
		{
			return $this->createdTime;
		}

		public function setCreatedTime($value)
		{
			if ($this->createdTime != $value)
			{
				$this->createdTime = $value;
				$this->fieldUpdated('createdTime', $value);
			}
		}

		public function getlastUpdatedTime()
		{
			return $this->lastUpdatedTime;
		}

		public function setlastUpdatedTime($value)
		{
			if ($this->lastUpdatedTime != $value)
			{
				$this->lastUpdatedTime = $value;
				$this->fieldUpdated('lastUpdatedTime', $value);
			}
		}

		public function __construct(
			$dbcon,
			$loanId,
			$ssn,
			$propertyValue,
			$loanAmount,
			$loanStatus,
			$createdTime,
			$lastUpdatedTime
			)
		{
			$this->dbcon = $dbcon;

			$this->loanId = $loanId;
			$this->ssn = $ssn;
			$this->propertyValue = $propertyValue;
			$this->loanAmount = $loanAmount;
			$this->loanStatus = $loanStatus;
			$this->createdTime = $createdTime;
			$this->lastUpdatedTime = $lastUpdatedTime;
		}

		public function __destruct()
		{
		}

		static public function Create(
			$dbcon,
			$ssn,
			$propertyValue,
			$loanAmount,
			$loanStatus,
			$createdTime,
			$lastUpdatedTime,
			&$object,
			&$dbError
			)
		{
			DBG_ENTER(DBGZ_LOANROW, __METHOD__, "ssn=$ssn, propertyValue=$propertyValue, loanAmount=$loanAmount, loanStatus=$loanStatus, createdTime=$createdTime, lastUpdatedTime=$lastUpdatedTime");

			$result = $dbcon->query(
				 	"INSERT INTO loans (ssn, propertyValue, loanAmount, loanStatus, createdTime, lastUpdatedTime)
					VALUES ('$ssn', '$propertyValue', '$loanAmount', '$loanStatus', '$createdTime', '$lastUpdatedTime')"
					);

			if ($result)
			{
				$loanId = $dbcon->lastInsertRowID();

				$object = new LoanRow(
						$dbcon,
						$loanId,
						$ssn,
						$propertyValue,
						$loanAmount,
						$loanStatus,
						$createdTime,
						$lastUpdatedTime
						);
			}
			else
			{
				$dbError = $dbcon->lastErrorCode();
				DBG_ERR(DBGZ_LOANROW, __METHOD__, "Failed to insert row with error=$dbError, ".$dbcon->lastErrorMsg());
			}

			DBG_RETURN_BOOL(DBGZ_LOANROW, __METHOD__, $result);
			return $result;
		}

		static public function FindOne(
			$dbcon,
			$fields,
			$filters,
			$sortOrder,
			$returnType,
			&$dbError
			)
		{
			$row = null;

			$rows = LoanRow::Find($dbcon, $fields, $filters, $sortOrder, $returnType, $dbError);

			if ($rows)
			{
				$row = $rows[0];
			}

			return $row;
		}

		static public function Find(
			$dbcon,
			$fields,
			$filters,
			$sortOrder,
			$returnType,
			&$dbError
			)
		{
			DBG_ENTER(DBGZ_LOANROW, __METHOD__);

			$rows = null;

			$selectFields = "loanid";
			$numSelectFields = 0;

			// Construct query from the fields, filters, and sort order
			if ($fields != null)
			{
				foreach ($fields as $field)
				{
					// Don't need to add loanId as it's already included by default.
					if ($field != "loanid")
					{
						$selectFields = "$selectFields, $field";
					}
				}
			}
			else
			{
				$selectFields = "*";
			}

			$filterString = "";
			$numFilters = 0;

			if ($filters != null)
			{
				foreach ($filters as $filter)
				{
					if ($numFilters == 0)
					{
						$filterString = "WHERE ($filter)";
					}
					else
					{
						$filterString = "$filterString AND ($filter)";
					}

					$numFilters += 1;
				}
			}

			$sortString = "";

			if ($sortOrder != null)
			{
				$sortString = "ORDER BY $sortOrder";
			}

			DBG_INFO(DBGZ_LOANROW, __METHOD__, "selectFields='$selectFields', sortString='$sortString', filterString='$filterString'");

			// Execute the query.
			$result = $dbcon->query("SELECT $selectFields FROM loans $filterString $sortString");

			if ($result !== false)
			{
				// Create an array of all the returned rows.  Each row will be returned either as an associative array or an object.
				while (($row = $result->fetchArray(SQLITE3_ASSOC)) !== false)
				{
					if ($returnType == ROW_ASSOCIATIVE)
					{
						$rows[] = $row;
					}
					else
					{
						$rows[] = new LoanRow(
								$dbcon,
								isset($row['loanid']) ? $row['loanid'] : null,
								isset($row['ssn']) ? $row['ssn'] : null,
								isset($row['propertyValue']) ? $row['propertyValue'] : null,
								isset($row['loanAmount']) ? $row['loanAmount'] : null,
								isset($row['loanStatus']) ? $row['loanStatus'] : null,
								isset($row['createdTime']) ? $row['createdTime'] : null,
								isset($row['lastUpdatedTime']) ? $row['lastUpdatedTime'] : null
								);
					}
				}
			}
			else
			{
				$dbError = $dbcon->lastErrorCode();
				DBG_ERR(DBGZ_LOANROW, __METHOD__, "Select failed with error=$dbError, ".$dbcon->lastErrorMsg());
			}

			DBG_RETURN(DBGZ_LOANROW, __METHOD__, "found ".count($rows)." rows");
			return $rows;
		}

		public function CommitChangedFields(
			&$dbError
			)
		{
			DBG_ENTER(DBGZ_LOANROW, __METHOD__, "loanId=$this->loanId");

			$result = false;
			$numFieldsChanged = count($this->changedFields);

			if ($numFieldsChanged > 0)
			{
				if ($this->loanId != null)
				{
					$setString = "";

					while (($changedField = current($this->changedFields)) !== false)
					{
						$fieldName = key($this->changedFields);
						$setString = "$setString $fieldName='$changedField', ";

						next($this->changedFields);
					}

					$setString = trim($setString, ', ');

					DBG_INFO(DBGZ_LOANROW, __METHOD__, "Updating row with loanId=$this->loanId. Number of fields changed: $numFieldsChanged. setString='$setString'");

					$result = $this->dbcon->query(
							"UPDATE loans
							 SET $setString
							 WHERE loanId='$this->loanId'"
							);

					if ($result !== false)
					{
						// Fields were written.  Reset the changedFields array.
						$this->changedFields = array();
					}
					else
					{
						$dbError = $dbcon->lastErrorCode();
						DBG_ERR(DBGZ_LOANROW, __METHOD__, "Failed to update row with error=$dbError, ".mysqli_error($this->$dbcon));
					}
				}
				else
				{
					DBG_WARN(DBGZ_LOANROW, __METHOD__, "Must set loanId property before calling this method.");
				}
			}
			else
			{
				DBG_INFO(DBGZ_LOANROW, __METHOD__, "No fields were changed, nothing to update.");

				// Nothing to change but we should return true.
				$result = true;
			}

			DBG_RETURN_BOOL(DBGZ_LOANROW, __METHOD__, $result);
			return $result;
		}

		public static function Update(
			$dbcon,
			$fields,
			$filters,
			&$dbError
			)
		{
			DBG_ENTER(DBGZ_LOANROW, __METHOD__);

			$result = false;

			$filterString = "";
			$numFilters = 0;

			if ($filters != null)
			{
				foreach ($filters as $filter)
				{
					if ($numFilters == 0)
					{
						$filterString = "WHERE ($filter)";
					}
					else
					{
						$filterString = "$filterString AND ($filter)";
					}

					$numFilters += 1;
				}
			}

			$setString = "";

			foreach ($fields as &$field)
			{
				$setString = "$setString $field, ";
			}

			$setString = trim($setString, ', ');

			DBG_INFO(DBGZ_LOANROW, __METHOD__, "setString='$setString', filterString='$filterString'");

			$result = $dbcon->query("UPDATE loans SET $setString $filterString");

			if ($result === false)
			{
				$dbError = $dbcon->lastErrorCode();
				DBG_ERR(DBGZ_LOANROW, __METHOD__, "Failed to update row with error=$dbError, ".$dbcon->lastErrorMsg());
			}

			DBG_RETURN_BOOL(DBGZ_LOANROW, __METHOD__, $result);
			return $result;
		}

		public static function Delete(
			$dbcon,
			$filters,
			&$dbError
			)
		{
			DBG_ENTER(DBGZ_LOANROW, __METHOD__);

			$retval = false;

			$filterString = "";
			$numFilters = 0;

			if ($filters != null)
			{
				foreach ($filters as $filter)
				{
					if ($numFilters == 0)
					{
						$filterString = "WHERE ($filter)";
					}
					else
					{
						$filterString = "$filterString AND ($filter)";
					}

					$numFilters += 1;
				}
			}

			DBG_INFO(DBGZ_LOANROW, __METHOD__, "filterString='$filterString'");

			$result = $dbcon->query("DELETE FROM loans $filterString");

			if ($result === false)
			{
				$dbError = $dbcon->lastErrorCode();
				DBG_ERR(DBGZ_LOANROW, __METHOD__, "Failed to delete rows with error=$dbError, ".$dbcon->lastErrorMsg());
			}

			DBG_RETURN_BOOL(DBGZ_LOANROW, __METHOD__, $result);
			return $result;
		}
	}
?>
