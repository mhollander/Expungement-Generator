<?php

/**************************************
*
*	ArrestSummary.php
*	Describes a Summary Arrest Record and has functions to parse a summary for helpful information
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
**************************************/

class ArrestSummary
{
	private $arrests = array();
	private $SID;
	private $PID;
	private $archived = false;  // a flag used when reading a summary docket to determine whether we have gotten to the archived cases yet
	private $aliases = array();

	public function __construct() {}

	protected static $SIDSearch = "/SID:\s?((\d+\-)*\d+)/";
	protected static $PIDSearch = "/PID:\s?(\d+)/";
	
	// gets the docket number, DC number, and OTN all in one search
	protected static $docketDCNOTNSearch = "/((MC|CP)\-\d{2}\-\D{2}\-\d*\-\d{4}).+DC No:\s*(\d*).*OTN:\s*(\D\d+)*/";
	
	// matches the arrest Date, Disposition Date, and judge from the summary arrest record
	protected static $arrestDateDispDateJudgeSearch = "/Arrest Dt:\s*(\d{1,2}\/\d{1,2}\/\d{4})?.*Disp Date:\s*(\d{1,2}\/\d{1,2}\/\d{4})?\s*Disp Judge:(.*)/";
	protected static $archivedSearch = "/^Archived$/";
	protected static $archivedCaseNumberSearch = "/((MC|CP)\-\d{2}\-\D{2}\-\d*\-\d{4})/";
	protected static $migratedJudgeSearch = "/migrated/i";
	
	public function getSID() { return $this->SID; }
	public function getPID() { return $this->PID; }
	
	public function setSID($SID) { $this->SID = $SID; }
	public function setPID($PID) { $this->PID = $PID; }
	
	public function getArrestKeys() { return array_keys($this->arrests); }

	// @return true if the arrestRecordFile is a summary docket sheet of all arrests, false if it isn't
	public static function isArrestSummary($arrestRecordFile)
	{
		if (preg_match("/Court Summary/i", $arrestRecordFile[1]))
			return true;
		else
			return false;
	}
	
	// @return true if there is an arrest key that has the docket number supplied
	// @param a docket number (CP-51-CR...)
	public function isArrestInSummary($docket)
	{
		if (isset($this->arrests[$docket]))
			return TRUE;
		else
			return FALSE;
	}

	// @return the arrest summary requestsed based on the $docket as key
	// @param a docket number (CP-51-CR...)
	public function getArrest($docket)
	{
		if (isset($this->arrests[$docket]))
			return $this->arrests[$docket];
		else
			return null;
	}
	
	
	// @return true if there are arrests in here, false otherwise
	public function hasValuableInformation()
	{
		if (count($this->arrests) > 0)
			return true;
		else
			return false;
	}
	
	// @input arrestRecordFile - the arrest record summary as an array of lines, as read by the file function
	// reads through the arrestRecordFile, constructs a proper ArrestSummary, combines like cases
	public function processArrestSummary($arrestRecordFile)
	{
		$this->readArrestSummary($arrestRecordFile);
	}
	
	// reads in a record summary and sets all of the relevant variable.
	// assumes that the record is an array of lines, read through the "file" function.
	// the file should be created by running pdftotext.exe on a pdf of the defendant's arrest.
	public function readArrestSummary($arrestRecordFile)
	{
		foreach ($arrestRecordFile as $line_num => $line)
		{		
			//print "$line_num: $line <br/>";
			
			// first check to see if we have gotten to the archived section of the dockets
			if ($this->archived)
			{
				// add a new arrest to the queue
				if (preg_match(self::$archivedCaseNumberSearch, $line, $matches))
				{
					$arrest = new Arrest();
					$arrest->setDocketNumber(array(trim($matches[1])));
					$this->arrests[trim($matches[1])] = $arrest;
				}
			}
				
			// check to see if we are at the "archived" section, where only case numbers are listed without information beyond that
			if (preg_match(self::$archivedSearch, trim($line),$matches))
				$this->archived=true;
				
			if (preg_match(self::$SIDSearch, $line, $matches))
				$this->setSID(trim($matches[1]));
			if (preg_match(self::$PIDSearch, $line, $matches))
				$this->setPID(trim($matches[1]));

			// if we match the docket/DC/OTN, we also want to check the next line which will have
			// the arrest date and the judge
			// We also want to create a new arrest and add that arrest to the array
			if (preg_match(self::$docketDCNOTNSearch, $line, $matches))
			{
				//print $line . "<br/>";
				$arrest = new Arrest();
				$arrest->setDocketNumber(array(trim($matches[1])));
				if (isset($matches[3]))
					$arrest->setDC(trim($matches[3]));
				if (isset($matches[4]))
					$arrest->setOTN(trim($matches[4]));
				if (preg_match(self::$arrestDateDispDateJudgeSearch,$arrestRecordFile[$line_num+1],$matches2))
				{
					// only set these if the variables are not empty (can't do empty(trim($matches) until PHP 5.5))
					if (trim($matches2[1]) != false)
						$arrest->setArrestDate(trim($matches2[1]));
					if (trim($matches2[2]) != false)
						$arrest->setDispositionDate(trim($matches2[2]));

					// we don't want to set the judge if the judge is "Migrated Judge" or there is 
					// no judge listed.
					if (!preg_match(self::$migratedJudgeSearch, $matches2[3], $junk) && trim($matches2[3]) != "")

						$arrest->setJudge(trim($matches2[3]));
				}
				$this->arrests[trim($matches[1])] = $arrest;
			}
		}
	}
}
?>
