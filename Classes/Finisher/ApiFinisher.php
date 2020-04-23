<?php
namespace Nitsan\NsZohoCrm\Finisher;

use In2code\Powermail\Finisher\AbstractFinisher;

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
        $settings = $this->getSettings();

        if ((int) $GLOBALS['TYPO3_CONF_VARS']['SYS']['compat_version'] < 7) {
            //version 6
            $fields = $this->getMail()->getAnswers();
            $avilablefileds = array();
            foreach ($fields as $key => $value) {
                $avilablefileds[$value->getField()->geTtitle()] = $value->getValue();
            }
        } else {
            // version 7 and 8
            $fields = $this->getMail()->getAnswersByFieldMarker();
            $avilablefileds = array();
            foreach ($fields as $key => $value) {
                $avilablefileds[$key] = $this->getMail()->getAnswersByFieldMarker()[$key]->getValue();
            }
        }
        // postData to CRM module
        $result = $this->postData($auth, $avilablefileds);

        $result2 = json_decode($result);
        $data = get_object_vars($result2);
        $data2 = get_object_vars($data['data'][0]);
        $data3 = get_object_vars($data2['details']);
        $recordId = $data3['id'];

        // UploadFiles to CRM Module
        $file = $this->uploadFile('Attachments', $auth, $recordId, $avilablefileds[$constant["attachment"]]);
        $profilephoto = $this->uploadFile('photo', $auth, $recordId, $avilablefileds[$constant["profilephoto"]]);
    }

    /**
     * postData to CRM Module
     *
     * @return void
     */
    public function postData($auth, $avilablefileds)
    {
        $constant = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_nszohocrm.']['settings.'];
        $url = "https://www.zohoapis.in/crm/v2/Leads";
        $json = '{
                "data":[
                {
                    "First_Name":"' . $avilablefileds[$constant["firstname"]] . '",
                    "Last_Name":"' . $avilablefileds[$constant["lastname"]] . '",
                    "Email":"' . $avilablefileds[$constant["email"]] . '",
                    "Description":"' . $avilablefileds[$constant["description"]] . '",
                    "Company":"' . $avilablefileds[$constant["company"]] . '",
                    "Lead_Source":"' . $avilablefileds[$constant["leadsource"]] . '",
                    "Phone":"' . $avilablefileds[$constant["phone"]] . '",
                    "Website":"' . $avilablefileds[$constant["website"]] . '",
                    "Designation":"' . $avilablefileds[$constant["title"]] . '",
                    "Mobile":"' . $avilablefileds[$constant["mobile"]] . '",
                    "Fax":"' . $avilablefileds[$constant["fax"]] . '",
                    "Industry":"' . $avilablefileds[$constant["industry"]] . '",
                    "Annual_Revenue":"' . $avilablefileds[$constant["annualrevenue"]] . '",
                    "No_of_Employees":"' . $avilablefileds[$constant["noofemployees"]] . '",
                    "Email_Opt_Out":"' . $avilablefileds[$constant["emailoptout"]] . '",
                    "Rating":"' . $avilablefileds[$constant["rating"]] . '",
                    "Skype_ID":"' . $avilablefileds[$constant["skypeid"]] . '",
                    "Twitter":"' . $avilablefileds[$constant["twitter"]] . '",
                    "Secondary_Email":"' . $avilablefileds[$constant["secondaryemail"]] . '",
                    "Street":"' . $avilablefileds[$constant["street"]] . '",
                    "City":"' . $avilablefileds[$constant["city"]] . '",
                    "State":"' . $avilablefileds[$constant["state"]] . '",
                    "Zip_Code":"' . $avilablefileds[$constant["zipcode"]] . '",
                    "Country":"' . $avilablefileds[$constant["country"]] . '"
                }
            ]
        }';

        // curl configuration for postData
        $response = $this->getCurl($url, $json, $auth, 'application/json');
        return $response;
    }

    /**
     * uploadFiles to CRM Module
     *
     * @return void
     */
    public function uploadFile($attachType, $auth, $recordId, $sendyourdetail)
    {

        // Upload file to CRM module
        $target_dir = '/uploads/tx_powermail/';
        $filename = $_SERVER['DOCUMENT_ROOT'] . dirname($_SERVER['SCRIPT_NAME']) . $target_dir . $sendyourdetail['0'];

        if (function_exists('curl_file_create')) {
            $cfile = curl_file_create($filename, '', basename($filename));
        }

        // Path for uploadFiles to CRM module
        $url = "https://www.zohoapis.in/crm/v2/Leads/" . $recordId . "/" . $attachType;
        $json = array("file" => $cfile);

        // curl configuration for uploadFiles
        $response = $this->getCurl($url, $json, $auth, 'multipart/form-data');
        return $response;
    }

    public function getCurl($url, $data, $auth, $contentType)
    {
        $ch = curl_init();
        /* set url to send post request */
        curl_setopt($ch, CURLOPT_URL, $url);
        /* allow redirects */
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        /* return a response into a variable */
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'Content-Type:' . $contentType,
            'Authorization: Zoho-oauthtoken ' . $auth, //Token required
        ));

        /* times out after 30s */
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        /* set POST method */
        curl_setopt($ch, CURLOPT_POST, 1);
        /* add POST fields parameters */
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data); // Set the request as a POST FIELD for curl.

        //Execute cUrl session
        $response = curl_exec($ch);

        curl_close($ch);
        return $response;
    }

}
