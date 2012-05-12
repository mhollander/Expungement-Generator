<?php
	// Case.php
	// helper function (class?) for displaying a case
	
	// @param $isE 1 if this is an expungement, 0 otherwise
	// @param $isR 1 if this is an redaction, 0 otherwise
	// @param $isSE 1 if this is a summary expungement, 0 otherwise
	// @return a string stating the stype of expungement.  
	// note that an expungement will have a 1 for both expungement and redaction fields
	// becuase an expungement is a special type of redaction where all charges are redacted
	function getRedactionType($isE, $isR, $isSE)
	{
		if ($isSE)
			return "Summary Expungement";
		elseif ($isE)
			return "Expungement";
		elseif ($isR)
			return "Redaction";
		else
			return "No Expungement Possible";
	}


?>