<?php

/*************************************************
*
*	Record.php
*	The class that describes an entire criminal record.  It is a collection of
* arrests with a number of functions that help do things to/on those arrests.
*
*	Copyright 2011-2018 Community Legal Services
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
************************************************************/

require_once("Arrest.php");
require_once("Person.php");
require_once("ArrestSummary.php");

class Record
{
    private $arrests = array();
    private $arrestSummary;
    private $person;
    private $cleanSlateEligible = array();

    // public function __construct($arrests)
    // {
    //     $this->arrests=$arrests;
    // }

    public function __construct($person)
    {
        $this->person = $person;//do nothing
        $this->arrestSummary = new ArrestSummary();
    }

    public function getArrests() { return $this->arrests; }
    public function getPerson() { return $this->person; }

    public function addArrest($arrest)
    {
        $this->arrests[$arrest->getFirstDocketNumber()] = $arrest;
    }

    public function getTotalArrests()
    {
        return count($this->arrests);
    }

    // parse the docket sheets into Arrest objects and place them all into the
    // $arrests arrays
    public function parseDockets($tempFile, $pdftotext, $docketFiles)
    {
        // loop over all of the files that we uploaded and read them in to see if they are expungeable
        foreach($docketFiles["userFile"]["tmp_name"] as $key => $file)
        {
            $command = $pdftotext . " -layout \"" . $file . "\" \"" . $tempFile . "\"";
            //print $command;
            system($command, $ret);

            # print "<br>The pdftotext command: $command <BR />";

            if ($ret == 0)
            {
                # print $filename . "<br />";
                $thisRecord = file($tempFile);

                $arrest = new Arrest();

                if ($arrest->isDocketSheet($thisRecord[1]) || $arrest->checkIsJuvenilePhilly($thisRecord[0]))
                {
                    // if this is a regular docket sheet, use the regular parsing function
                    $arrest->readArrestRecord($thisRecord, $this->person);

                    // associate the PDF with the arrest for later saving to the DB
                    // associate the real PDF file name with the arrest as well for use in the overview
                    if ($docketFiles["userFile"]["size"][$key] > 0)
                    {
                        $arrest->setPDFFile($file);
                        $arrest->setPDFFileName($docketFiles["userFile"]["name"][$key]);
                    }

                    // now add the arrest to the arrests array
                    if ($arrest->isArrestCriminal())
                        $this->addArrest($arrest);

                }
                elseif (ArrestSummary::isArrestSummary($thisRecord))
                {
                    // if this is a summary sheet of all arrests, make a separate array
                    $this->arrestSummary->processArrestSummary($thisRecord);
                }
            }
        }
        try
        {
            unlink($tempFile);
        }
        catch (Exception $e) {}

    }

    // integrates information from the summaryArrest object and the arrests array.  The summary
    // arrest object most commonly contains additional information about the judge, but could
    // have other useful information like the OTN or DC number.
    // also integrates information into the Person object if a PPID and SID exist
    // Includes an optional $isAPI, default False. The function won't print to the screen if
    // $isAPI is True.
    public function integrateSummaryInformation($isAPI=False)
    {
        if ($this->arrestSummary != null && $this->arrestSummary->hasValuableInformation())
        {
            // integrate arrests together
            foreach ($this->arrests as $arrest)
            {
                // grab the docket number off of the arrest
                $docket = $arrest->getFirstDocketNumber();

                // and combine with the like summary, if one exists
                if ($this->arrestSummary->isArrestInSummary($docket))
                $arrest->combineWithSummary($this->arrestSummary->getArrest($docket));
            }

            // warn the user about any cases that are in the summary, but that were not uploaded
            $summaryKeys = $this->arrestSummary->getArrestKeys();
            $arrestKeys = array_keys($this->arrests);
            $missingDockets = array_diff($summaryKeys, $arrestKeys);

            // this shoudl probably be moved elsewhere since it is printing to screen
            if ( (count($missingDockets) > 0) && ($isAPI==False) )
            {
                print "<b>The following cases appear in the summary docket, but you didn't upload a corresponding docket sheet:</b><br/>";
                foreach ($missingDockets as $missingDocket)
                print "$missingDocket<br/>";
                print "<br/>";

            }
        }

        // integrate the DOB from the arrests into the person
        foreach ($this->arrests as $arrest)
        {
            $DOB = $arrest->getDOB();
            if($DOB != null and $DOB != "")
            {
                $this->person->setDOB($DOB);
                return;
            }
        }

    }

