<?php
/*********************************
 * 
 * CPCMS.php
 * The main class for searching CPCMS and scraping the content from there.
 * Relies heavily on a casperjs script that Nate Vagel and Michael Hollander Wrote
 * 
 * Copyright 2011-2016 Community legal Services
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
 **********************************/

require_once("config.php");
require_once("ArrestSummary.php");

class CPCMS
{
    private $first;
    private $last;
    private $dob = null;
    private $results = array();
    private $bestSummaryDocketNumber;
    public static $docketURL = "https://ujsportal.pacourts.us/DocketSheets/CPReport.ashx?docketNumber=";
    public static $summaryURL = "https://ujsportal.pacourts.us/DocketSheets/CourtSummaryReport.ashx?docketNumber=";
    

    // expects that the date will come in YYYY-MM-DD formate, but CPCMS requires mm/dd/yyy format
    public function __construct($first, $last, $dob)
    {
        $this->first = htmlspecialchars(stripslashes($first));
        $this->last = htmlspecialchars(stripslashes($last));
    
        if (($time = strtotime($dob))!==false)
            $this->dob = date("m/d/Y", $time);
    }
    

    // searches CPCMS for the person matchine first, last, and DOB.  If no DOB is specified, returns
    // the first page of results from a search of just the first and last name.
    // Returns the sttaus code.
    public function cpcmsSearch()
    {
        $searchString = " --first=$this->first --last=$this->last";
    
        // if a DOB is specified, then search for it.  If not, only return the first page of results
        if (isset($this->dob))
          $searchString = $searchString . " --DOB=$this->dob";
        else
          $searchString = $searchString . " --limit";
    
        $command = $GLOBALS['casperjsCommand'] . " " . $GLOBALS['casperScript'] . $searchString;
        // print $command . "<br/>";
        exec($command, $results);
    
        foreach($results as $key=>$value)
        {
           $results[$key] = explode(" | ", $value);
        }
        $status = array_shift($results);
        $this->results = $results;
        return $status;
    }   

    // assumes a result array that looks like this:
    // [1..n]->Docket Number | Active/inactive | OTN | DOB
    //
    public function printResultsForWeb()
    {
        $this->sortResults();
        $summaryCase = $this->findBestSummaryDocketNumber();

        print "<a href='$this->summaryURL" . $summaryCase . "' target='_blank'>Summary Docket</a>";
        print "<table><th>Docket Number</th><th>Status</th><th>OTN</th><TH>DOB</TH>";
        foreach ($this->results as $result)
        {
            print "<tr>";
            print "<td><a href='$this->docketURL" . $result[0] . "' target='_blank'>$result[0]</a></td>";
            print "<td>$result[1]</td>";
            print "<td>$result[2]</td>";
            print "<td>$result[3]</td>";
            print "</tr>";
        }   
        print "</table>";

    }

