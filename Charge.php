<?php

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
		if ($disp== "Guilty" || $disp== "Guilty Plea" || $disp== "Guilty Plea - Negotiated" || $disp== "Guilty Plea - Non-Negotiated"|| $disp == "Held for Court" || $disp == "Waived for Court")
			$this->setIsRedactable(FALSE);
		else
			$this->setIsRedactable(TRUE);
		return $this->getIsRedactable();
	}

	public function isSummaryRedactable()
	{
		if (isset($this->isSummaryRedactable)) { return $this->getIsSummaryRedactable(); }
		$disp = $this->getDisposition();
		if ($disp== "Guilty" || $disp== "Guilty Plea" || $disp== "Guilty Plea - Negotiated")
			$this->setIsSummaryRedactable(TRUE);
		else
			$this->setIsSummaryRedactable(FALSE);
		return $this->getIsSummaryRedactable();
	}

	public function isARD()
	{
		if (isset($this->isARD)) { return $this->getIsARD(); }
		$disp = $this->getDisposition();
		if ($disp == "ARD" || $disp == "ARD - County" || $disp == "ARD - County Open")
			$this->setIsARD(TRUE);
		else
			$this->setIsARD(FALSE);
		return $this->getIsARD();
	}
	
}
?>