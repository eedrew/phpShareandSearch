<?php
 /**
 * Account management functions.
 *
 * Function List:
 *	ProcessNewAccount() - Manages a form submission to create a new account.
 *	UpdateAccountSettings() - Allows a user to change his profile/settings.
 *	ValidateA() - Checks form data for new account or update to ensure correct information.
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
 *	9/3/2013 - Renamed ValidateNA() to ValidateA() and made other changes to make it work for both new accounts, but also account changes.
 */
 
require_once 'databasefuncs.php';
require_once 'mailfuncs.php';
require_once 'constants.php';
require_once 'stdlib1_0.php';

//Creates a new account on form submission and generates the email to validate the account.
function ProcessNewAccount(){
		//If the data all looks good, proceed.
	if(ValidateA(FALSE)){
		EasyCon();
		//Escape the $_POST Array.
		$EPost = ArrayEscape($_POST);
		//Verify this email address isn't already in the account list.
		$sql = "SELECT * FROM Accounts WHERE EmailAddress = '$EPost[Email]'";
		if(EasyQ($sql)){
			
			if(CountRows() == 0){
				EasyFree();
				$ip = EasyEscape($_SERVER['REMOTE_ADDR']);
				$date = date("Y-m-d");
				$EPost['Email'] = strtolower($EPost['Email']);
				if($EPost['Subscribe'] != 1){
					$EPost['Subscribe'] = 0;
				}
				$sql = "INSERT INTO Accounts (FirstName, LastName, Type, EmailAddress, Password, RegistrationIP, FirstJoined, Subscribed) VALUES
					('$EPost[FirstName]', '$EPost[LastName]', '$EPost[CAType]', '$EPost[Email]', '$EPost[Password1]', '$ip', '$date', '$EPost[Subscribe]')";
				if(EasyInsUpd($sql)){
					Mess('Account Created Successfully');
					SendVerificationEmail($_POST['Email']);
				} else {
					Mess("Could not create account. Database Error: " . EasyQError());
					error_log("Application Error: Database Error creating account '". EasyQError . "' in $_SERVER[SCRIPT_NAME]");
				}
				EasyClose();
			} else {
				$row = EasyRow();
				if($row['Blocked']){
					Mess("This email address is already in our database but has been flagged for SPAM.  If you beleive this is incorrect, please email " . _ADMIN_EMAIL_);
					error_log("Application Error: Attempted login by a blocked user '$row[UID]' in $_SERVER[SCRIPT_NAME]");
				} elseif(!$row['Verified']){
					Mess('This email address has already been registered, but not verified. Check your email for a message from ' . _ADMIN_EMAIL_ . ' to verify. Or <a href="resendverificationemail.php?To=' . urlencode($_POST['Email']) .'" target="_blank">Resend Verification Email</a>');
					error_log("Application Error: Attempted login by an unverified account '$row[UID]' in $_SERVER[SCRIPT_NAME]");
				} else {
					Mess("<br>This email address already has an account.  Please Login above.");
					error_log("Application Error: Attempted recration of an account '$row[UID]' in $_SERVER[SCRIPT_NAME]");
				}
				EasyFree();
				EasyClose();
			}
		} else {
			Mess("Database Error.  Err: " . EasyQError());
			error_log("Application Error: Database Error creating account '".EasyQError()."' in $_SERVER[SCRIPT_NAME]");
		}
	}
}

