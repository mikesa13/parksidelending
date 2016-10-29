/* Loans REST API wrappers
*/
function CreateLoan(
	ssn,
	propertyValue,
	loanAmount,
	callback
	)
{
	paramsString = "ssn=" + encodeURIComponent(ssn);
	paramsString += "&propertyValue=" + encodeURIComponent(propertyValue);
	paramsString += "&loanAmount=" + encodeURIComponent(loanAmount);

	return $.ajax(
			{
				type: "POST",
				url: "https://parksidelending/v1/loans",
				data: paramsString,
				dataType: "html",
				cache: false,
				success: function(response, textStatus, jqXHR)
				{
					jsonResponse = JSON.parse(response);

					callback(
							jsonResponse.result,
							jsonResponse.resultstring,
							jsonResponse.data
							);
				},
				error: function(jqXHR, textStatus, errorThrown)
				{
					callback("failed", textStatus);
				}
			}
			);
}

function GetLoanInfo(
	loanId,
	callback
	)
{
	return $.ajax(
			{
				type: "GET",
				url: "https://parksidelending/v1/loans/" + loanId,
				dataType: "html",
				cache: false,
				success: function(response, textStatus, jqXHR)
				{
					jsonResponse = JSON.parse(response);

					callback(
							jsonResponse.result,
							jsonResponse.resultstring,
							jsonResponse.data
							);
				},
				error: function(jqXHR, textStatus, errorThrown)
				{
					callback("failed", textStatus);
				}
			}
			);
}

function DeleteLoan(
	loanId,
	callback
	)
{
	return $.ajax(
			{
				type: "DELETE",
				url: "https://parksidelending/v1/loans/" + loanId,
				dataType: "html",
				cache: false,
				success: function(response, textStatus, jqXHR)
				{
					jsonResponse = JSON.parse(response);

					callback(
							jsonResponse.result,
							jsonResponse.resultstring
							);
				},
				error: function(jqXHR, textStatus, errorThrown)
				{
					callback("failed", textStatus);
				}
			}
			);
}