    // takes an array of Arrests and determines which ones are part and parcel of the same case.
    // @return the array of Arrests, pared down.
    public function combineArrests()
    {
        // start by comparing the arrests and combining the ones with matching OTNS or DC numbers
        foreach ($this->arrests as $key=>$arrest)
        {
            $innerArrests = $this->arrests;
            foreach ($innerArrests as $innerKey=>$innerArrest)
            {
                if($arrest->combine($innerArrest))
                {
                    print "combining " . $arrest->getFirstDocketNumber() . " | " . $innerArrest->getFirstDocketNumber() . "<br />";
                    unset($this->arrests[$innerKey]);
                }
            }
        }
        // reindex the arrests array now that some entries have been removed
        $this->arrests = array_values($this->arrests);
    }

    // checks if anything is sealable by running through each case and checking if that case is sealable
    // Not sealable if any case is not sealable or if there are 4 or more convictions on this record
    // Returns true or false.  If true, still need to check later to get the reasons something may not
    // be sealable (this is for isSealable > 1).
    public function checkIfSealable()
    {
        $sealable = 1;
        $convictions = 0;
        foreach ($this->arrests as $arrest)
        {
            $sealable = $sealable * $arrest->isArrestSealable();
            if ($sealable == 0)
            break;

            // if this is a conviction on a non-summary case, we need to increment convictions
            if ($arrest->isArrestConviction() && !$arrest->getIsSummaryArrest())
            {
                $convictions++;
                // if there are more than 4 convictions, then we can't seal
                if ($convictions > 3)
                {
                    $sealable = 0;
                    break;
                }
            }
        }
        $this->sealable = $sealable;
        return $sealable;
    }

    public function parseArrests() {
        // Similar to createOverview, but without the microsoft word
        $results = Array();
        //print("\nParsing arrests.\n");
        //print_r($arrests);
        //print("\n Size of arrests: ");
        //print(sizeof($arrests));
        //print("\n");
        if (sizeof($this->arrests) == 0) {
            $results['expungements_redactions'] = ["none"];
        } else {
            $results['expungements_redactions'] = Array();
            $results['sealing'] = Array();
            foreach($this->arrests as $arrest) {

                $thisArrest = Array();

                $thisArrest['docket'] = htmlspecialchars(implode(", ", $arrest->getDocketNumber()), ENT_COMPAT, 'UTF-8');
                $thisArrest['otn'] = htmlspecialchars($arrest->getOTN(), ENT_COMPAT, 'UTF-8');

                $expType = "No expungement possible";
                if ($arrest->isArrestRedaction()) {
                    $expType = "Partial Expungement";
                }
                if ($arrest->isArrestExpungement()) {
                    $expType = "Expungement";
                }
                if ($arrest->isArrestARDExpungement()) {
                    $expType = "ARD Expungement***";
                }
                if ($arrest->isArrestSummaryExpungement($this->arrests)) {
                    $expType = "Summary Expungement";
                }
                if ($arrest->isArrestOver70Expungement($this->arrests, $this->person)) {
                    $expType = "Expungement (over 70)";
                }
                // Ignoring act 5 sealing for now
                $thisArrest['expungement_type'] = $expType;
                $thisArrest['unpaid_costs'] = htmlspecialchars(number_format($arrest->getCostsTotal() - $arrest->getBailTotal(),2),ENT_COMPAT, 'UTF-8');
                $thisArrest['bail'] = htmlspecialchars(number_format($arrest->getBailTotalTotal(),2), ENT_COMPAT, 'UTF-8');
                $results['expungements_redactions'][] = $thisArrest;
                if ($arrest->isArrestSealable()>0) {
                    //then iterate over all the charges
                    foreach ($arrest->getCharges() as $charge) {
                        $thisCharge = Array();
                        // check if the charge is a conviction and if it is sealable (non conviction charges get a 1)
                        if ( $charge->isConviction() && ($charge->isSealable() >0) ) {
                            $thisCharge['case_number'] = htmlspecialchars($arrest->getFirstDocketNumber(), ENT_COMPAT, 'UTF-8');
                            $thisCharge['charge_name'] = htmlspecialchars($charge->getChargeName(), ENT_COMPAT, 'UTF-8');
                            $thisCharge['code_section'] = htmlspecialchars($charge->getCodeSection(), ENT_COMPAT, 'UTF-8');
                            if ($charge->isSealable()==1) {
                                $thisCharge['sealable'] = "Yes";
                            } else {
                                $thisCharge['sealable'] = "No";
                            }
                            $thisCharge['additional_information'] = htmlspecialchars($charge->getSealablePercent(), ENT_COMPAT, 'UTF-8');
                            $results['sealing'][] = $thisCharge;
                        } // end processing if a charge is a conviction that is sealable
                    } //end loop over charges for an arrest
                } // end of checking if arrest is sealable

            }//end of processing arrests

        }// end of processing results
        //error_log("Returning response:");
        //error_log("-----------");
        return $results;
    }//end of parseArrests

