<?php
/**
 * Cron Jobs
 *
 * File is run on a weekly basis to perform the following functions
 *		* Email subscribed customers new resource contributions.
 *			* Also include newly liked resources.
 *		* Email the admin the error messages.
 *		* Cleanout any account creation requests more than 1 week old that have not been verified.
 *		* Maybe someday auto-promote people people to moderator based on some sort of activity.
 *				*e.g. 100 Visits + 10 Contributions + 10 Likes?
 *
 * Tested With: PHP Version 5.2.17
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program (see License.txt).  If not, see <http://www.gnu.org/licenses/>.
 *
 * DOCUMENTATION: Visit our SourceForge documentation page for information on installation and configuration: https://sourceforge.net/p/phpshareandsearch/wiki/Home/
 *
 * DONATIONS: While this code is distributed FREE OF CHARGE, your donations are appreciated to help support this project and continued developments and enhancements.
 *   Simply use this button multiple times to contribute the amount you feel is appropriate (in increments of $100): https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=93RPJ8GXVPWH4
 *   Or this button(in increments of $20): https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=PQG75X8T277A4\
 *   Donations are not tax-deductible and we are not a 503(c) organization, but we feel letting you choose the amount you this is appropriate to pay for our
 *   software makes more sense than setting a fixed price.
 *
 * @category	MainFile
 * @package		phpS&S
 * @author		Drew Siebert <siebert.public@gmail.com>
 * @copyright	2013 Drew Siebert
 * @license		http://www.gnu.org/licenses/gpl.html  GPLv3
 * @version		0.5
 * @link		https://sourceforge.net/projects/phpshareandsearch/
 * @since		File available since Release 0.5
 * @depreciated	N/A
 * DocBlock Standards: http://pear.php.net/manual/en/standards.sample.php
 *
 * CHANGELOG
 *	09/17/2013 	- Added DocBlock
 *				- Changed max emails per hour to _MAX_EMAILS_PER_TIMEFRAME_
 */

require_once './includes/databasefuncs.php';
require_once './includes/mailfuncs.php';
require_once './includes/constants.php';
define("_SECONDS_IN_A_DAY_", "86400");
define("_SECONDS_IN_A_WEEK_", "604800");

$mysqli = new mysqli(_DBASE_HOST_, _DBASE_USERNAME_, _DBASE_PASSWORD_, _DBASE_NAME_);
if(!$mysqli->errno){
	//We don't include any results from today, but we do include results from the day the last time this was sent out.
	$sql = "SELECT Title, Author, Description From Content WHERE Contributed between '".date("Y-m-d",time()-_SECONDS_IN_A_WEEK_)."' AND '".date("Y-m-d",time()-_SECONDS_IN_A_DAY_)."' AND Hidden = '0'";
	if($con_res = $mysqli->query($sql)){
		if($con_res->num_rows >= 1){
			while ($content=$con_res->fetch_assoc()) {
				$eml_con .= "-----\r\n".
							"Title: $content[Title]\r\n".
							"Author: $content[Author]\r\n".
							"Description:\r\n".
							"$content[Description]\r\n\r\n";
			}
			$filename = _ROOT_PATH_."/specialsubscribermessage.txt";
			if(file_exists($filename)){
				$spec_mess = fopen($filename,r);
				$spec_mess_txt = fread($spec_mess, filesize($filename));
				if($spec_mess_txt != ''){			//If there's anything in the special message, add some new lines to the bottom
					$spec_mess_txt .= "\r\n\r\n";
				}
				fclose($spec_mess);
				$spec_mess = fopen($filename,w);	//Clear the contents of the special message so it doesn't send next week
				fclose($spec_mess);
			} else { $spec_mess_txt = ''; };
			
			$eml_con =	"Thank you for subscribing to new content posted on "._PRETTY_TITLE_.".\r\n\r\n".
						$spec_mess_txt.
						"There are ".$con_res->num_rows." new resources posted this week:\r\n".
						$eml_con.
						"----------\r\n".
						"You received this message because of your subscription to "._PRETTY_TITLE_.".\r\n".
						"We never send more than one email per week, for your benefit.\r\n".
						"If you would like to unsubscribe, login to http://"._ROOT_URL_." and click on Account Settings.\r\n";
			$con_res->close;
			$headers =	'From: ' . _PRETTY_TITLE_ . '<' . _ADMIN_EMAIL_ . ">\r\n" .
						'Reply-To: ' . _ADMIN_EMAIL_ . "\r\n" .
						'X-Mailer: PHP/' . phpversion();
			$subj	=	'Your notification of new content on '._PRETTY_TITLE_;
			$sql = "SELECT FirstName, LastName, EmailAddress, UID from Accounts WHERE Verified = '1' AND Blocked = '0' AND Subscribed = '1'";
			if($sub_res = $mysqli->query($sql)){
					while($subscriber=$sub_res->fetch_assoc()){
						$eml_con .= "Or to quick-unsubscribe click http://"._ROOT_URL_."/quickunsubscribe.php?UID=$subscriber[UID]&EmailAddress=$subscriber[EmailAddress]";
						$to = "$subscriber[FirstName] $subscriber[LastName] <$subscriber[EmailAddress]>";
						if(!mail($to,$subj,$eml_con,$headers)){
							fs_error_log("Failed to send email to '$to'",NULL,NULL,$_SERVER['SCRIPT_NAME']);
						}
					}
				
				//Also need to wordwrap it wordwrap($str,$width=75,"\r\n")
			} else {
				fs_error_log('Query issue retrieving subscribers', $mysqli->errno, $mysqli->error, $_SERVER['SCRIPT_NAME']);
			}
		}
	} else { fs_error_log('Query issue retrieving latest content', $mysqli->errno, $mysqli->error, $_SERVER['SCRIPT_NAME']); }
} else {
	fs_error_log('Database connection issue retrieving subscribers', $mysqli->connect_errno, $mysqli->connect_error, $_SEVER['SCRIPT_NAME']);
}



