<?php
	// @@@@@@@@ TODO - Add extra columns to the arrest column to see if expungement, summary, ard, etc...
	// @todo pick up judge and case number from MC case and put on petition and order
	// @todo make aliases work
	// @todo detect non-philly cases and exclude them?  note them?
	// @todo think about whether I want to have the charges array check to see if a duplicate
	//		 charge is being added and prevent duplicate charges.  A good example of this is if
	//       a charge is "replaced by information" and then later there is a disposition. 
	// 		 we probably don't want both the replaced by information and the final disposition on 
	// 		 the petition.  This is especially true if the finally dispoition is Guilty
	// @todo add verification and ifp; add checkboxes for non philly expungements; judge address?

require_once("Charge.php");
require_once("Person.php");
require_once("Attorney.php");
require_once("utils.php");

class Arrest
{

	private $county;
	private $OTN;
	private $DC;
	private $docketNumber = array();
	private $arrestingOfficer;
	private $arrestingAgency;
	private $arrestDate;
	private $complaintDate;
	private $judge;
	private $DOB;
	private $dispositionDate;
	private $firstName;
	private $lastName;
	private $charges = array();
	private $costsTotal;
	private $costsPaid;
	private $costsCharged;
	private $costsAdjusted;
	private $bailTotal;
	private $bailCharged;
	private $bailPaid;
	private $bailAdjusted;
	private $bailTotalTotal;
	private $bailChargedTotal;
	private $bailPaidTotal;
	private $bailAdjustedTotal;
	private $isCP;
	private $isCriminal;
	private $isARDExpungement;
	private $isExpungement;
	private $isRedaction;
	private $isHeldForCourt;
	private $isSummaryArrest = FALSE;
	private $isArrestSummaryExpungement;
	
	public static $redactionTemplate = "redactionTemplate.odt";
	public static $expungementTemplate = "expungementTemplate.odt";
	public static $redactionTemplateIFP = "redactionTemplateIFP.odt";
	public static $expungementTemplateIFP = "expungementTemplateIFP.odt";
	public static $summaryExpungementTemplate = "summaryExpungementTemplate.odt";
	public static $summaryExpungementTemplateIFP = "summaryExpungementTemplateIFP.odt";
	public static $ARDexpungementTemplate = "ARDexpungementTemplate.odt";
	public static $ARDexpungementTemplateIFP = "ARDexpungementTemplateIFP.odt";
	public static $overviewTemplate = "overviewTemplate.odt";
	
	protected static $unknownInfo = "N/A";
	
	protected static $countySearch = "/\sof\s(\w+)\sCOUNTY/i";
	protected static $OTNSearch = "/OTN:\s+(\D\d+)/";
	protected static $DCSearch = "/District Control Number\s+(\d+)/";
	protected static $docketSearch = "/Docket Number:\s+((MC|CP)\-\d{2}\-(\D{2})\-\d*\-\d{4})/";
	protected static $arrestingAgencyAndOfficerSearch = "/Arresting Agency:\s+(.*)\s+Arresting Officer: (\D+)/";
	protected static $arrestDateSearch = "/Arrest Date:\s+(\d{1,2}\/\d{1,2}\/\d{4})/";
	protected static $complaintDateSearch = "/Complaint Date:\s+(\d{1,2}\/\d{1,2}\/\d{4})/";
	protected static $judgeSearch = "/Final Issuing Authority:\s+(.*)/";
	protected static $judgeAssignedSearch = "/Judge Assigned:\s+(.*)\s+Date Filed:/";
	protected static $migratedJudgeSearch = "/migrated/i";
	protected static $DOBSearch = "/Date Of Birth:?\s+(\d{1,2}\/\d{1,2}\/\d{4})/i";
	protected static $nameSearch = "/^Defendant\s+(.*), (.*)/";

	// ($1 = charge, $2 = disposition, $3 = code section
	protected static $chargesSearch = "/\d\s+\/\s+(.*[^Not])\s+(Guilty|Not Guilty|Nolle Prossed|Guilty Plea|Guilty Plea - Negotiated|Withdrawn|Charge Changed|Held for Court|Dismissed - Rule 1013 \(Speedy|Dismissed - LOP|Dismissed - LOE|Dismissed|ARD - County|ARD|Transferred to Another Jurisdiction|Transferred to Juvenile Division|Quashed|Judgment of Acquittal \(Prior to)\s+\w{0,2}\s+(\w{1,2}\247\d+(\-|\247|\w+)*)/"; // removed "Replacement by Information"
	protected static $chargesSearchOverflow = "/^\s+(\w+\s*\w*)\s*$/";
	// disposition date can appear in two different ways (that I have found):
	// 1) it can appear on its own line, on a line that looks like: 
	//    Status   mm/dd/yyyy    Final  Disposition
	//    Trial   mm/dd/yyyy    Final  Disposition
	//    Preliminary Hearing   mm/dd/yyyy    Final  Disposition
	//    Migrated Dispositional Event   mm/dd/yyyy    Final  Disposition
	// 2) on the line after the charge disp
	protected static $dispDateSearch = "/(Status|Status of Restitution|Status - Community Court|Status Listing|Migrated Dispositional Event|Trial|Preliminary Hearing)\s+(\d{1,2}\/\d{1,2}\/\d{4})\s+Final Disposition/";
	protected static $dispDateSearch2 = "/(.*)\s(\d{1,2}\/\d{1,2}\/\d{4})/";
	
