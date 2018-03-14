<?php

/*************************************************
*
*	Charge.php
*	The class that describes a criminal charge and has helper functions for detarmining redactibility.
*
*	Copyright 2011-2015 Community Legal Services
* 
* Licensed under the Apache License, Version 2.0 (the "License");
* you may not use this file ehexcept in compliance with the License.
* You may obtain a copy of the License at
*
*    http://www.apache.org/licenses/LICENSE-2.0

* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
 * 
 * TODO: Edga case to consider: CP-26-CR-0001501-2015: all charges are dismissed or held for court
 * so it is an expungement, but it appears as a sealable redaction, which is wrong.
*
**************************************************************************/


class Charge
{
	private $chargeName;
	private $disposition;
	private $codeSection;
	private $dispDate;
	private $isRedactable;
	private $isSummaryRedactable;
    // 0 if not sealable at all; 1 if definitely sealable (or redactable); 2 if maybe sealable
    // this allows us to multiply charges together to get a sealable score.  Because anything that isn't
    // sealable nullifies sealing of any charge anywhere,  you can multiple all charges together.
    // if the product is 0, that means at least one charge isn't sealable and you can't seal anything.
    // if the product is 1, that means that all charges are sealable (or redactable), and anything
    // greater than 1 means that there are some charges that may be sealable but may not be; no charges 
    // are not sealable
    private $isSealable;
    private $sealablePercent;
	private $isARD;
	private $grade;
    private static $excludedOffenses = array("2904", "2910", "7507.1", "1801", "2424", "2425", "6318", "6320", "1591", "2243", "2244", "2251", "2260", "2421", "3121", "3123", "3124.1", "3125", "2241", "2242", "2244");
    private static $excludedOffensesWithSubsection = array("2902" => "b",
                                                          "2903" => "b",
                                                          "3124.2" => "a",
                                                          "3126" => "A1",
                                                          "3126" => "1", 
                                                          "6301" => "A1ii", 
                                                          "6312" => "d", 
                                                          "3011" => "b", 
                                                          "3124.2" => "a.2",
                                                          "3126" => "a2", 
                                                          "3126" => "a3", 
                                                          "3126" => "a4", 
                                                          "3126" => "a5", 
                                                          "3126" => "a6", 
                                                          "3126" => "a8", 
                                                          "5901" => "b1", 
                                                          "5903" => "a3ii", 
                                                          "5903" => "a4ii", 
                                                          "5903" => "a5ii", 
                                                          "5903" => "a6", 
                                                          "6312" => "b", 
                                                          "6312" => "c", 
                                                          "2901" => "a1", 
                                                          "3122.1" => "b", 
                                                          "3124.2" => "a1", 
                                                          "3126" => "a7", 
                                                          "4302" => "b"); 
	
	public function __construct($chargeName, $disposition, $codeSection, $dispDate, $grade)
	{
		$this->setChargeName($chargeName);
		$this->setDisposition($disposition);
		$this->setCodeSection($codeSection);
		$this->setDispDate($dispDate);
		$this->setGrade($grade);
	}
	
	public function setChargeName($chargeName) { $this->chargeName=$chargeName; }
	public function setDisposition($disposition) { $this->disposition=$disposition; }
	public function setCodeSection($codeSection) { $this->codeSection=$codeSection; }
	public function setDispDate($dispDate) { $this->dispDate=$dispDate; }
	public function setIsRedactable($isRedactable) { $this->isRedactable=$isRedactable; }
	public function setIsSummaryRedactable($isRedactable) { $this->isSummaryRedactable=$isRedactable; }
	public function setIsSealable($isSealable) { $this->isSealable=$isSealable; }
    public function setSealablePercent($percent) { $this->sealablePercent = $percent;}
	public function setIsARD($isARD) { $this->isARD=$isARD; }
	public function setGrade($grade) { $this->grade=$grade; }

	public function getChargeName() { return $this->chargeName; }
	public function getDisposition() { return $this->disposition; }
	public function getCodeSection() { return $this->codeSection; }
	public function getDispDate() { return $this->dispDate; }
	public function getIsRedactable() { return $this->isRedactable; }
	public function getIsSummaryRedactable() { return $this->isSummaryRedactable; }
	public function getIsSealable() { return $this->isSealable; }
    public function getSealablePercent() { return $this->sealablePercent; }
	public function getIsARD() { return $this->isARD; }
	public function getGrade() { if (!isset($this->grade) || $this->grade == "") $this->setGrade("unk"); return $this->grade; }
	
