<?php

/********************************************
*
*	 Case.php
	helper function (class?) for displaying a case
	
	@param $isE 1 if this is an expungement, 0 otherwise
	@param $isR 1 if this is an redaction, 0 otherwise
	@param $isSE 1 if this is a summary expungement, 0 otherwise
	@return a string stating the stype of expungement.  
	note that an expungement will have a 1 for both expungement and redaction fields
	becuase an expungement is a special type of redaction where all charges are redacted
*
*	Copyright 2011-2015 Community Legal Services
* 
* Licensed under the Apache License, Version 2.0 (the "License");
* you may not use this file except in compliance with the License.
* You may obtain a copy of the License at
*
*    http://www.apache.org/licenses/LICENSE-2.0

* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
*
****************************************/


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