	// this is a crazy one.  Basically matching whitespace then $xx.xx then whitespace then 
	// -$xx.xx, etc...  The fields show up as Assesment, Payment, Adjustments, Non-Monetary, Total
	protected static $costsSearch = "/Totals:\s+\\$([\d\,]+\.\d{2})\s+-?\\$([\d\,]+\.\d{2})\s+-?\\$([\d\,]+\.\d{2})\s+-?\\$([\d\,]+\.\d{2})\s+-?\\$([\d\,]+\.\d{2})/";
	protected static $bailSearch = "/Bail.+\\$([\d\,]+\.\d{2})\s+-?\\$([\d\,]+\.\d{2})\s+-?\\$([\d\,]+\.\d{2})\s+-?\\$([\d\,]+\.\d{2})\s+-?\\$([\d\,]+\.\d{2})/";
	public function __construct () {}
	
	
	//getters
	public function getCounty() { if (!isset($this->county)) $this->setCounty(self::$unknownInfo); return $this->county; }
	public function getOTN() { if (!isset($this->OTN)) $this->setOTN(self::$unknownInfo); return $this->OTN; }
	public function getDC() { if (!isset($this->DC)) $this->setDC(self::$unknownInfo); return $this->DC; }
	public function getDocketNumber() { return $this->docketNumber; }
	public function getArrestingOfficer() { return $this->arrestingOfficer; }
	public function getArrestingAgency() { return $this->arrestingAgency; }
	public function getArrestDate() { return $this->arrestDate; }
	public function getComplaintDate() { return $this->complaintDate; }
	//  getDispositionDate() exists elsewhere
	public function getJudge() { return $this->judge; }
	public function getDOB() { return $this->DOB; }
	public function getfirstName() { return $this->firstName; }
	public function getLastName() { return $this->lastName; }
	public function getCharges() { return $this->charges; }
	public function getCostsTotal() { if (!isset($this->costsTotal)) $this->setCostsTotal("0"); return $this->costsTotal; }
	public function getCostsPaid()  { if (!isset($this->costsPaid)) $this->setCostsPaid("0");return $this->costsPaid; }
	public function getCostsCharged() { if (!isset($this->costsCharged)) $this->setCostsCharged("0"); return $this->costsCharged; }
	public function getCostsAdjusted()  { if (!isset($this->costsAdjusted)) $this->setCostsAdjusted("0");return $this->costsAdjusted; }
	public function getBailTotal() { if (!isset($this->bailTotal)) $this->setBailTotal("0"); return $this->bailTotal; }
	public function getBailPaid()  { if (!isset($this->bailPaid)) $this->setBailPaid("0");return $this->bailPaid; }
	public function getBailCharged() { if (!isset($this->bailCharged)) $this->setBailCharged("0"); return $this->bailCharged; }
	public function getBailAdjusted()  { if (!isset($this->bailAdjusted)) $this->setBailAdjusted("0");return $this->bailAdjusted; }
	public function getBailTotalTotal() { if (!isset($this->bailTotalTotal)) $this->setBailTotalTotal("0"); return $this->bailTotalTotal; }
	public function getBailPaidTotal()  { if (!isset($this->bailPaidTotal)) $this->setBailPaidTotal("0");return $this->bailPaidTotal; }
	public function getBailChargedTotal() { if (!isset($this->bailChargedTotal)) $this->setBailChargedTotal("0"); return $this->bailChargedTotal; }
	public function getBailAdjustedTotal()  { if (!isset($this->bailAdjustedTotal)) $this->setBailAdjustedTotal("0");return $this->bailAdjustedTotal; }
	public function getIsCP()  { return $this->isCP; }
	public function getIsCriminal()  { return $this->isCriminal; }
	public function getIsARDExpungement()  { return $this->isARDExpungement; }
	public function getIsExpungement()  { return $this->isExpungement; }
	public function getIsRedaction()  { return $this->isRedaction; }
	public function getIsHeldForCourt()  { return $this->isHeldForCourt; }
	public function getIsSummaryArrest()  { return $this->isSummaryArrest; }
		
	//setters
	public function setCounty($county) { $this->county = ucwords(strtolower($county)); }
	public function setOTN($OTN) { $this->OTN = $OTN; }
	public function setDC($DC) { $this->DC = $DC; }
	public function setDocketNumber($docketNumber) { $this->docketNumber = $docketNumber; }
	public function setIsSummaryArrest($isSummaryArrest)  { $this->isSummaryArrest = $isSummaryArrest; } 
	public function setArrestingOfficer($arrestingOfficer) {  $this->arrestingOfficer = ucwords(strtolower($arrestingOfficer)); }
	
	// when we set the arresting agency, replace any string "PD" with "Police Dept"
	public function setArrestingAgency($arrestingAgency) {  $this->arrestingAgency = preg_replace("/\bpd\b/i", "Police Dept",$arrestingAgency); }
	
	public function setArrestDate($arrestDate) {  $this->arrestDate = $arrestDate; }
	public function setComplaintDate($complaintDate) {  $this->complaintDate = $complaintDate; }
	public function setJudge($judge) { $this->judge = $judge; }
	public function setDispositionDate($dispositionDate) { $this->dispositionDate = $dispositionDate; }
	public function setDOB($DOB) { $this->DOB = $DOB; }
	public function setFirstName($firstName) { $this->firstName = $firstName; }
	public function setLastName($lastName) { $this->lastName = $lastName; }
	public function setCharges($charges) {  $this->charges = $charges; }
	public function setCostsTotal($costsTotal) {  $this->costsTotal = $costsTotal; }
	public function setCostsPaid($costsPaid)  {  $this->costsPaid = $costsPaid; }
	public function setCostsCharged($costsCharged) {  $this->costsCharged = $costsCharged; }
	public function setCostsAdjusted($costsAdjusted)  {  $this->costsAdjusted = $costsAdjusted; }
	public function setBailTotal($bailTotal) {  $this->bailTotal = $bailTotal; }
	public function setBailPaid($bailPaid)  {  $this->bailPaid = $bailPaid; }
	public function setBailCharged($bailCharged) {  $this->bailCharged = $bailCharged; }
	public function setBailAdjusted($bailAdjusted)  {  $this->bailAdjusted = $bailAdjusted; }
	public function setBailTotalTotal($bailTotal) {  $this->bailTotalTotal = $bailTotal; }
	public function setBailPaidTotal($bailPaid)  {  $this->bailPaidTotal = $bailPaid; }
	public function setBailChargedTotal($bailCharged) {  $this->bailChargedTotal = $bailCharged; }
	public function setBailAdjustedTotal($bailAdjusted)  {  $this->bailAdjustedTotal = $bailAdjusted; }
	public function setIsCP($isCP)  {  $this->isCP = $isCP; }
	public function setIsCriminal($isCriminal)  {  $this->isCriminal = $isCriminal; }
	public function setIsARDExpungement($isARDExpungement)  {  $this->isARDExpungement = $isARDExpungement; }
	public function setIsExpungement($isExpungement)  {  $this->isExpungement = $isExpungement; }
	public function setIsRedaction($isRedaction)  {  $this->isRedaction = $isRedaction; }
	public function setIsArrestSummaryExpungement($isSummaryExpungement) { $this->isArrestSummaryExpungement = $isSummaryExpungement; }
	public function setIsHeldForCourt($isHeldForCourt)  {  $this->isHeldForCourt = $isHeldForCourt; }

	// add a Bail amount to an already created bail figure
	public function addBailTotal($bailTotal) 
	{  
		$this->bailTotal = $this->getBailTotal() + $bailTotal; 
		$this->bailTotalTotal = $this->getBailTotalTotal() + $bailTotal; 
	}
	public function addBailPaid($bailPaid)  
	{  
		$this->bailPaid = $this->getBailPaid() + $bailPaid; 
		$this->bailPaidTotal = $this->getBailPaidTotal() + $bailPaid; 
	}
	public function addBailCharged($bailCharged) 
	{  
		$this->bailCharged = $this->getBailCharged() + $bailCharged; 
		$this->bailChargedTotal = $this->getBailChargedTotal() + $bailCharged; 
	}
	public function addBailAdjusted($bailAdjusted)  
	{  
		$this->bailAdjusted = $this->getBailAdjusted() + $bailAdjusted; 
		$this->bailAdjustedTotal = $this->getBailAdjustedTotal() + $bailAdjusted; 
	}
	
	// push a single chage onto the charge array
	public function addCharge($charge) {  $this->charges[] = $charge; }
	