	public function isRedactable()
	{
		
		if (isset($this->isRedactable)) { return $this->getIsRedactable(); }
		$disp = $this->getDisposition();
		
		// "waived for court" appears on some MDJ cases.  It means the same as held for court.
		$nonRedactableDisps = array("Guilty" , "Guilty - Rule 1002", "Guilty Plea", "Guilty Plea - Negotiated", "Guilty Plea - Non-Negotiated", "Guilty Plea (Lower Court)", "Guilty by Trial (Lower Court)", "Held for Court", "Waived for Court", "Waived for Court (Lower Court)", "Held for Court (Lower Court)", "Nolo Contendere", "Found in Contempt");
		if (in_array($disp, $nonRedactableDisps))
			$this->setIsRedactable(FALSE);
		else
			$this->setIsRedactable(TRUE);
			
		return $this->getIsRedactable();
	}

	public function isSummaryRedactable()
	{
		if (isset($this->isSummaryRedactable)) { return $this->getIsSummaryRedactable(); }
		$disp = $this->getDisposition();
		
		$redactableDisps = array("Guilty" , "Guilty - Rule 1002", "Guilty Plea", "Guilty Plea - Negotiated", "Guilty Plea - Non-Negotiated", "Guilty Plea (Lower Court)", "Nolo Contendere", "Found in Contempt");
		if (in_array($disp, $redactableDisps))
			$this->setIsSummaryRedactable(TRUE);
		else
			$this->setIsSummaryRedactable(FALSE);
		return $this->getIsSummaryRedactable();
	}

    public function isConviction()
    {
        if (in_array($this->getDisposition(), array("Guilty" , "Guilty - Rule 1002", "Guilty Plea", "Guilty Plea - Negotiated", "Guilty Plea - Non-Negotiated", "Guilty Plea (Lower Court)", "Nolo Contendere", "Found in Contempt")))
            return true;
        else
            return false;

    }
    
    public function isHeldForCourt()
    {
        if (in_array($this->getDisposition(), array("Held for Court", "Waived for Court", "Waived for Court (Lower Court)", "Held for Court (Lower Court)")))
          return true;
        else
          return false;
    }
    