//This function determines if there are any users whose verification emails should've expired, and deletes them from the database.
EasyCon();																								//Connect to the database.
$sql = "DELETE FROM Accounts WHERE Verified = '0' AND FirstJoined < DATE_ADD(NOW(),INTERVAL -8 DAY)";	//Write the query to delete unverified users greater than 8 days old.
if(EasyInsUpd($sql)){																					//Attempt to run the query
	$AccountsDeleted = EasyAffRows();																	//Count how many rows were effect.
} else {
	$AccountsDeleted = 'FAILED!';																		//If the query failed, say so in the email
	error_log("Application Error: Couldn't delete old unverified accounts: '".EasyQErr()."' in $_SERVER[SCRIPT_NAME]"); //And log it in the error_log
}
$sql = "SELECT COUNT(*) AS Subscribers FROM Accounts WHERE Subscribed = '1'";
if(EasyQ($sql)){
	$row = EasyRow();
	$Subscribers = $row['Subscribers'];
	EasyFree();
} else {
	$Subscribers = 'FAILED!';
	error_log("Application Error: Couldn't count subscribers: '".EasyQErr()."' in $_SERVER[SCRIPT_NAME]");
}
EasyClose();

//These two functions should always run last to capture any errors that were recorded during the cron job attempt.
$filename = _ROOT_PATH_."/error_log";
if(file_exists($filename)){
	$handle = fopen($filename, "r");										//Open the root error_log
	$root_errors = fread($handle, filesize($filename));						//Pull out the contents.
	fclose($handle);														//Close the file
	unlink($filename);														//Delete the error log
} else {
	$root_errors = "No errors to report!";
}

$filename = _ROOT_PATH_."/includes/error_log";
if(file_exists($filename)){
	$handle = fopen($filename, "r");										//Open the root error_log
	$includes_errors = fread($handle, filesize($filename));						//Pull out the contents.
	fclose($handle);														//Close the file
	unlink($filename);														//Delete the error log
} else {
	$includes_errors = "No errors to report!";
}

$msg = 	"Cron Job: '$_SERVER[SCRIPT_NAME]' just ran on "._PRETTY_TITLE_."\r\n\r\n".
		"Remember, my host only allows for " . _MAX_EMAILS_PER_TIMEFRAME_ . " emails/hour so some changes are required to cronjobs.php when we start getting close to that limit.\r\n".
		"Number of subscribers... $Subscribers/". _MAX_EMAILS_PER_TIMEFRAME_ . "\r\n".
		"Number of deleted old unverified accounts... $AccountsDeleted\r\n".
		"\r\n---\r\nRoot Error Log:\r\n---\r\n$root_errors\r\n".
		"\r\n---\r\nInclude Error Log:\r\n---\r\n$includes_errors\r\n";

$headers = 'From: ' . _PRETTY_TITLE_ . '<' . _ADMIN_EMAIL_ . ">\r\n" .
			'Reply-To: ' . _ADMIN_EMAIL_ . "\r\n" .
			'X-Mailer: PHP/' . phpversion();

//Email the Admin the results of the cron job.
mail(_ADMIN_EMAIL_,_PRETTY_TITLE_.' Cron Job Statistics',$msg,$headers);

//Show the results of the cron job on the screen if this happens to be being run manually.
//echo nl2br($msg);

?>