//Updates a users profile/settings.
function UpdateAccountSettings(){
	//The user doesn't have to change his password, if he doesn't choose to, ignore it in the validation.
	if(($_POST['Password1'] == '' OR $_POST['Password1'] == NULL) && ($_POST['Password2'] == '' OR $_POST['Password2'] == NULL)){
		$exclPW = TRUE;
	} else { $exclPW = FALSE; }
	if(ValidateA($exclPW)){
		$mysqli = new fs_mysqli(_DBASE_HOST_, _DBASE_USERNAME_, _DBASE_PASSWORD_, _DBASE_NAME_);
		if (!$mysqli->connect_errno) {
			$EPost = $mysqli->fs_real_escape_array($_POST);
			$EPost['Email'] = strtolower($EPost['Email']);
			if($EPost['Subscribe'] != 1){
				$EPost['Subscribe'] = 0;
			}
			if(!$exclPW){
				$pwSQL = ",Password = '$EPost[Password1]'";
			} else {
				$pwSQL = "";
			}
			$sql = "SELECT Password FROM Accounts WHERE UID='$_SESSION[UID]' LIMIT 1";
			if($result = $mysqli->query($sql)){
				$row = $result->fetch_assoc();
				if($row['Password'] == $_POST['OldPassword']){
					$result->close();
					$date = date("Y-m-d");
					$sql = "UPDATE Accounts SET FirstName = '$EPost[FirstName]', LastName = '$EPost[LastName]', Type = '$EPost[CAType]', EmailAddress = '$EPost[Email]', Subscribed = '$EPost[Subscribe]', Updated = '$date' $pwSQL WHERE UID = '$_SESSION[UID]' LIMIT 1";
					if($mysqli->query($sql)){
						Mess("Account updated successfully");
						$sql = "SELECT UID, FirstName, LastName, Type, Subscribed, EmailAddress, Permissions, Blocked, Verified FROM Accounts WHERE UID = '$_SESSION[UID]' LIMIT 1";
						if($result = $mysqli->query($sql)){				//Run the query to get the most up-to-date data from the database.
							$row = $result->fetch_assoc();				//Pull the data from the result
							$_SESSION = array_merge($_SESSION,$row);	//Update the session variables with the new information.
						} else {
							Mess("Database error after your account was successfully updated.  You may need to log out and log back in again for the system to operate correctly.");
							error_log("Application Error: Issue retriving account information to update session variables after account modification '".$mysqli->errno.": ".$mysqli->error."' in $_SERVER[SCRIPT_NAME]");
						}
					} else {
						Mess("Error updating account.  Please try again later. ". $mysqli->errno . ": ". $mysqli->error);
						error_log("Appliation Error: Query issue updating account '". $mysqli->errno . ": ".$mysqli->error."' in $_SERVER[SCRIPT_NAME]");
					}
					//RUN UPDATE
					//RUN QUERY TO PULL DATA BACK OUT SO CAN DO ANOTHER ARRAY_MERGE WITH $_SESSION to update the session data.
				} else {
					Mess("Error: The <i>Current Password</i> you provided does not match our records.  Please try again.");
				}
			} else {
				Mess("Error: Database Error: ". $mysqli->errno . ": ". $mysqli->error);
				error_log("Application Error: Issue retreiving password from the database when trying to update UserAccountSettings '".$msqli->errno.": ".$mysqli->error." in $_SERVER[SCRIPT_NAME]");
			}
			$mysqli->close;
		} else {
			Mess("Error: Database Error: ". $mysqli->connect_errno.": ".$mysqli->connect_error);
			error_log("Application Error: Issue connectint to dabase on account update '".$mysqli->connect_errno.": ".$mysqli->connect_error." in $_SERVER[SCRIPT_NAME]");
		}
	}
}

//Validates $_POST data for account creation/modification.
//If $exclPassword, then the passwords are not verified (for modification of account);
function ValidateA($exclPassword=FALSE,$exclOther=FALSE){
	$Return = TRUE;
	if(!$exclOther){
		if(strlen($_POST['FirstName']) < 2) {
			Mess("Error: Please provide your First Name");
			$Return = FALSE;
		}
		if(strlen($_POST['LastName']) < 2){
			Mess("Error: Please provide your Last Name");
			$Return = FALSE;
		}
		if($_POST['CAType'] == 0){
			Mess("Error: Please provide information on what Type of user you are");
			$Return = FALSE;
		}
		if(!filter_var($_POST["Email"], FILTER_VALIDATE_EMAIL)){
			Mess("Error: The email address you provided does not look legit: ".$_POST['Email']);
			$Return = FALSE;
		}
	}
	//If passed parameter, exclPassword, is false, then don't validate the password.
	if(!$exclPassword){
		if($_POST['Password1'] != $_POST['Password2']){
			Mess("Error: The password and the password verify entries are not equal.  Please type the same password twice");
			$Return = FALSE;
		}
		similar_text($_POST['Password1'], $_POST['Email'], $percentSimilar); // Calculate the percentage similarity between the email address and password.
		if(strlen($_POST['Password1']) < 8 || strlen($_POST['Password1']) > 20){
			Mess("Error:  Please choose a password with at least 8 and fewer than 20 characters");
			$Return = FALSE;
		} elseif($percentSimilar > 50){
			Mess("Error: Your password is too similar to your email address, please choose a different password");
			error_log("Application Error: Password too similar to email address on new account creation '$_POST[Password1]' and '$_POST[Email]' was '$percentSimilar %' in $_SERVER[SCRIPT_NAME]");
		}
	}
	return $Return;
}
?>