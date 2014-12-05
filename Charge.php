<?php

/*************************************************
*
*	Charge.php
*	The class that describes a criminal charge and has helper functions for detarmining redactibility.
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
**************************************************************************/


class Charge
{
	private $chargeName;
	private $disposition;
	private $codeSection;
	private $dispDate;
	private $isRedactable;
	private $isSummaryRedactable;
	private $isARD;
	private $grade;
	
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
	public function setIsARD($isARD) { $this->isARD=$isARD; }
	public function setGrade($grade) { $this->grade=$grade; }

	public function getChargeName() { return $this->chargeName; }
	public function getDisposition() { return $this->disposition; }
	public function getCodeSection() { return $this->codeSection; }
	public function getDispDate() { return $this->dispDate; }
	public function getIsRedactable() { return $this->isRedactable; }
	public function getIsSummaryRedactable() { return $this->isSummaryRedactable; }
	public function getIsARD() { return $this->isARD; }
	public function getGrade() { if (!isset($this->grade) || $this->grade == "") $this->setGrade("unk"); return $this->grade; }
	
	public function isRedactable()
	{
		
		if (isset($this->isRedactable)) { return $this->getIsRedactable(); }
		$disp = $this->getDisposition();
		
		// "waived for court" appears on some MDJ cases.  It means the same as held for court.
		$nonRedactableDisps = array("Guilty" , "Guilty - Rule 1002", "Guilty Plea", "Guilty Plea - Negotiated", "Guilty Plea - Non-Negotiated", "Guilty Plea (Lower Court)", "Held for Court", "Waived for Court", "Held for Court (Lower Court)", "Nolo Contendere");
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
		
		$redactableDisps = array("Guilty" , "Guilty Plea", "Guilty Plea - Negotiated", "Guilty Plea - Non-Negotiated", "Guilty Plea (Lower Court)", "Nolo Contendere");
		if (in_array($disp, $redactableDisps))
			$this->setIsSummaryRedactable(TRUE);
		else
			$this->setIsSummaryRedactable(FALSE);
		return $this->getIsSummaryRedactable();
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