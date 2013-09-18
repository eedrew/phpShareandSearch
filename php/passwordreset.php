<?php
/**
 * Password Reset
 *
 * Thsi page gives the user the ability to reset his/her forgotten password.
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
 *	09/17/2013 - Added DocBlock and some better comments.
 */

require_once './includes/sessionmgt.php';
require_once './includes/constants.php';
require_once './includes/stdlib1_0.php';
require_once './includes/databasefuncs.php';
require_once './includes/accountmgt.php';

//If the user reaches this page signed-in, simply redirect them to the account settings page.
if($_SESSION['Permissions'] >= 10){
	header("Location: http://"._ROOT_URL_."/accountsettings.php");
	exit;
}

// When the user requests a password reset, we store the email address that they sent the request to in the session.
// If it isn't there, we can't continue.
if(!$_SESSION['ResetPWEmail']){
	Mess("The information in the link that directed you here is invalid.  Please try again to login.");
	fs_error_log('A user reached the passwordreset.php page without a an EmailAddress',NULL,$_GET['EmailAddress'],$_SERVER['SCRIPT_NAME']);
	$disableSubmit = ' disabled="disabled" ';				// So we disable the reset button.
}

//	If the CODE for reseting the password hasn't been created yet, then we need to create it and send the email.
if(!$_SESSION['ResetPWCode']){
	$mysqli = new fs_mysqli(_DBASE_HOST_, _DBASE_USERNAME_, _DBASE_PASSWORD_, _DBASE_NAME_);
	if(!$mysqli->connect_errno){
		$sql = "SELECT UID FROM Accounts WHERE EmailAddress = '$_SESSION[ResetPWEmail]' LIMIT 1";	// Pull the UID from the database that matches the email address that requested the reset.
		if($result = $mysqli->query($sql)){
			if($result->num_rows == 1){
				$_SESSION['ResetPWCode'] = fs_rdm_chr_gen(8);				// This is a random code generator in the stdlib1_0.
				$to = $_SESSION['ResetPWEmail'];
				$sbj = "Reset password request for "._PRETTY_TITLE_;
				$headers =	'From: ' . _PRETTY_TITLE_ . '<' . _ADMIN_EMAIL_ . ">\r\n" .
							'Reply-To: ' . _ADMIN_EMAIL_ . "\r\n" .
							'X-Mailer: PHP/' . phpversion();
				$message =	"We received a request for a password reeset on your "._PRETTY_TITLE_." account. \r\n".
							"Copy and paste this code into the Code box on in the browser you requested the reset from.\r\n\r\n".
							"CODE: ".$_SESSION['ResetPWCode'];
				if(mail($to,$sbj,$message,$headers)){
					Mess("The reset password verification was sent to your email successfully");
				} else {
					Mess("Error sending password verification email.  Perhaps you registered with an invalid email address?  If you this this message is in error, please try again later");
					fs_error_log("Error sending password verification email",$to,$headers,$_SERVER['SCRIPT_NAME']);
				}
			} else {
				Mess("We don't have the email address you tried to login with on file, please try again to login with the correct email address.");
			}
			$result->close;
		} else {
			Mess("Database error, please try again later");
			fs_error_log('Query issue for password reset',$mysqli->errno,$mysqli->error,$_SERVER['SCRIPT_NAME']);
		}
		$mysqli->close;
	} else {
		Mess("Database error, please try again later");
		fs_error_log('Database issue for password reset',$mysqli->connect_errno,$mysqli->connect_error,$_SERVER['SCRIPT_NAME']);
	}
}

if($_POST['ResetPassword']){
	if($_POST['Code'] == $_SESSION['ResetPWCode']){		// Verify the code we emailed out matches the code the user entered int he box.
		if(ValidateA(FALSE,TRUE)){						// Validate the password meets the minimum requirements.
			$mysqli = new fs_mysqli(_DBASE_HOST_, _DBASE_USERNAME_, _DBASE_PASSWORD_, _DBASE_NAME_);
			if(!$mysqli->connect_errno){
				$EPost = $mysqli->fs_real_escape_array($_POST);
				$sql = "UPDATE Accounts SET Password = '$EPost[Password1]' WHERE EmailAddress = '$_SESSION[ResetPWEmail]' LIMIT 1";
				if($mysqli->query($sql)){
					if($mysqli->affected_rows == 1){
						Mess("Password reset successful.  Please login above.");
						$disableSubmit = ' disabled="disabled" ';
					} else {
						Mess("Error resetting password.  Please email "._ADMIN_EMAIL_." for assistance");
						fs_error_log("No records were updated when trying to reset password '$_POST[ResetPWEmail]'",NULL,NULL,$_SERVER['SCRIPT_NAME']);
					}
				} else {
					Mess("Error resetting password.  Please email "._ADMIN_EMAIL_." for assistance");
					fs_error_log("Error running reset password query",$mysqli->errno,$mysqli->error,$_SERVER['SCRIPT_NAME']);
				}
			} else {
				Mess("Error connecting to the database, please try again later");
				fs_error_log("Error connectin to the database for a password reset",$mysqli->connect_errno,$mysqli->connect_error,$_SERVER['SCRIPT_NAME']);
			}
		}
	} else {
		Mess("Error: The verification code you entered is not correct.  Please check your email and copy and paste the verification into the box provided");
	}
}
?>
<HTML>
<HEAD>
<TITLE><?php echo _PRETTY_TITLE_; ?> - Password Reset</TITLE>
<link rel="stylesheet" type="text/css" href="./includes/style.css">
<script type="text/javascript" src="./includes/javafuncs.js"></script>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</HEAD>
<BODY>
<?php
include_once("./includes/googleanalytics.php");
echo SignInBox();	//Add the sign in box at the top of the screen.
 ?>
<DIV id="Content">
<?php include_once("./includes/header.php"); ?>

<!-- PHP Message Echo Box -->
		
	<DIV id="MessageReportingArea" class="ErrorBox" STYLE="<?php if(!$Message) echo "display:none;"; ?>"><?php echo substr($Message,4); ?></DIV>

<!-- Main Body -->	
		<DIV id="ResetPassword" class="ContentBox" style="display:inline-block;margin-left:auto;margin-right:auto;text-align:left;padding:50px;">
				<FORM NAME="PasswordReset" ACTION="" METHOD="POST">
				<p><LABEL FOR="Password1">New Password:</LABEL><BR><INPUT TYPE="Password" NAME="Password1" ID="Password1"></p>
				<p><LABEL FOR="Password2">Verify New Password:</LABEL><BR><INPUT TYPE="Password" NAME="Password2" ID="Password2"></p>
				<p><LABEL FOR="Code">Verification CODE:</LABLE><BR><INPUT TYPE="TEXT" NAME="Code" ID="Code"><DIV CLASS="HelpText">(This was just sent to your email)</DIV></p>
				<div class="center"><INPUT TYPE="SUBMIT" NAME="ResetPassword" VALUE="Reset Password" <?php echo $disableSubmit; ?>></div>
				</FORM>
		</DIV>
</DIV>
</BODY>
</HTML>