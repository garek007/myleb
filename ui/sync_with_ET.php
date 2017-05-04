<?php
session_start();
header("Access-Control-Allow-Origin: *");

require('00-Includes/exacttarget_soap_client.php');
$wsdl = 'https://webservice.s4.exacttarget.com/etframework.wsdl';
$etusername = 'salachniewicz';//Enter the username you use to login to Exact Target
$etpassword = '750_BStreet!';//Enter the password you use to login to Exact Target

$lists = $_POST['lists'];
$sender = new StdClass();
$sender->name = $_SESSION['from_name'];
$sender->email = $_SESSION['from_email'];
$sender->send_date = $_POST['send_date'];
$sender->send_time = $_POST['time'];

$fulldatetime = $sender->send_date . ' ' . $sender->send_time;



$sender->timestamp = strtotime($fulldatetime);




$html = $_REQUEST["html"];





	
	

//include '00-Includes/functions.php';


try//try to connect
{
	$client = new ExactTargetSoapClient($wsdl, array('trace'=>1));
	$client->username = $etusername;
	$client->password = $etpassword;



								$email = new ExactTarget_Email();
                $email->Name = "2016 Summer Seasonal Email #3";
								$email->ID = 829771;
               // $email->Description = 'Shareable Content Module from MyLEB';
                $email->HTMLBody = $html;
                //$email->Subject = $_SESSION['subject'];
                $email->EmailType = 'HTML';
                $email->IsHTMLPaste = 'false';
								
								//$email->ID = "811629";
								//$email->ClonedFromID = 325013;
								
								//$template = new ExactTarget_Template;
								//$template->CategoryID = 325013;
								
						
								
								
                                                               
                $object = new SoapVar($email, SOAP_ENC_OBJECT, 'Email', "http://exacttarget.com/wsdl/partnerAPI");
 
                $request = new ExactTarget_UpdateRequest();
                $request->Options = NULL;
                $request->Objects = array($object);
								
								//$email->CategoryIDSpecified = true;
								//$email->CategoryID = 338240;
 
                $results = $client->Update($request);
								
								echo '<pre>';
								echo $html;
								//var_dump($results);
								echo '</pre>';
								
								//sendEmail($email,$client, $lists,$sender);
								
               		//echo "ID is ".$email->ID;
				//echo '<pre>';var_dump($results);echo '</pre>';
		
	echo '<h1>Your email has been created for you in ExactTarget. You\'re welcome.</h1><br>
	<img src="http://67.media.tumblr.com/3014e3f261ac11edccdc52cdbf7673d0/tumblr_midlpknusY1rooxc2o1_400.gif" /><br>
	<img src="http://www.enjoysandiego.com/mirum.jpg" />
	';




}catch (SoapFault $e) {
	/* output the resulting SoapFault upon an error */
	echo "Error please contact webmaster";
	var_dump($e);
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