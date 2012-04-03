<?php

class Charge
{
	private $chargeName;
	private $disposition;
	private $codeSection;
	private $dispDate;
	private $isRedactable;
	
	public function __construct($chargeName, $disposition, $codeSection, $dispDate)
	{
		$this->setChargeName($chargeName);
		$this->setDisposition($disposition);
		$this->setCodeSection($codeSection);
		$this->setDispDate($dispDate);
	}
	
	public function setChargeName($chargeName) { $this->chargeName=$chargeName; }
	public function setDisposition($disposition) { $this->disposition=$disposition; }
	public function setCodeSection($codeSection) { $this->codeSection=$codeSection; }
	public function setDispDate($dispDate) { $this->dispDate=$dispDate; }
	public function setIsRedactable($isRedactable) { $this->isRedactable=$isRedactable; }

	public function getChargeName() { return $this->chargeName; }
	public function getDisposition() { return $this->disposition; }
	public function getCodeSection() { return $this->codeSection; }
	public function getDispDate() { return $this->dispDate; }
	public function getIsRedactable() { return $this->isRedactable; }
	
	public function isRedactable()
	{
		if (isset($this->isRedactable)) { return $this->getIsRedactable(); }
		$disp = $this->getDisposition();
		if ($disp== "Guilty" || $disp== "Guilty Plea" || $disp == "Held for Court")
			$this->setIsRedactable(FALSE);
		else
			$this->setIsRedactable(TRUE);
		return $this->getIsRedactable();
	}
	
}
?>