    /** Right now this just checks to see if a case is Act 56 eligible.  In them
    * future, this might also include other types of eligibility, which might
    * mean that there should be other functions insie: checkCleanSlate, checkAc72, etc...
    * This runs throgh each of the arrests and checks to see if each is eligible
    * for clean slate or otherwize triggers some sort of diqualification from
    * clean slate.
    * @Return void.  It just sets some variables and then those can be checked
    * later.
    *********************************/
    public function checkSealingEligibility()
    {
        $this->checkCleanSlateMurderFelony();
        $this->checkCleanSlatePast10MFConviction();
        $this->checkCleanSlatePast15MoreThanOneM1F();
        $this->checkCleanSlatePast15ProhibitedConviction();
        $this->checkCleanSlatePast20MoreThanThreeM2M1F();
        $this->checkCleanSlatePast20FProhibitedConviction();

        foreach ($this->arrests as $arrest)
        {
            // $arrest->hasOutstandingFinesCosts();

        }
    }

    public static function not_empty($var)
    {
        return array_filter($var);
    }

    public static function test_not_empty($var)
    {
        print("<pre>a");
        print_r($var);
        print("a</pre>");
        return !empty($var);
    }

    public function checkCleanSlateMurderFelony()
    {
        // if we have already gone through this before, just exit
        if (isset($this->cleanSlateEligible['MurderF1']['answer']))
            return;

        foreach ($this->arrests as $arrest)
        {
            // check whether there are any murder/felony convictions on each
            // and return true if there are
            $this->cleanSlateEligible['MurderF1'][$arrest->getFirstDocketNumber()] = $arrest->checkCleanSlateMurderFelony();
        }
        // if we have inserted elements into the array, that means that there are
        // potential F1 convictions.  TO check if there are elements in the array
        // see if there are any non-null nodes in the array
        if (array_filter($this->cleanSlateEligible['MurderF1'], array(__CLASS__, 'not_empty')))
        {
            $this->cleanSlateEligible['MurderF1']['answer'] = FALSE;
        }
        else
            $this->cleanSlateEligible['MurderF1']['answer'] = TRUE;
    }

    public function checkCleanSlatePast10MFConviction()
    {
        // if we have already gone through this before, just exit
        if (isset($this->cleanSlateEligible['Past10MFConviction']['answer']))
            return;

        foreach ($this->arrests as $arrest)
        {
            // check whether there are any murder/felony convictions on each
            // and return true if there are
            $this->cleanSlateEligible['Past10MFConviction'][$arrest->getFirstDocketNumber()] = $arrest->checkCleanSlatePast10MFConviction();
        }
        // if we have inserted elements into the array, that means that there are
        // potential F1 convictions.  TO check if there are elements in the array
        // see if there are any non-null nodes in the array
        if (array_filter($this->cleanSlateEligible['Past10MFConviction'], array(__CLASS__, 'not_empty')))
            $this->cleanSlateEligible['Past10MFConviction']['answer'] = FALSE;
        else
            $this->cleanSlateEligible['Past10MFConviction']['answer'] = TRUE;

    }

    public function checkCleanSlatePast15MoreThanOneM1F()
    {
        // if we have already gone through this before, just exit
        if (isset($this->cleanSlateEligible['Past15MoreThanOneM1F']['answer']))
            return;

        foreach ($this->arrests as $arrest)
        {
            // check whether there are any murder/felony convictions on each
            // and return true if there are
            $this->cleanSlateEligible['Past15MoreThanOneM1F'][$arrest->getFirstDocketNumber()] = $arrest->checkCleanSlatePast15MoreThanOneM1F();
        }

        // if we have inserted elements into the array, that means that there are
        // potential M1 and F convictions.  Filter the array for non-null
        // elements and then count the number of these elements. For the
        $M1Fs = array_map('array_filter', $this->cleanSlateEligible['Past15MoreThanOneM1F']);

        // array_map with count will create an array where the keys are the case
        // numbers and the values are the counts of charges within the case
        // numbers.
        // array_sum then sums up all of those case numbers
        // IMPORTANT: if you want to count the total cases represented in the
        // array rather than the total charges, then change the line below to
        // if (count($MF1s) >1).
        // IMPORTANT #2: If you count this same array after true/false
        // has been set, you have to subtract 1 from the count($MF1s) as
        // ['answer'] will also get added into the count
        if (array_sum(array_map("count", $M1Fs)) > 1)
        {
            $this->cleanSlateEligible['Past15MoreThanOneM1F']['answer'] = FALSE;
        }
        else
            $this->cleanSlateEligible['Past15MoreThanOneM1F']['answer'] = TRUE;
    }


