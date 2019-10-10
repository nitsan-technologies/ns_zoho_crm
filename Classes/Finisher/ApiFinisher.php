<?php
namespace Nitsan\NsZohoCrm\Finisher;

use In2code\Powermail\Finisher\AbstractFinisher;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility as Debug;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Alex Kellner <alexander.kellner@in2code.de>, in2code.de
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Class ApiFinisher 
 * 
 * @package Nitsan/NsZohoCrm/Finisher
*/
class ApiFinisher extends AbstractFinisher
{
    /**
     * getFinisher
     *
     * @return void
     */
    public function getFinisher()
    {
        // get configuration from extension manager        
        $constant = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_nszohocrm.']['settings.'];
        $auth = $constant['authtoken'];
        
        // get subject from mail
        $subject = $this->getMail()->getSubject();
        $settings  = $this->getSettings();        

        if((int)$GLOBALS['TYPO3_CONF_VARS']['SYS']['compat_version'] < 7){
            //version 6
            $fields = $this->getMail()->getAnswers();
            $avilablefileds=array();
            foreach ($fields as $key => $value) {
                $avilablefileds[$value->getField()->geTtitle()]= $value->getValue();
            }
        }else{
            // version 7 and 8 
            $fields = $this->getMail()->getAnswersByFieldMarker();
            $avilablefileds=array();
            foreach ($fields as $key => $value) {
                $avilablefileds[$key]= $this->getMail()->getAnswersByFieldMarker()[$key]->getValue();    
            }
        }

        // postData to CRM module
        $result = $this->postData($auth, $avilablefileds);

        // get recordId from CRM Module
        $xmlpath = simplexml_load_string($result);
        $json  = json_encode($xmlpath);        
        $configData = json_decode($json, true);
        $recordId = $configData['result']['recorddetail']['FL']['0'];

        // UploadFiles to CRM Module        
        $file = $this->uploadFile($auth,$recordId,$avilablefileds[$constant["attachment"]],'uploadFile');
        $profilephoto = $this->uploadFile($auth,$recordId,$avilablefileds[$constant["profilephoto"]],'uploadPhoto');        
    }

    /**
     * postData to CRM Module
     *
     * @return void
     */
    public function postData($auth,$avilablefileds)
    {
        $constant = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_nszohocrm.']['settings.'];
        $xml = 
            '<?xml version="1.0" encoding="UTF-8"?>
            <Leads>
                <row no="1">
                    <FL val="Lead Status">New Lead</FL>
                    <FL val="First Name">'.$avilablefileds[$constant["firstname"]].'</FL>
                    <FL val="Last Name">'.$avilablefileds[$constant["lastname"]].'</FL>
                    <FL val="Email">'.$avilablefileds[$constant["email"]].'</FL>
                    <FL val="Description">'.$avilablefileds[$constant["description"]].'</FL>
                    <FL val="Company">'.$avilablefileds[$constant["company"]].'</FL>                    
                    <FL val="Lead Source">'.$avilablefileds[$constant["leadsource"]].'</FL>
                    <FL val="Phone">'.$avilablefileds[$constant["phone"]].'</FL>
                    <FL val="Website">'.$avilablefileds[$constant["website"]].'</FL>
                    <FL val="Title">'.$avilablefileds[$constant["title"]].'</FL>
                    <FL val="Mobile">'.$avilablefileds[$constant["mobile"]].'</FL>
                    <FL val="Fax">'.$avilablefileds[$constant["fax"]].'</FL>
                    <FL val="Industry">'.$avilablefileds[$constant["industry"]].'</FL>
                    <FL val="Annual Revenue">'.$avilablefileds[$constant["annualrevenue"]].'</FL>
                    <FL val="No of Employees">'.$avilablefileds[$constant["noofemployees"]].'</FL>
                    <FL val="Email Opt Out">'.$avilablefileds[$constant["emailoptout"]].'</FL>
                    <FL val="Rating">'.$avilablefileds[$constant["rating"]].'</FL>
                    <FL val="Skype ID">'.$avilablefileds[$constant["skypeid"]].'</FL>
                    <FL val="Twitter">'.$avilablefileds[$constant["twitter"]].'</FL>
                    <FL val="Secondary Email">'.$avilablefileds[$constant["secondaryemail"]].'</FL>
                    <FL val="Street">'.$avilablefileds[$constant["street"]].'</FL>
                    <FL val="City">'.$avilablefileds[$constant["city"]].'</FL>
                    <FL val="State">'.$avilablefileds[$constant["state"]].'</FL>
                    <FL val="Zip Code">'.$avilablefileds[$constant["zipcode"]].'</FL>
                    <FL val="Country">'.$avilablefileds[$constant["country"]].'</FL>
                </row>
            </Leads>';

        $url ="https://crm.zoho.com/crm/private/xml/Leads/insertRecords";
        $query="authtoken=".$auth."&scope=crmapi&newFormat=1&xmlData=".$xml;

        // curl configuration for postData
        $response = $this->getCurl($url,$query);        
        return $response;
    }

    /**
     * uploadFiles to CRM Module
     *
     * @return void
     */
    public function uploadFile($auth,$recordId,$sendyourdetail,$fieldname)
    {
        // Upload file to CRM module
        $target_dir = '/uploads/tx_powermail/';
        $filename = $_SERVER['DOCUMENT_ROOT'] . dirname($_SERVER['SCRIPT_NAME']) . $target_dir . $sendyourdetail['0'];
        
        if(function_exists('curl_file_create')){
            $cfile = curl_file_create($filename,'',basename($filename));
        }

        // Path for uploadFiles to CRM module
        $url = "https://crm.zoho.com/crm/private/json/Leads/".$fieldname."?authtoken=".$auth."&scope=crmapi";        
        $query = array("id" => $recordId,"content" => $cfile);
        
        // curl configuration for uploadFiles 
        $response = $this->getCurl($url,$query);
        return $response;
    }
    
    public function getCurl($url,$query)
    {
        $ch = curl_init();
        /* set url to send post request */
        curl_setopt($ch, CURLOPT_URL, $url);
        /* allow redirects */
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        /* return a response into a variable */
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        /* times out after 30s */
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        /* set POST method */
        curl_setopt($ch, CURLOPT_POST, 1);
        /* add POST fields parameters */
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);// Set the request as a POST FIELD for curl.

        //Execute cUrl session
        $response = curl_exec($ch);

        curl_close($ch);
        return $response;
    }        

}