    // assumes a result array that looks like this:                                                           
    // [1..n]->Docket Number | Active/inactive | OTN | DOB                                                    
    // will create a form that displays all of the dockets with some information about them
    // as well as hidden fields from all of the postVars passed in.  The form will submit
    // to the $postLocation
    public function displayAsWebForm($postLocation, $postVars)
    {                                                                                                         
        $this->sortResults();                                                                                 
        $summaryCase = $this->findBestSummaryDocketNumber();                                                  
        if (empty($this->dob))
          print "<b>Only showing the first page of results from CPCMS because no DOB specified</b>";
        // print out a form to resubmit your search query
        print "<form action='$postLocation' method='post'>";
        // print out all of the hidden form variables and a post button
        foreach ($postVars as $name=>$value)
        {
            print "<input type='hidden' name='$name' value='" . htmlspecialchars($value) . "' />";
        }
      ?>
            <div class="form-item">                                                                                                                                 
                <label for="personFirst">Client's Name</label>
                <div class="form-item-column">                                                                                                                      
                    <input type="text" name="personFirst" id="personFirst" class="form-text" value="<?php printIfSet('personFirst');?>" />
                </div>                                                                                                                                              
                <div class="form-item-column">                                                                                                                      
                    <input type="text" name="personLast" id="personLast" class="form-text" value="<?php printIfSet('personLast');?>" />                             
                </div>                                                                                                                                              
                <div class="space-line"></div>                                                                                                                      
                <div class="description">                                                                                                                           
                    <div class="form-item-column">                                                                                                                  
                        First Name                                                                                                                                  
                    </div>                                                                                                                                          
                    <div class="form-item-column">                                                                                                                  
                        Last Name                                                                                                                                   
                    </div>                                                                                                                                          
                </div>                                                                                                                                              
                <div class="space-line"></div>                                                                                                                      
            </div>                                                                                                                                                  
            <div class="form-item">                                                                                                                                 
                <label for="personDOB">Date of Birth</label>                                                                                                            
                <input type="date" name="personDOB" value="<?php printIfSet('personDOB');?>" maxlength="10"/>                                                       
                <div class="description">MM/DD/YYYY</div>                                                                                                           
            </div>              
            <input type="hidden" name="cpcmsSearch" value="true" />
            <div class="form-item">                                                                                                                                 
                <input type="submit" value="Redo CPCMS Search" />                                                                                                     
            </div>                                                                                                                                                  
        </form>
        <div class='space-line'>&nbsp;</div>
        <div class='boldLabel'>Docket Sheets downloaded from CPCMS</div>
        <div class='space-line'>&nbsp;</div>
        <div class='boldLabel'>
<?php
        print "<a href='". CPCMS::$summaryURL . $summaryCase . "' target='_blank'>Summary Docket</a>";
        print "</div>";
        print "<div class='space-line'>&nbsp;</div>";
        print "<form action='$postLocation' method='post'>";
        print "<table><th>&nbsp;</th><th>Docket Number</th><th>Status</th><th>OTN</th>";
        
        // only print the DOB field if we are viewing 
        if (empty($this->dob))
          print "<TH>DOB</TH>";
        print "<tr><td><input type='checkbox' id='checkAll' checked='checked'/></td><td>Check/Uncheck All</td></tr>";
        foreach ($this->results as $result)                                                                   
        {                                                                                                     
            print "<tr>";       
            print "<td><input type='checkbox' name='docket[]' value='$result[0]' checked='checked' class='checkItem' /></td>";
            print "<td><a href='" . CPCMS::$docketURL . $result[0] . "' target='_blank'>$result[0]</a></td>";     
            print "<td>$result[1]</td>";                                                                      
            print "<td>$result[2]</td>";
            
            // only print the DOB if we are looking at a general search
            if (empty($this->dob))
                print "<td>$result[3]</td>";                                                                      
            print "</tr>";                                                                                    
        }                                                                                                     
        print "</table>";
        
        // now print out all of the hidden form variables and a post button
        foreach ($postVars as $name=>$value)
        {
            print "<input type='hidden' name='$name' value='" . htmlspecialchars($value) . "' />";
        }
        
print "        
        <div class='form-item'>
        <input type='hidden' name='scrapedDockets' value='true'>
        <input type='submit' value='Expunge' />
        </div>
        <div class='form-item'>                                                                                                                                 
            <br />                                                                                                                                              
            <input type='checkbox' name='expungeRegardless' /> Expunge Regardless of whether the docket is actually expungable.  This should only be used in very rare circumstances, like when someone receives a pardon and you want to prepare an expungement petition for them despite the docket not appearing to be expungeable.
        </div>
        </form>
        <script type='text/javascript'>
        // this allows us to have a checkall/deselect all checkbox                                                    
        $('#checkAll').click(function () {                                                                            
            $(':checkbox.checkItem').prop('checked', this.checked);                                                   
        }); 
        </script>                                                                                                          
            
";
    }                        
    
    public function findBestSummaryDocketNumber()
    {
        if (isset($this->bestSummaryDocketNumber))
          return $this->bestSummaryDocketNumber;
        else $number = $this->findBestSummaryDocket($this->results);
        
        if ($number === 0)
            return $this->results[0][0];

        else
            return $number;
    }

    // finds the best case to use to look at a person's summary docket (a roll up of all of their dockets)
    // Older cases are generally not good for this (what is older?  Maybe I should just look for the most recent)
    // Closed cases are better.  Cases with a -CR- in them are better (criminal cases), SU cases second best
    // Helpfully, the results returned by CPCMS are generally already in the correct order.  
    // returns 0 if there are no docketes that fit the bill
    private function findBestSummaryDocket($aResults)
    {
        // default case--if there is only one docket number, return 0; this tells the
        // user of the function to use the first docket number since there are none that match our criteria
        // this also prevents an infinite recursion
        if (count($aResults) == 1)
            return 0;

        // if this is a closed case and it is criminal, return it
        if (preg_match("/Closed/", $aResults[0][1]) && preg_match("/\-CR\-/", $aResults[0][0]))
            return $aResults[0][0];

        // otherwise, remove the first element from the array (the one that we just rejected) and try again
        else
        {
            array_shift($aResults);
            // perform the same exercise on the remainig dockets
            return $this->findBestSummaryDocket($aResults);
        }
    }
            