	// @returns the proper template depending on whether this is an expungment, redaction, and ifp or not
	// @param redaction - true if this is a redaction, false if an expungement
	// @param ifp - true if this is an ifp petition
	public function getTemplateName($ifp)
	{
		// ARD Expungement check has to go first, b/c isArrestExpungement includes ARD offenses
		if ($this->isArrestARDExpungement())
		{
			if ($ifp)
				return self::$ARDexpungementTemplateIFP;
			else
				return self::$ARDexpungementTemplate;
		}
		if ($this->isArrestExpungement())
		{
			if ($ifp)
				return self::$expungementTemplateIFP;
			else
				return self::$expungementTemplate;
		}
		else if($this->isArrestSummaryExpungement)
		{
			if ($ifp)
				return self::$summaryExpungementTemplateIFP;
			else
				return self::$summaryExpungementTemplate;
		}
		
		else
		{
			if ($ifp)
				return self::$redactionTemplateIFP;
			else
				return self::$redactionTemplate;
		
		}
	}
	
	// @return the first docket number on the array, which should be the CP or lead docket num
	public function getFirstDocketNumber() 
	{
		
		if (count($this->getDocketNumber()) > 0)
		{
			$docketNumber = $this->getDocketNumber();
			return $docketNumber[0];
		}
		else
			return NULL;
	}
	
	// @return true if the arrestRecordFile is a docket sheet, false if it isn't
	public function isDocketSheet($arrestRecordFile)
	{
		if (preg_match("/Docket/i", $arrestRecordFile[1]))
			return true;
		else
			return false;
	}
		
	// reads in a record and sets all of the relevant variable.
	// assumes that the record is an array of lines, read through the "file" function.
	// the file should be created by running pdftotext.exe on a pdf of the defendant's arrest.
	// this does not read the summary.
	public function readArrestRecord($arrestRecordFile)
	{
		foreach ($arrestRecordFile as $line_num => $line)
		{

		
			// print "$line_num: $line<br/>" . self::$countySearch;
			
			// figure out which county we are in
			if (preg_match(self::$countySearch, $line, $matches))
				$this->setCounty(trim($matches[1]));
					
			// find the docket Number
			else if (preg_match(self::$docketSearch, $line, $matches))
			{
				$this->setDocketNumber(array(trim($matches[1])));

				// we want to set this to be a summary offense if there is an "SU" in the 
				// docket number.  The normal docket number looks like this:
				// CP-##-CR-########-YYYY or CP-##-SU-#######-YYYYYY; the latter is a summary
				if (trim($matches[3]) == "SU")
					$this->setIsSummaryArrest(TRUE);
				else
					$this->setIsSummaryArrest(FALSE);
			}
			
			else if (preg_match(self::$OTNSearch, $line, $matches))
				$this->setOTN(trim($matches[1]));
			
			else if (preg_match(self::$DCSearch, $line, $matches))
				$this->setDC(trim($matches[1]));

			
			else if (preg_match(self::$arrestDateSearch, $line, $matches))
				$this->setArrestDate(trim($matches[1]));

			else if (preg_match(self::$complaintDateSearch, $line, $matches))
				$this->setComplaintDate(trim($matches[1]));

			// aresting agency and officer are on the same line, so we have to find
			// them together and deal with them together.
			else if (preg_match(self::$arrestingAgencyAndOfficerSearch, $line, $matches))
			{
				// first set the arresting agency
				$this->setArrestingAgency(trim($matches[1]));

				// then deal with the arresting officer
				$ao = trim($matches[2]);
				
				// if there is no listed affiant or the affiant is "Affiant" then set arresting 
				// officer to "Unknown Officer"
				if ($ao == "" || !(stripos("Affiant", $ao)===FALSE))
					$ao = "Unknown Officer";
				$this->setArrestingOfficer($ao);
			}	

			// the judge name can appear in multiple places.  Start by checking to see if the
			// judge's name appears in the Judge Assigned field.  If it does, then set it.
			// Later on, we'll check in the "Final Issuing Authority" field.  If it appears there
			// and doesn't show up as "migrated," we'll reassign the judge name.
			else if (preg_match(self::$judgeAssignedSearch, $line, $matches))
			{
				if (!preg_match(self::$migratedJudgeSearch, $matches[1], $junk))
					$this->setJudge(trim($matches[1]));
			}
			
			else if (preg_match(self::$judgeSearch, $line, $matches))
			{
				// make sure the judge field isn't blank or doesn't equal "migrated"
				if (!preg_match(self::$migratedJudgeSearch, $matches[1], $junk) && trim($matches[1]) != "")
					$this->setJudge(trim($matches[1]));
			}
			
			
			else if  (preg_match(self::$DOBSearch, $line, $matches))
				$this->setDOB(trim($matches[1]));
			
			else if (preg_match(self::$nameSearch, $line, $matches))
			{
				$this->setFirstName(trim($matches[2]));
				$this->setLastName(trim($matches[1]));
			}

			else if (preg_match(self::$dispDateSearch, $line, $matches))
				$this->setDispositionDate($matches[2]);
				
			// charges can be spread over two lines sometimes; we need to watch out for that
			else if (preg_match(self::$chargesSearch, $line, $matches))
			{
				$charge = trim($matches[1]);
				// we need to check to see if the next line has overflow from the charge.
				// this happens on long charges, like possession of controlled substance
				$i = $line_num+1;
				
				if (preg_match(self::$chargesSearchOverflow, $arrestRecordFile[$i], $chargeMatch))
				{
					$charge .= " " . trim($chargeMatch[1]);
					$i++;
				}
	
			
				// need to grab the disposition date as well, which is on the next line
				if (isset($this->dispositionDate))
					$dispositionDate = $this->getDispositionDate();
				else if (preg_match(self::$dispDateSearch2, $arrestRecordFile[$i], $dispMatch))
					// set the date;
					$dispositionDate = $dispMatch[2];
				else
					$dispositionDate = NULL;
					
				$charge = new Charge($charge, $matches[2], trim($matches[3]), trim($dispositionDate));
				$this->addCharge($charge);
				
			}
			
			else if (preg_match(self::$bailSearch, $line, $matches))
			{
				$this->addBailCharged(doubleval(str_replace(",","",$matches[1])));  
				$this->addBailPaid(doubleval(str_replace(",","",$matches[2])));  // the amount paid
				$this->addBailAdjusted(doubleval(str_replace(",","",$matches[3])));
				$this->addBailTotal(doubleval(str_replace(",","",$matches[5])));  // tot final amount, after all adjustments
			}

			else if (preg_match(self::$costsSearch, $line, $matches))
			{
				$this->setCostsCharged(doubleval(str_replace(",","",$matches[1])));  
				$this->setCostsPaid(doubleval(str_replace(",","",$matches[2])));  // the amount paid
				$this->setCostsAdjusted(doubleval(str_replace(",","",$matches[3])));
				$this->setCostsTotal(doubleval(str_replace(",","",$matches[5])));  // tot final amount, after all adjustments
			}
		}
	}
		