    public function checkCleanSlatePast15ProhibitedConviction()
    {
        // if we have already gone through this before, just exit
        if (isset($this->cleanSlateEligible['Past15ProhibitedConviction']['answer']))
            return;

        foreach ($this->arrests as $arrest)
        {
            // check whether there are any murder/felony convictions on each
            // and return true if there are
            $this->cleanSlateEligible['Past15ProhibitedConviction'][$arrest->getFirstDocketNumber()] = $arrest->checkCleanSlatePast15ProhibitedConviction();
        }
        // if we have inserted elements into the array, that means that there are
        // potential F1 convictions.  TO check if there are elements in the array
        // see if there are any non-null nodes in the array
        if (array_filter($this->cleanSlateEligible['Past15ProhibitedConviction'], array(__CLASS__, 'not_empty')))
        {
            $this->cleanSlateEligible['Past15ProhibitedConviction']['answer'] = FALSE;
        }
        else
            $this->cleanSlateEligible['Past15ProhibitedConviction']['answer'] = TRUE;

    }

    public function checkCleanSlatePast20MoreThanThreeM2M1F()
    {
        // if we have already gone through this before, just exit
        if (isset($this->cleanSlateEligible['Past20MoreThanThreeM2M1F']['answer']))
            return;

        foreach ($this->arrests as $arrest)
        {
            // check whether there are any murder/felony convictions on each
            // and return true if there are
            $this->cleanSlateEligible['Past20MoreThanThreeM2M1F'][$arrest->getFirstDocketNumber()] = $arrest->checkCleanSlatePast20MoreThanThreeM2M1F();
        }

        // if we have inserted elements into the array, that means that there are
        // potential M1 and F convictions.  Filter the array for non-null
        // elements and then count the number of these elements. For the
        $M2M1Fs = array_map('array_filter', $this->cleanSlateEligible['Past20MoreThanThreeM2M1F']);

        // array_map with count will create an array where the keys are the case
        // numbers and the values are the counts of charges within the case
        // numbers.
        // array_sum then sums up all of those case numbers
        // IMPORTANT: if you want to count the total cases represented in the
        // array rather than the total charges, then change the line below to
        // if (count($MF1s) >1).
        // IMPORTANT #2: I THINK! If you count this same array after true/false
        // has been set, you have to subtract 1 from the count($MF1s) as
        // ['answer'] will also get added into the count
        if (array_sum(array_map("count", $M2M1Fs)) > 3)
        {
            $this->cleanSlateEligible['Past20MoreThanThreeM2M1F']['answer'] = FALSE;
        }
        else
            $this->cleanSlateEligible['Past20MoreThanThreeM2M1F']['answer'] = TRUE;

    }

    public function checkCleanSlatePast20FProhibitedConviction()
    {
        // if we have already gone through this before, just exit
        if (isset($this->cleanSlateEligible['Past20FProhibitedConviction']['answer']))
            return;

        foreach ($this->arrests as $arrest)
        {
            // check whether there are any murder/felony convictions on each
            // and return true if there are
            $this->cleanSlateEligible['Past20FProhibitedConviction'][$arrest->getFirstDocketNumber()] = $arrest->checkCleanSlatePast20FProhibitedConviction();
        }
        // if we have inserted elements into the array, that means that there are
        // potential F1 convictions.  TO check if there are elements in the array
        // see if there are any non-null nodes in the array
        if (array_filter($this->cleanSlateEligible['Past20FProhibitedConviction'], array(__CLASS__, 'not_empty')))
        {
            $this->cleanSlateEligible['Past20FProhibitedConviction']['answer'] = FALSE;
        }
        else
            $this->cleanSlateEligible['Past20FProhibitedConviction']['answer'] = TRUE;

        print "<pre>";
        print_r($this->cleanSlateEligible['Past20FProhibitedConviction']);
        print "</pre>";

    }
}