    // Looks up the summary docket for one case and reads through it.  If there are any additional
    // cases to add to the array, adds them in with all of the relevant information.
    public function integrateSummaryInformation()
    {
        $docketNumber = $this->findBestSummaryDocketNumber();
        $summaryFile = $this->getDocket($docketNumber, true);
        
        $command = $GLOBALS['pdftotext'] . " -layout \"" . $summaryFile . "\" \"" . $GLOBALS['tempFile'] . "\"";
        system($command, $ret);
        //        print "<br>The pdftotext command: $command <BR />";

        if ($ret == 0)
        {
            $thisRecord = file($GLOBALS['tempFile']);
            $summary = new ArrestSummary();
            $summary->readArrestSummary($thisRecord);
            $sArrests = $summary->getArrests();
            
            // compare the arrests from the summary docket to the 
            // arrests already on this CPCMS object.  Add in any arrests
            // that weren't already there with a notation that they were found on the summary
            $thisArrests = $this->results;
            foreach ($sArrests as $arrest)
            {
                // each arrest could have multiple docket numbers
                foreach ($arrest->getDocketNumber() as $dn)
                {
                    $add = true;
                    // check each arrest against the arrests stored on this object
                    foreach ($thisArrests as $thisArrest)
                    {
                        // if we find a match between this docket number and a docket number
                        // in our results list, break and go on to the next docket numbe rin our list
                        if ($dn==$thisArrest[0])
                        {
                            $add = false;
                            break;
                        }
                    }

                    // if we never found a match, add this docket number to the list.
                    if ($add)
                        $this->results[] = array($dn, "From Summary", $arrest->getOTN(), "From Summary");
                }
            }
        }

    }

    // downloads a docketsheet from CPCMS.  If isSummary is true, will download a summary
    // docketSheet based on the docketnumber given; otherwise gets a regular docket sheet
    public static function getDocket($docketNumber, $isSummary)
    {
        $url;
        if ($isSummary) $url = CPCMS::$summaryURL . $docketNumber;
        else $url = CPCMS::$docketURL . $docketNumber;
        
        $ch = CPCMS::initConnection();
        curl_setopt($ch, CURLOPT_URL, $url);
        
        $filename = $GLOBALS['dataDir'] . $docketNumber;
        if ($isSummary) $filename = $filename . "Summary";
        $filename = $filename . ".pdf";
        $fp = fopen($filename, "w");
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_exec($ch);
        fclose($fp);
        curl_close($ch);
        
        // return the filename
        return $filename;
        
    }
      
      
    // initializes a connection for CURL
    public static function initConnection()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, FALSE);
        return $ch;
    }
    

    // Sort the $results member by year of case, with the most recent cases at the start of the array
    private function sortResults()
    {
        usort($this->results, function($a,$b) {                                                                           
           return substr($b[0],-4) - substr($a[0],-4);                                                             
        });
    }

    // takes an array of docket numbers and downloads all of the docketsheets
    // including the summary docket.  If a file already exists in the $data dir, 
    // will just use that and not redownload.
    // returns an associative array like the $_FILES array.  It will have the following fields:
    // $files['userFile']['tmp_name'][] which is the path to the file
    // $files['userFile']['size'][] which is the size of the file
    // $files['userFile']['name'][] which is the name of the file (the docket number is this case
    public static function downloadDockets($dockets)
    {
        $files = array();
        
        // sort the dockets in reverse cron order
        usort ($dockets, function($a,$b) {
            return substr($b, -4) - substr($a ,-4);
        });
        
        foreach ($dockets as $dn)
        {
            // first check to see if there is already a docket downloaded with this number
            $thisFile = $GLOBALS['dataDir'] . $dn . ".pdf";
            if (!(file_exists($thisFile) && filesize($thisFile) > 0))
                $thisFile = CPCMS::getDocket($dn, false);
            $files['userFile']['tmp_name'][] = $thisFile;
            $files['userFile']['size'][] = filesize($thisFile);
            $files['userFile']['name'][] = $dn . ".pdf";
        }
        
        // download the summary docket as well
        $bestSummary = $dockets[0];
        // check each of the docket numbers to see if it is better than dns[0], the first on the list
        foreach ($dockets as $dn)
        {
            if (preg_match("/-CR-/", $dn))
            {
                // if we found a CR docket, then use that instead of whatever is already in bestSummary
                $bestSummary = $dn;
                break;
            }
        }

        // now download the summary
        // first check to see if there is already a docket downloaded with this number                    
        $thisFile = $GLOBALS['dataDir'] . "Summary" . $bestSummary . ".pdf";
        if (!(file_exists($thisFile) && filesize($thisFile) > 0))                                         
            $thisFile = CPCMS::getDocket($bestSummary, true);
        $files['userFile']['tmp_name'][] = $thisFile;                                                     
        $files['userFile']['size'][] = filesize($thisFile);                                               
        $files['userFile']['name'][] = "SummaryDocket.pdf";

        return $files;
    }
}                 

?>