	// Compares two arrests to see if they are part of the same case.  Two arrests are part of the 
	// same case if the DC or OTNs match; first check DC, then check OTN.
	// There are some cases where the OTNs match, but not the DC.  This can happen when:
	// someone is arrest and charged with multiple sets of crimes; all of these cases go to CP court
	// but they aren't consolidated.  B/c the arrests happened at the same time, OTN will
	// be the same on all cases, but the DC numbers will only match from the MC to the CP that 
	// follows
	// Don't match true if we match ourself
	public function compare($that)
	{
		// return false if we match ourself
		if ($this->getFirstDocketNumber() == $that->getFirstDocketNumber())
			return FALSE;
		else if ($this->getDC() != self::$unknownInfo && $this->getDC() == $that->getDC())
			return TRUE;
		else if ($this->getDC() == self::$unknownInfo && ($this->getOTN() != self::$unknownInfo && $this->getOTN() == $that->getOTN()))
		  	return TRUE;
		else
			return FALSE;
	}

	// combines the $this and $that. We assume for the purposes of this function that
	// $this and $that are the same docket number as that was previously checked
	// @param $that is an Arrest, but obtained from the Summary docket sheet, so it doesn't
	// have charge information with, just judge, arrest date, etc...
	public function combineWithSummary($that)
	{
		if ($that->getJudge() != "")
			$this->setJudge($that->getJudge());
		if (!isset($this->arrestDate) || $this->getArrestDate() == self::$unknownInfo)
			$this->setArrestDate($that->getArrestDate());
		if (!isset($this->dispositionDate) || $this->getDispositionDate() == self::$unknownInfo)
			$this->setDispositionDate($that->getDispositionDate());
	}
	
	//gets the first docket number on the array
	// Compares $this arrest to $that arrest and determines if they are actually part of the same
	// case.  Two arrests are part of the same case if they have the same OTN or DC number.
	// If the two arrests are part of the same case, combines them by taking all of the information
	// from one case and adding it to the other case (unless that information is already there.
	// It is important to note that you can only combine a CP case with an MC case.  You cannot
	// two MC cases together without a CP.
	// @param $that = Arrest to combine with $this
	public function combine($that)
	{
		
		// if $this isn't a CP case, then don't combine.  If $that is a CP case, don't combine.
		if (!$this->isCP() || $that->isCP())
		{
			return FALSE;
		}
		
		// return false if we don't find something with the same DC or OTN number
		if (!$this->compare($that))
			return FALSE;
		
		// if $that (the MC case) is an expungement itself, then we don't want to combine.
		// If the MC case was an expungement, then no charges will move up from the MC case
		// to the associated CP case.  This happens in the following situation: 
		// Person is arrested and charged with three different sets of crimes that show up on
		// 3 different MC cases.  One of the MC cases is completely resolved at the prelim hearing
		// and charges are dismissed.  The other two MC cases have "held for court" charges
		// which are brought up to a CP case.  THe CP case OTN will match all three MC cases, but 
		// will only have charges from the two MC cases that were "held for court"
		if ($that->isArrestExpungement())
			return FALSE;
		
		// combine docket numbers
		$this->setDocketNumber(array_merge($this->getDocketNumber(),$that->getDocketNumber()));
		
		// combine charges.  Only include $that charges that are not "held for court"
		// The reason for this is that held for court charges will already appear on the CP,
		// they will just appear with a disposition.  We don't want to include held for court
		// charges and then assume that this isn't an expungement in our later logic.
		// This is a possible future thing to change.  Perhaps held for court should be put on
		// And something should be "expungeable" regardless of whether "held for court"
		// charges are on there.
		$heldForCourtMatch = "/Held for Court/";
		$thatChargesNoHeldForCourt = array();
		foreach ($that->charges as $charge)
		{
			if (!preg_match($heldForCourtMatch, $charge->getDisposition()))
				$thatChargesNoHeldForCourt[] = $charge;
		}
		
		// if $thatChargesNoHeldForCourt[] has less elements than $that->charges, we know that
		// some charges were disposed of at the lower court level.  In that case, we need to
		// add the lower court judges in as well on the expungement sheet.
		// @todo add judges here
		
		// combine bail amounts.  This isn't used for the petitions, but it is helpful for later
		// when we print out the overview of bail.  
		// Generally speaking, an individual could have a bail assessment on an MC case, even if
		// all charged went to CP court (this would happen if they failed to appear for a hearing
		// and then later appeared, were sent to CP court, and were tried there.
		// generally speaking, there are not fines on an MC case that is ultimately combined with
		// a CP case.
		$this->setCharges(array_merge($this->getCharges(),$thatChargesNoHeldForCourt));
		$this->setBailChargedTotal($this->getBailChargedTotal()+$that->getBailChargedTotal());
		$this->setBailTotalTotal($this->getBailTotalTotal()+$that->getBailTotalTotal());
		$this->setBailAdjustedTotal($this->getBailAdjustedTotal()+$that->getBailAdjustedTotal());
		$this->setBailPaidTotal($this->getBailPaidTotal()+$that->getBailPaidTotal());
		
		return TRUE;
	}

	
	// @return a comma separated list of all of the dispositions that are on the "charges" array
	// @param if redactableOnly is true (default) returns only redactable offenses
	
	public function getDispList($redactableOnly=TRUE)
	{
		$disposition = "";
		foreach ($this->getCharges() as $charge)
		{
			// if we are only looking for redactable charges, skip this charge if it isn't redactable
			if ($redactableOnly && !$charge->isRedactable())
				continue;
			if ((stripos($disposition,$charge->getDisposition())===FALSE))
			{
				if ($disposition != "")
					$disposition .= ", ";
				$disposition .= $charge->getDisposition();
			}
		}
		return $disposition;
	}
	
	// @param redactableOnly - boolean defaults to false; if set to true, only returns redactable charges
	// @return a string holding a comma separated list of charges that are in the charges array; 
	// @return if "redactableOnly" is TRUE, returns only those charges that are expungeable
	
	public function getChargeList($redactableOnly=FALSE)
	{
		$chargeList = "";
		foreach ($this->getCharges() as $charge)
		{
			// if we are trying to only get the list of "Expungeable" offenses, then 
			// continue to the next charge if this charge is not Expungeable
			if ($redactableOnly && !$charge->isRedactable())
				continue;
			if ($chargeList != "")
				$chargeList .= ", ";
			$chargeList .= ucwords(strtolower($charge->getChargeName()));
		}
		return $chargeList;
	}

	
	
	// returns the age based off of the DOB read from the arrest record
	public function getAge()
	{
		$birth = new DateTime($this->getDOB());
		$today = new DateTime();
		return dateDifference($today, $birth);
	}

	// @return the disposition date of the first charge on the charges array
	// @return if no disposition date exists on the first chage, then sets the dipsositionDate to the migrated disposition date
	public function getDispositionDate()
	{
		if (!isset($this->dispositionDate))
		{
			if (count($this->charges))
			{
				$firstCharge = $this->getCharges();
				$this->setDispositionDate($firstCharge[0]->getDispDate());
			}
			else
				$this->setDispositionDate(self::$unknownInfo);
		}	
		
		return $this->dispositionDate;
	}
	
