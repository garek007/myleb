<?php
session_start();
header("Access-Control-Allow-Origin: *");

require('exacttarget_soap_client.php');
$wsdl = 'https://webservice.s4.exacttarget.com/etframework.wsdl';
$etusername = '';//Enter the username you use to login to Exact Target
$etpassword = '';//Enter the password you use to login to Exact Target

$lists = $_POST['lists'];
$sender = new StdClass();
$sender->name = $_SESSION['from_name'];
$sender->email = $_SESSION['from_email'];
$sender->send_date = $_POST['send_date'];
$sender->send_time = $_POST['time'];

$fulldatetime = $sender->send_date . ' ' . $sender->send_time;



$sender->timestamp = strtotime($fulldatetime);



$update = $_REQUEST['update'];
//if update, get html
if($update == true){
	ob_start();
	include 'corp_template_start.html';
	$htmlstart = ob_get_contents();
	ob_end_clean();
	
	ob_start();
	include 'corp_template_end.html';
	$htmlend = ob_get_contents();
	ob_end_clean();	
	
	
	$html = $htmlstart.$_REQUEST["html"].$htmlend;
	$et_id = $_REQUEST["et_id"];
	echo "this is an update";
}else{
	$folderID = $_POST['et_folder_input'];
}



//folders
//shareable content 338240
//exec report 343713


//include '00-Includes/functions.php';

try//try to connect
{
	$client = new ExactTargetSoapClient($wsdl, array('trace'=>1));
	$client->username = $etusername;
	$client->password = $etpassword;



	$email = new ExactTarget_Email();
	if($update != true){
	$email->Name = $project_name;
	}
	$email->Description = 'Email from My Little Email Builder';
	$email->HTMLBody = isset($html) ? $html : $_SESSION['html'];
	$email->Subject = isset($subject) ? $subject : $_SESSION['subject'];
	$email->EmailType = 'HTML';
	$email->IsHTMLPaste = 'false';
	
	//if this is a new email, specify folder
	if($update != true){
		$email->CategoryIDSpecified = true;
		$email->CategoryID = $folderID;//id of the folder to save in 343713
	}else{
		$email->ID = $et_id;		
	}
	//$email->ID = "811629";
	//$email->ClonedFromID = 325013;
	
	//$template = new ExactTarget_Template;
	//$template->CategoryID = 325013;
													 
	$object = new SoapVar($email, SOAP_ENC_OBJECT, 'Email', "http://exacttarget.com/wsdl/partnerAPI");
	
	//if it's an update, update, otherwise, create
	if($update == true){
		$request = new ExactTarget_UpdateRequest();
	}else{
		$request = new ExactTarget_CreateRequest();
	}
	$request->Options = NULL;
	$request->Objects = array($object);
	
	//if it's an update, update, otherwise, create
	if($update == true){
		$results = $client->Update($request);
		echo '<pre>';
		var_dump($results);
		echo '</pre>';
	}else{
		$results = $client->Create($request);
		$exacttargetID = retrieveEmail($client,$project_name);
	}



	
	//sendEmail($email,$client, $lists,$sender);
								
               		//echo "ID is ".$email->ID;
				//echo '<pre>';var_dump($results);echo '</pre>';
		





}catch (SoapFault $e) {
	/* output the resulting SoapFault upon an error */
	echo "Error please contact webmaster";
	var_dump($e);
}

function retrieveEmail($client,$pname){
		$rr = new ExactTarget_RetrieveRequest();
    $rr->ObjectType = 'Email';

    //Set the properties to return
    $props = array('ID', 'Name');
    $rr->Properties = $props;

    //Setup account filtering, to look for a given account MID
    $filterPart = new ExactTarget_SimpleFilterPart();
    $filterPart->Property = 'Name';
    $values = array($pname);
    $filterPart->Value = $values;
    $filterPart->SimpleOperator = ExactTarget_SimpleOperators::equals;

    //Encode the SOAP package
    $filterPart = new SoapVar($filterPart, SOAP_ENC_OBJECT,'SimpleFilterPart', "http://exacttarget.com/wsdl/partnerAPI");

    //Set the filter to NULL to return all MIDs, otherwise set to filter object
    //$rr->Filter = NULL;
    $rr->Filter = $filterPart;

    //Setup and execute request
    $rrm = new ExactTarget_RetrieveRequestMsg();
    $rrm->RetrieveRequest = $rr;
    $results = $client->Retrieve($rrm);

   echo '<pre>';
	 
	 return $results->Results->ID;
	 
	 
	 echo '</pre>';	
	
	
}