    // checks whether a charge is sealable.
    // Returns 0 if it isn't sealable.  0 Would be returned if the charge is contained within a 
    // list of non-expungeable offenses, defined by Act 5 of 2016.  0 would also be returned if the 
    // charge is not an M, M3, or M2 (if we know the grade. 
    // Returns 1 if the charge is definitely sealable.  Definitely sealable offenses have a known grade
    // of M, M3, or M2, are not in the list of excluded offenses, are not redactable.  Returns 1 also
    // if the charge is redactable; that makes things easier when checking a slew of charges together.
    // Returns 2 if the charge may be redactable.  This will generally come up if the grade is unknown.
    // If the charge is 2, separately sets a field stating what percent of charges in this category
    // are sealable, based on past data.
	public function isSealable()
	{
		if (isset($this->isSealable)) { return $this->getIsSealable(); }
		
        // start with the assumption that something is sealable; change that if we figure out that it isn't
        $this->setIsSealable(1);
        // if this is redactable, just return 1 and pretend it is sealable as well.
        // if this isn't a conviction, then return 1 as well.  
        // When is something not a conviction but also not redactable?  When it is held for court
        if ($this->isRedactable() || !$this->isConviction()) { $this->setIsSealable(1); return 1; }

        // if this case falls into a series of non sealable case types, then return 0
        $codeSection = preg_split("/\x{A7}+/u", $this->getCodeSection(), -1, PREG_SPLIT_NO_EMPTY);
        if (count($codeSection) == 1)
        {
          // this means that therew as a problem splitting the codeSection, so return 2
          $this->setSealablePercent("? - unknown code section");
          $this->setIsSealable(2);
        }

        // if this is title 18 and we are in the list of excludable offenses, return 0
        elseif ((trim($codeSection[0])=="18") && in_array(trim($codeSection[1]), Charge::$excludedOffenses))
        {
           $this->setIsSealable(0); 
            return 0;
        }
       
        // similar to above, but with specific subsection, but only if there is a subsection listed
        elseif ((count($codeSection)>2) && ((trim($codeSection[0])=="18") && $this->inOffensesWithSubsection(trim($codeSection[1]), trim($codeSection[2]))))
        {
            $this->setIsSealable(0); 
            $this->setSealablePercent("This crime/subsection is one of the exclusionary crimes (but not simple assault)");
            return 0;
        }
        
        
        // we have to deal separately with simple assault (2701) b/c it is sometimes and sometimes not sealable
        elseif ((trim($codeSection[0])=="18") && (trim($codeSection[1])=="2701"))
        {
            // simple assault is hanlded different if it is an M3 or an M2.  M3s can be expunged.  M2 are 
            // exclusionary
            if ($this->getGrade()=="unk")
            {
                $this->setIsSealable(2);
                $this->setSealablePercent("Unlikely - ungraded simple assault is likely an M2");
            }
            elseif ($this->getGrade()=="M3")
              $this->setIsSealable(1);
            elseif ($this->getGrade()=="M2")
            {
              $this->setIsSealable(0);
              $this->setSealablePercent("M2 Simple Assault");
            }
            else
            {
              $this->setIsSealable(0);
              $this->setSealablePercent("Simple Assault other than M3");
            }
            return $this->getIsSealable();
        }
       
        // check the grade of the offense; if it is known, we know whether this is sealable.  If 
        // the grade is unknown, we have to look the crime up to get a sense as to whether it may be
        // sealble
        if (!in_array($this->getGrade(), array("M", "M3", "M2", "S", "unk")))
          $this->setIsSealable(0);
        elseif ($this->getGrade()=="unk")
        {
            // if sealable is 1, this turns it into 2; if it is 0, this keeps it at 0; if it is more than 2, 
            // it stays at more than 2
            $this->setIsSealable($this->getIsSealable() * 2);
          
            // now look in the database to find this particular code section and see whetherh or not
            // it is potential sealable
            //TODO
            if (count($codeSection) > 2)
              $sql = "SELECT Percent_w_Subsection as P FROM crimes_w_subsection WHERE GRADE in ('M', 'M3', 'M2', 'MS') AND title='".trim($codeSection[0])."' AND section='".trim($codeSection[1])."' AND subsection like '".trim($codeSection[2])."%'";
            else
              $sql = "SELECT Percent as P FROM crimes_wo_subsection WHERE Grade in ('M', 'M3', 'M2') AND title='".trim($codeSection[0])."' AND section='".trim($codeSection[1])."'";
            
            $result = $GLOBALS['chargeDB']->query($sql);
            if (!$result)
                $this->setSealablePercent("Unclear; there was an error querying the database $sql");
            
            else
            {
                $p = mysqli_fetch_assoc($result);
                $this->setSealablePercent($p['P'] . " of cases with this code section were sealable.");
            }

        }
            
            
        // check the age of the disposition; if it is 8-10 years, set this as a 2; if it is < 8 years,
        // set this as a 0;
        $dispDate = new DateTime($this->getDispDate());
        $today = new DateTime();
        $age = abs(dateDifference($today, $dispDate));
        if ($age < 8)
        {
            $this->setIsSealable(0);
            $this->setSealablePercent("The disposition date of this charge (" . $this->getDispDate() . ") is < 8 years old.");
        }
        elseif ($age < 10)
        {
            $this->setIsSealable($this->getIsSealable() * 2);
            $this->setSealablePercent("The disposition date of this charge (" . $this->getDispDate() . ") is between 8 and 10 years old");
        }

        return $this->getIsSealable();        


    }
     
    // returns true if the given section and subsection exist in the excludedOffensesWIthSubsection array
    private function inOffensesWithSubsection($section, $subsection)
    {
        // first check the section as a key
        if (!array_key_exists($section, Charge::$excludedOffensesWithSubsection))
            return false;
        // then check the subsection
        if (stipos(Charge::$excludedOffensesWithSubsection[$section], $subsection)===0)
          return true;
        else
          return false;
    }
            
    public function getSealablePercentFromDB()
    {
        // queries the database to see what percent of charges were sealable with this code section
	    if (isset($this->sealablePercent)) { return $this->sealablePercent; }
    }
    
    
	public function isARD()
	{
		if (isset($this->isARD)) { return $this->getIsARD(); }
		$disp = $this->getDisposition();
		$ardRedactableDisps = array("ARD" , "ARD - County", "ARD - County Open");
		if (in_array($disp, $ardRedactableDisps))
			$this->setIsARD(TRUE);
		else
			$this->setIsARD(FALSE);
		return $this->getIsARD();
	}
	
}
?>