	// @function getBestDispotiionDate returns a dispotition date if available.  Otherwise returns
	// the arrest date.
	// @return a date
	public function getBestDispositionDate()
	{
		if ($this->getDispositionDate() != self::$unknownInfo)
			return $this->getDispositionDate();
		else
			return $this->getArrestDate();
	}

	// returns true if this is a criminal offense.  this is true if we see CP|MC-##-CR|SU, 
	// not SA or MD
	public function isArrestCriminal()
	{
		if (isset($this->isCriminal))
			return  $this->getIsCriminal();
		else
		{
			$criminalMatch = "/CR|SU/";
			if (preg_match($criminalMatch, $this->getFirstDocketNumber()))
			{
					$this->setIsCriminal(TRUE);
					return TRUE;
			}
			$this->setIsExpungement(FALSE);
			return FALSE;
		}
	}

	// returns true if this arrest includes ARD offenses.
	public function isArrestARDExpungement()
	{
		if (isset($this->isARDExpungement))
			return  $this->getIsARDExpungement();
		else
		{
			foreach ($this->getCharges() as $num=>$charge)
			{
				if($charge->isARD())
				{
					$this->setIsARDExpungement(TRUE);
					return TRUE;
				}
			}
			$this->setIsARDExpungement(FALSE);
			return FALSE;
		}
	}
	
	// @function isArrestSummaryExpungement - returns true if this is an expungeable summary 
	// arrest.  
	// This is true in a slightly more complicated sitaution than the others.  To be a 
	// summary expungement a few things have to be true:
	// 1) This has to be a summary offense, characterized by "SU" in the docket number.
	// 2) The person must have been found guilty or plead guilty to the charges (if they were
	// not guilty or dismissed, then there is nothing to worry about - normal expungmenet.
	// 3) The person must have five years arrest free AFTER the arrest.  This doesn't have to be 
	// the five years immediately following the arrest nor does it have to be the most recent five
	// years.  It just has to be five years arrest free at some point post arrest.  
	// @note - a problem that might come up is if someone has a summary and then is confined in jail
	// for a long period of time (say 10 years).  This will apear eligible for a summary exp, but
	// is not.
	// @param arrests - an array of all of the other arrests that we are comparing this too to see
	// if they are 5 years arrest free
	// @return TRUE if the conditions above are met; FALSE if not.
	public function isArrestSummaryExpungement($arrests)
	{
		// if already set, then just return the member variable
		if (isset($this->isArrestSummaryExpungement))
			return $this->isArrestSummaryExpungement;
			
		// return false right away if this is not a summary arrest
		if (!$this->getIsSummaryArrest())
		{
			$this->setIsArrestSummaryExpungement(FALSE);
			return FALSE;
		} 	
		
		// loop through all of the charges; only do a summary exp if none are redactible
		// NOTE: THis may be a problem for HELD FOR COURT charges; keep this in mind
		// NOTE: Is it possible that someone has some not guilty and some guilty for summary charges?
		foreach ($this->getCharges() as $num=>$charge)
		{
			if($charge->isRedactable())
			{
				$this->setIsArrestSummaryExpungement(FALSE);
				return FALSE;
			}
		}
			
		// at this point we know two things: summary arrest and the charges are all guilties.
		// now we need to check to see if they are arrest free for five years.	
		// Loop through all of the arrests passed in to get the disposition dates or the 
		// arrest dates if the disposition dates don't exist.  
		// Drop dates that are before this date.
		// Make a sorted array of all of the dates and find the longest gap.
		$thisDispDate = new DateTime($this->getBestDispositionDate());
		$dispDates = array();

		$dispDates[] = $thisDispDate;
		$dispDates[] = new DateTime(); // add today onto the array as well
		foreach ($arrests as $arrest)
		{
			$thatDispDate = new DateTime($arrest->getBestDispositionDate());
			// if the disposition date of that arrest was before this arrest, ignore it
			if ($thatDispDate < $thisDispDate)
				continue;
			else
				$dispDates[] = $thatDispDate;
		}
		// sort array
		asort($dispDates);

		// sort through the first n-1 members of the dateArray and compare them to the next
		// item in the array to see if there is more than 5 years between them
		for ($i=0; $i<(sizeof($dispDates)-1); $i++)
		{
			if (abs(dateDifference($dispDates[$i+1], $dispDates[$i])) >= 5)
			{
				$this->setIsArrestSummaryExpungement(TRUE);
				return TRUE;
			}
		}
		
		// if we got here, it means there are no five year periods of freedom
		$this->setIsArrestSummaryExpungement(FALSE);
		return FALSE;
			
	}
	

	// returns true if this is an expungeable arrest.  this is true if no charges are guilty
	// or guilty plea or held for court.
	public function isArrestExpungement()
	{
		if (isset($this->isExpungement))
			return  $this->getIsExpungement();
		else
		{
			foreach ($this->getCharges() as $num=>$charge)
			{
				// the quirky case where on a CP, the held for court charges are listed from the MC
				// case.
				if ($this->isCP() && $charge->getDisposition() == "Held for Court")
					continue;
				if(!$charge->isRedactable())
				{
					$this->setIsExpungement(FALSE);
					return FALSE;
				}
			}
			
			// deal with the quirky case where there are no charges on the array.  This happens
			// rarely where there is a docket sheet that lists charges, but doesn't list
			// dispositions at all.
			if (count($this->getCharges()) == 0)
			{
					$this->setIsExpungement(FALSE);
					return FALSE;
			}
			
			$this->setIsExpungement(TRUE);
			return TRUE;
		}
	}
	
	// returns true if the first docket number starts with "CP"
	public function isCP()
	{
		if (isset($this->isCP))
			return $this->getIsCP();
		else
		{
			$match = "/^CP/";
			if (preg_match($match, $this->getFirstDocketNumber()))
			{
				$this->setIsCP(TRUE);
				return TRUE;
			}
			
			$this->setIsCP(FALSE);
			return FALSE;
		}
	}
	
	// returns true if this is a redactable offense.  this is true if there are charges that are NOT
	// guilty or guilty plea or held for court.  returns true for expungements as well.
	public function isArrestRedaction()
	{
		if (isset($this->isRedaction))
			return  $this->getIsRedaction();
		else
		{
			foreach ($this->getCharges() as $charge)
			{
				// if we don't match Guilty|Guilty Plea|Held for court, this is redactable
				if ($charge->isRedactable())
				{
					$this->setIsRedaction(TRUE);
					return TRUE;
				}
			}
			// if we ever get here, we have no redactable offenses, so return false
			$this->setIsRedaction(FALSE);
			return FALSE;
		}
	}
	