function sendEmail($email,$client,$lists,$sender){
	
	
	
	//$lists = implode(",",$lists);

	//$sendDate = strtotime("+1 minute");
	$sendDate = strtotime("+10 seconds");
		
				$emailSendDef = new ExactTarget_EmailSendDefinition();
        $emailSendDef->CustomerKey = "333333";
        //$emailSendDef->Name = "Shareable Content Send";
	
								$send = new ExactTarget_Send();
								$send->Email = $email;
								
								$myLists = array();
								foreach($lists as $list){
									$newList = new ExactTarget_List();
									$newList->ID = $list;
									$myLists[] = $newList;
									
								}
								
								//$list = new ExactTarget_List();
								//$list->ID = "299500";
								//$list->ID = '300512';								
							
								
								//$list2 = new ExactTarget_List();
								//$list2->ID = '384277';
									
								$send->List = $myLists;
										
	
								//$sendDefList->List = $list;
								//$sendDefList->DataSourceTypeID = "List";
								//$sendDefList->SendDefinitionListType = "SourceList";
								//$emailSendDef->SendDefinitionList[] = $sendDefList;
								
								
								//$send->SendDate = $sendDate;
								$send->SendDate = $sender->timestamp;
								$send->FromAddress = $sender->email;
								$send->FromName = $sender->name;
								$send->EmailSendDefinition = $emailSendDef;
								$send->UniqueOpens = '500';
								
                $object = new SoapVar($send, SOAP_ENC_OBJECT, 'Send', "http://exacttarget.com/wsdl/partnerAPI");
 
                $request = new ExactTarget_CreateRequest();
                $request->Options = NULL;
                $request->Objects = array($object);
								
								//$email->CategoryIDSpecified = true;
								//$email->CategoryID = 338240;
 
                $results = $client->Create($request);								
	
			
				echo '<pre>';var_dump($results);echo '</pre>';	
	
/*
	
				$emailSendDef = new ExactTarget_EmailSendDefinition();
        $emailSendDef->CustomerKey = "Shareable Content Send2";
        $emailSendDef->Name = "Shareable Content Send";

        //Setup the Send Classification
        $sendClass = new ExactTarget_SendClassification();
        $sendClass->CustomerKey = "470";
        $emailSendDef->SendClassification = $sendClass;

        // Setting Up the Source List
        $emailSendDef->SendDefinitionList = array();
        $sendDefList = new ExactTarget_SendDefinitionList();
        $list = new ExactTarget_List();
        $list->ID = "299500";
        $sendDefList->List = $list;
        $sendDefList->DataSourceTypeID = "List";
        $sendDefList->SendDefinitionListType = "SourceList";
        $emailSendDef->SendDefinitionList[] = $sendDefList;

        // Setting up the exclude list
        /* $sendDefListExclude = new ExactTarget_SendDefinitionList();
        $listExclude = new ExactTarget_List();
        $listExclude->ID = "1729515";
        $sendDefListExclude->List = $listExclude;
        $sendDefListExclude->DataSourceTypeID = "List";
        $sendDefListExclude->SendDefinitionListType = "ExclusionList";
        $emailSendDef->SendDefinitionList[] = $sendDefListExclude;  

        // Specify the Email To Send
       // $email = new ExactTarget_Email();
       	$email->ID = "789022";
        $emailSendDef->Email = $email;
        $object = new SoapVar($emailSendDef, SOAP_ENC_OBJECT, 'EmailSendDefinition', "http://exacttarget.com/wsdl/partnerAPI");

        $request = new ExactTarget_CreateRequest();
        $request->Options = NULL;
        $request->Objects = array($object);

        $results = $client->Create($request);
			echo "ID is ".$email->ID;
				echo '<pre>';var_dump($results);echo '</pre>';
	
*/
	
	
	
	}

?>