	// return true if any of the charges are held for court.  this means we are ripe for 
	// consolodating with another arrest
	public function isArrestHeldForCourt()
	{
		if (isset($this->isHeldForCourt))
				return  $this->getIsHeldForCourt();
		else
		{
			$heldForCourtMatch = "/Held for Court/";
			foreach ($this->getCharges() as $num=>$charge)
			{
				// if we match Held for court, setheldforcourt = true
				if (preg_match($heldForCourtMatch,$charge->getDisposition()))
				{
					$this->setIsHeldForCourt(TRUE);
					return TRUE;
				}
			}
			// if we ever get here, we have no heldforcourt offenses, so return false
			$this->setIsHeldForCourt(FALSE);
			return FALSE;
	
		}
	}
	
	// @returns an associative array with court information based on the county name
	public function getCourtInformation($db)
	{
		// sql statements are case insensitive by default
		$query = "SELECT * FROM court WHERE court.county='" . $this->getCounty() . "'";
		$result = mysql_query($query, $db);
		if (!$result) 
		{
			if ($GLOBALS['debug'])
				die('Could not get the county information from the DB:' . mysql_error());
			else
				die('Could not get the county Information from the DB');
		}
		$row = mysql_fetch_assoc($result);
		return $row;
	}
	
	
	// @var newTemplate - A boolean.  True if we are to use the newstyle of template
	public function writeExpungement($inputDir, $outputDir, $person, $attorney, $newTemplate, $db)
	{
		$odf;
		if ($newTemplate)
			$odf = new odf($inputDir . "790ExpungementTemplate.odt");
		else
			$odf = new odf($inputDir . $this->getTemplateName($attorney->getIFP()));
		if ($GLOBALS['debug'])
			print $this->getTemplateName($attorney->getIFP());
		
		if ($GLOBALS['debug'])
			print ($this->isArrestExpungement() || $this->isArrestSummaryExpungement)?("Performing Expungement"):("Performing Redaction");
		
		// set attorney variables
		$odf->setVars("ATTORNEY_HEADER", $attorney->getPetitionHeader());
		$odf->setVars("ATTORNEY_SIGNATURE", $attorney->getPetitionSignature());
		$odf->setVars("ATTORNEY_FIRST", $attorney->getFirstName());
		$odf->setVars("ATTORNEY_LAST", $attorney->getLastName());
		$odf->setVars("ATTORNEY_ELEC_SIG", $attorney->getFirstName() . " " . $attorney->getLastName());

		// set the date.  Format = Month[word] Day[number], Year[4 digit number]
		$odf->setVars("PETITION_DATE", date("F j, Y"));
		// set the type of petition
		if ($newTemplate)
		{
			if ($this->isArrestRedaction() && !$this->isArrestExpungement())
			{
				$odf->setVars("EXPUNGEMENT_OR_REDACTION","Redaction");
				$odf->setVars("EXPUNGED_OR_REDACTED", "Redacted");
			}
			else if ($this->isArrestExpungement() || $this->isArrestSummaryExpungement)
			{
				$odf->setVars("EXPUNGEMENT_OR_REDACTION", "Expungement");
				$odf->setVars("EXPUNGED_OR_REDACTED", "Expunged");
			}
		}
		
		if ($attorney->getIFP())
			$odf->setVars("IFP_MESSAGE", $attorney->getIFPMessage());
		
		
		// setting docket number involves looping through all docket numbers and setting
		// each on on there as a line.
		// Because ODFPHP doesn't like having the same segment reproduced within a document
		// multiple times, we have to name the segment on the petition, order, and verification
		// something different.  hence docketnumber, docketnumber1, and docketnumber2.  
		$theDocketNum = $odf->setSegment("docketnumber");
		$theDocketNum1 = $odf->setSegment("docketnumber1");
		if ($newTemplate)
		{
			$theDocketNum2 = $odf->setSegment("docketnumber2");
			$theDocketNum3 = $odf->setSegment("docketnumber3");
		}
		else
		{
			if ($attorney->getIFP())
				$theDocketNum2 = $odf->setSegment("docketnumber2");
		}
		
		foreach ($this->getDocketNumber() as $value)
		{
			$theDocketNum->setVars("CP", $value);
			$theDocketNum1->setVars("CP", $value);
			if ($newTemplate)
			{
				$theDocketNum2->setVars("CP", $value);
				$theDocketNum3->setVars("CP", $value);
			}
			else
			{
				if ($attorney->getIFP())
					$theDocketNum2->setVars("CP", $value);
			}
			$theDocketNum->merge();
			$theDocketNum1->merge();
			if ($newTemplate)
			{
				$theDocketNum2->merge();
				$theDocketNum3->merge();
			}
			else 
			{
				if ($attorney->getIFP())
					$theDocketNum2->merge();
			}
		}
		$odf->mergeSegment($theDocketNum);
		$odf->mergeSegment($theDocketNum1);
		if ($newTemplate)
		{
			$odf->mergeSegment($theDocketNum2);
			$odf->mergeSegment($theDocketNum3);
			
		}
		else
		{
			if ($attorney->getIFP())
				$odf->mergeSegment($theDocketNum2);
		}
		
		/// do the same for aliases
		if ($newTemplate)
		{
			$odf->setVars("ALIASES", $person->getAliasCommaList());
		}
		else
		{
			$theAlias = $odf->setSegment("alias");
			$theAlias1 = $odf->setSegment("alias1");
			if ($attorney->getIFP())
				$theAlias2 = $odf->setSegment("alias2");
			foreach ($person->getAlias() as $value)
			{
				if ($value=="" || $value==null)
					continue;
				$aliasValue = "aka: " . $value;
				$theAlias->setVars("ALIASES", $aliasValue);
				$theAlias1->setVars("ALIASES", $aliasValue);
				if ($attorney->getIFP())
					$theAlias2->setVars("ALIASES", $aliasValue);
				$theAlias->merge();
				$theAlias1->merge();
				if ($attorney->getIFP())
					$theAlias2->merge();
			}
			$odf->mergeSegment($theAlias);
			$odf->mergeSegment($theAlias1);
			if ($attorney->getIFP())
				$odf->mergeSegment($theAlias2);
		}
				
		if (!$newTemplate)
			$odf->setVars("FIRST_DOCKET_NUM", $this->getFirstDocketNumber());
		$odf->setVars("OTN", $this->getOTN());
		$odf->setVars("DC", $this->getDC());
		$odf->setVars("PP", $person->getPP());
		$odf->setVars("SSN", $person->getSSN());
		$odf->setVars("DOB", $this->getDOB());
		$odf->setVars("SID", $person->getSID());
		
		// setting the disposition list is different dependingo on what type of expungement
		// this is.  An ARD will say that the ard was completed; a summary might say something else
		// and a regular expungement/redaction will say the actual dispositions
		if ($newTemplate)
		{
			// if this is an ard expungement
			
			$odf->setVars("DISPOSITION_LIST", $this->getDispList());
			if ($this->isArrestARDExpungement())
			{
				$odf->setVars("ARD_EXTRA", " and the petitioner successfully completed ARD. The ARD completion letter is attached to this petition");
				$odf->setVars("SUMMARY_EXTRA", "");
			}
			else if ($this->isArrestSummaryExpungement)
			{
				$odf->setVars("DISPOSITION_LIST", "summary convictions");
				$odf->setVars("ARD_EXTRA", "");
				$odf->setVars("SUMMARY_EXTRA", " summary convictions. The petitioner has been arrest free for more than five years since this summary conviction");
			}
			else
			{
				$odf->setVars("DISPOSITION_LIST", $this->getDispList());
				$odf->setVars("ARD_EXTRA", "");
				$odf->setVars("SUMMARY_EXTRA", "");
			}
		}
		
		// if this is not the new 490 or 790 template, then we have to do things differently
		else
		{
			if (!$this->isArrestSummaryExpungement)
				$odf->setVars("DISPOSITION_LIST", $this->getDispList());
		}
		
		//$odf->setVars("DISPOSITION_DATE", $this->getDispositionDate());
		$odf->setVars("FIRST_NAME", $this->getFirstName());
		$odf->setVars("LAST_NAME", $this->getLastName());
		$odf->setVars("STREET", $person->getStreet());
		$odf->setVars("CITY", $person->getCity());
		$odf->setVars("STATE", $person->getState());
		$odf->setVars("ZIP", $person->getZip());
		
		$today = new DateTime();
		$odf->setVars("ORDER_YEAR", $today->format('Y'));

		// for costs, we have to subtract out any effect that bail may have had on the costs and fines.  The rules only require
		// that we tell the court costs and fines accrued and paid off, not bail accrued and paid off
		$odf->setVars("TOTAL_FINES", "$" . number_format($this->getCostsCharged() - $this->getBailCharged(),2));
		$odf->setVars("FINES_PAID", "$" . number_format($this->getCostsPaid() + $this->getCostsAdjusted() - $this->getBailPaid() - $this->getBailAdjusted(),2));
		
		// if judge exists, then write "Judge $judge"; otherwise write "Unknown Judge"
		$odf->setVars("JUDGE", (isset($this->judge) && $this->getJudge() != "") 
			? "Judge " . $this->getJudge() : "Unknown Judge");
		$odf->setVars("ARREST_DATE", $this->getArrestDate());
		$odf->setVars("AFFIANT", $this->getArrestingOfficer());

		if (!$newTemplate)
		{
			$odf->setVars("AGE", $this->getAge());
			$odf->setVars("CHARGE_LIST", $this->getChargeList());
		}
//		if ($this->isArrestRedaction() && !$this->isArrestExpungement())
//			$odf->setVars("EXPUNGEABLE_CHARGE_LIST", $this->getChargeList(TRUE));
		
		
		// set some of the vars that only appear in the new template
		if ($newTemplate)
		{
			$odf->setVars("COUNTY", $this->getCounty());
			$odf->setVars("ARRESTING_AGENCY", $this->getArrestingAgency());
			$odf->setVars("COMPLAINT_DATE", $this->getComplaintDate());		

			$courtInformation = $this->getCourtInformation($db);
			
			$odf->setVars("COURT_NAME", $courtInformation['courtName']);
			$odf->setVars("COURT_STREET", $courtInformation['address']) . ' ' . $courtInformation['address2'];
			$odf->setVars("COURT_CITY_STATE_ZIP", $courtInformation['city'] . ", " . $courtInformation['state'] . " " . $courtInformation['zip']);
			
			// if this is a summary arrest and we aren't in philadelphia, this is a 490 petition
			// otherwise it is a 790 petition
			if ($this->isArrestSummaryExpungement)
				$odf->setVars("490_OR_790", "490");
			else
				$odf->setVars("490_OR_790", "790");

		}
		
		$theCharges=$odf->setSegment("charges");
		$theCharges1=$odf->setSegment("charges1");
		foreach ($this->getCharges() as $charge)
		{
			if (!$this->isArrestSummaryExpungement && !$charge->isRedactable())
				continue;
			if ($this->isArrestSummaryExpungement && !$charge->isSummaryRedactable()) 
				continue;
			
			// sometimes disp date isn't associated with a charge.  If not, just use the disposition
			// date of the whole shebang.  It is a good guess, at the very least.
			$dispDate = $charge->getDispDate();
			if ($dispDate == null || $dispDate == "")
				$dispDate = $this->getDispositionDate();
				
			$theCharges->CHARGE($charge->getChargeName());
			$theCharges->CODE_SEC($charge->getCodeSection());
			$theCharges->DISP($charge->getDisposition());
			$theCharges->DISP_DATE($dispDate);
			$theCharges->merge();

			$theCharges1->CHARGE1($charge->getChargeName());
			$theCharges1->CODE_SEC1($charge->getCodeSection());
			$theCharges1->DISP1($charge->getDisposition());
			$theCharges1->DISP_DATE1($dispDate);
			$theCharges1->merge();
		}
		$odf->mergeSegment($theCharges);
		$odf->mergeSegment($theCharges1);


		// save template to file
		$outputFile = $outputDir . $this->getFirstName() . $this->getLastName() . $this->getFirstDocketNumber();
		if ($this->isArrestARDExpungement())
			$outputFile .= "ARDExpungement";
		else if ($this->isArrestExpungement())
			$outputFile .= "Expungement";
		else 
			$outputFile .= "Redaction";
		$odf->saveToDisk($outputFile . ".odt");
		return $outputFile . ".odt";

	}
	
	public function simplePrint()
	{
		echo "<div>Docket #:";
		foreach ($this->getDocketNumber() as $value)
			print "$value | ";
		echo "</div><div>";
		echo "Name: " . $this->getFirstName() . ". " . $this->getLastName();
		echo "</div><div>";
		echo "DOB: " . $this->getDOB();
		echo "</div><div>";
		echo "age: " . $this->getAge();
		echo "</div><div>";
		echo "OTN: " . $this->getOTN();
		echo "</div><div>";
		echo "DC: " .$this->getDC();
		echo "</div><div>";
		echo "arrestingOfficer: " .$this->getArrestingOfficer();
		echo "</div><div>";
		echo "arrestDate: " . $this->getArrestDate();
		echo "</div><div>";
		echo "judge: " .$this->getJudge();
		echo "</div>";
		foreach ($this->getCharges() as $num=>$charge)
		{
			echo "<div>";
			echo "charge $num: " . $charge->getChargeName() . "|".$charge->getDisposition()."|".$charge->getCodeSection()."|".$charge->getDispDate();
			echo "</div>";
		}
		echo "<div>";
		echo "Total Costs: " .$this->getCostsTotal();
		echo "</div><div>";
		echo "Costs Paid: " . $this->getCostsPaid();
		echo "</div>";
	}
	
					
	public function writeExpungementToDatabase($person, $attorney, $db)
	{
		// the defendant has already been inserted
		// next insert the arrest, which includes the defendant ID
		// next insert each charge, which includes the arrest id and the defendant ID
		// finally insert the expungement, which includes the arrest id, the defendant id, the chargeid, the userid, and a timestamp
		$defendantID = $person->getPersonID();
		$attorneyID = $attorney->getUserID();
		$arrestID = $this->writeArrestToDatabase($defendantID, $db);

		// we only want to write an expungement to the database if this is a redactable arrest
		if ($this->isArrestExpungement() || $this->isArrestRedaction() || $this->isArrestSummaryExpungement)
			$expungementID = $this->writeExpungementDataToDatabase($arrestID, $defendantID, $attorneyID, $db);
		else
			$expungementID = "NULL";
			
		$numRedactableCharges = 0;
		foreach ($this->getCharges() as $charge)
		{
			// if the charge isn't redactable, we don't want to include an expungement ID
			// The expungementID may be placed on other charges from the same arrest.
			// We use a tempID so that we don't change the value of the main variable each time 
			// through the loop.
			// If we are a redactable charge, increment the counter.
			$tempExpungementID = $expungementID;
			if (!$charge->isRedactable() && !$this->isArrestSummaryExpungement)
				$tempExpungementID = "NULL";
			else
				$numRedactableCharges++;
				
			$chargeID = $this->writeChargeToDatabase($charge, $arrestID, $defendantID, $tempExpungementID, $db);
		}
		
		$this->updateExpungementWithNumCharges($expungementID, $numRedactableCharges, $db);
		
	}
	
	// @return the id of the arrest just inserted into the database
	// @param $defendantID - the id of the defendant that this arrest concerns
	// @param $db - the database handle
	public function writeArrestToDatabase($defendantID, $db)
	{
		$sql = "INSERT INTO arrest (`defendantID`, `OTN` ,`DC` ,`docketNumPrimary` ,`docketNumRelated` ,`arrestingOfficer` ,`arrestDate` ,`dispositionDate` ,`judge` ,`costsTotal` ,`costsPaid` ,`costsCharged` ,`costsAdjusted` ,`bailTotal` ,`bailCharged` ,`bailPaid` ,`bailAdjusted` ,`bailTotalToal` ,`bailChargedTotal` ,`bailPaidTotal` ,`bailAdjustedTotal` ,`isARD` ,`isSummary` ,`county` ,`policeLocality`) VALUES ('$defendantID', '" . $this->getOTN() . "', '" . $this->getDC() . "', '" . $this->getFirstDocketNumber() . "', '" . implode("|", $this->getDocketNumber()) . "', '" . $this->getArrestingOfficer() . "', '" . dateConvert($this->getArrestDate()) . "', '" . dateConvert($this->getDispositionDate()) . "', '" . $this->getJudge() . "', '" . $this->getCostsTotal() . "', '" . $this->getCostsPaid() . "', '" . $this->getCostsCharged() . "', '" . $this->getCostsAdjusted() . "', '" . $this->getBailTotal() . "', '" . $this->getBailCharged() . "', '" . $this->getBailPaid() . "', '" . $this->getBailAdjusted() . "', '" . $this->getBailTotalTotal() . "', '" . $this->getBailChargedTotal() . "', '" . $this->getBailPaidTotal() . "', '" . $this->getBailAdjustedTotal() . "', '" . $this->getIsARDExpungement() . "', '" . $this->getIsSummaryArrest() . "', '" . $this->getCounty() . "', '" . $this->getArrestingAgency() . "')";

		if ($GLOBALS['debug'])
			print $sql;
		$result = mysql_query($sql, $db);
		if (!$result) 
		{
			if ($GLOBALS['debug'])
				die('Could not add the arrest to the DB:' . mysql_error());
			else
				die('Could not add the arrest to the DB');
		}
		return mysql_insert_id();
	}
	
	// @return the id of the charge just inserted into the database
	// @param $defendantID - the id of the defendant that this arrest concerns
	// @param $db - the database handle
	// @param $charge - the charge that we are inserting
	// @param $arrestID - the id of the arrest that we are innserting
	public function writeChargeToDatabase($charge, $arrestID, $defendantID, $expungementID, $db)
	{
		$sql = "INSERT INTO charge (`arrestID`, `defendantID`, `expungementID`, `chargeName`, `disposition`, `codeSection`, `dispDate`, `isARD`, `isExpungeableNow`, `grade`, `arrestDate`) VALUES ('$arrestID', '$defendantID', $expungementID, '" . $charge->getChargeName() . "', '" . $charge->getDisposition() . "', '" . $charge->getCodeSection() . "', '" . dateConvert($charge->getDispDate()) . "', '" . $charge->getIsARD() . "', '" . $charge->getIsRedactable() . "', '" . $charge->getGrade() . "', '" . dateConvert($this->getArrestDate()) . "')";
		
		$result = mysql_query($sql, $db);
		if (!$result) 
		{
			if ($GLOBALS['debug'])
				die('Could not add the arrest to the DB:' . mysql_error());
			else
				die('Could not add the arrest to the DB');
		}
		return mysql_insert_id();
	}
	
	// @return the expungementID
	// @param $defendantID - the id of the defendant that this arrest concerns
	// @param $db - the database handle
	// @param $arrestID - the id of the arrest that we are innserting
	// @param $chargeID - the id of the charge that we are innserting
	public function writeExpungementDataToDatabase($arrestID, $defendantID, $attorneyID, $db)
	{
		$sql  = "INSERT INTO  expungement (`arrestID`, `defendantID`, `userid`, `isExpungement`, `isRedaction`, `isSummaryExpungement`, `timestamp`) VALUES ('$arrestID',  '$defendantID', '$attorneyID', '" . $this->isArrestExpungement() . "', '" . $this->isArrestRedaction() . "', '" . $this->isArrestSummaryExpungement ."', CURRENT_TIMESTAMP)";

		$result = mysql_query($sql, $db);
		if (!$result) 
		{
			if ($GLOBALS['debug'])
				die('Could not add the expungement to the DB:' . mysql_error());
			else
				die('Could not add the expungement to the DB');
		}
		return mysql_insert_id();
	
	}

	// @return none
	// @function updateExpungementWithNumCharges - updates the expungement table with the 
	// number of charges that were expungeable
	// @param $expungementID The id of the expungement that we want to update.  Will be "NULL" if there is no expungement to update.
	// @param $numRedactableCharges The number of charges that we want to redact.  Will be zero if there are no charges.
	// @param $db - the database handle
	public function updateExpungementWithNumCharges($expungementID, $numRedactableCharges, $db)
	{
		if ($expungementID != "NULL" && $numRedactableCharges > 0)
		{		
			$sql  = "UPDATE expungement SET numRedactableCharges=$numRedactableCharges WHERE expungementID=$expungementID";
			
			$result = mysql_query($sql, $db);
			if (!$result) 
			{
				if ($GLOBALS['debug'])
					die('Could not update the expungement with the number of arrests:' . mysql_error());
				else
					die('Could not update the expungement with the number of arrests.');
			}
		}
		return;
	}
	
}  // end class arrest

?>