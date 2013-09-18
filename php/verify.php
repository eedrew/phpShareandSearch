<?php
/**
 * Verification page for phpShare&Search
 *
 * This is the page users visit to verify their account after they receive the verification email.
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
 *	09/17/2013 - Added DocBlock & better commenting
 *	09/01/2013 - Added redirect to homepage if the user is already logged in.
 */

require_once './includes/sessionmgt.php';		// Keeps track of the users session and provides the account box HTML
require_once './includes/constants.php';		// All configuration for the website
require_once './includes/databasefuncs.php';	// Some handy database functions (especially for the proceedural database calls)
require_once './includes/stdlib1_0.php';		// Standard library of functions.


//If the user is already logged in (or logs in while on this page) redirect him/her to the homepage.
if($_SESSION['Permissions'] >= 10){
	header("Location: http://"._ROOT_URL_);
	exit;
}

//If the form has been submitted, check the password for legitimacy and if so, update the record as a verified account.
if($_POST['Verify']){
	if(EasyCon()){																//Connect to the database.
		$EPost = ArrayEscape($_POST);											//Escape the whole $_POST Array
		$sql = "SELECT * FROM Accounts WHERE UID = '$EPost[UID]' LIMIT 1";		//Create SQL to pull user's info from databse.
		if(EasyQ($sql)){														//Run the SQL command.
			$row = EasyRow();													//Pull the result data.
			if(CountRows()==1){													//As long as there is a result, continue...											
				if(!$row['Verified']){											//If the user isn't already verified continue.
					if($row['Password'] == $_POST['Password']){					//Compare the password in the database with the password provided.
						EasyFree();												//Free the result set.
						$sql = "UPDATE Accounts SET Verified = '1' WHERE UID = '$EPost[UID]' LIMIT 1";	//Create the query for verifying since the passwords matched.
						if(EasyInsUpd($sql)){									//Run the query to set the user to verified.
							Mess('Thank you for verifying your email address.  Would you like to return to our <a href="http://' . _ROOT_URL_ . '">homepage</a>?');	//Give a link back to the homepage.
							LogIn($row['EmailAddress'],$_POST['Password']);		//Log the user in.
						} else {												//If the update fails, alert the user.
							Mess("Database Connection Issue: " . EasyQErr());	//Give an error message to the user.
							fs_error_log("Database update failed in verify.php",NULL,NULL,$_SERVER['SCRIPT_NAME']);	//Log an error to the error_log
						}
					} else {
						EasyFree();												//Free the result set.
						Mess("Password Incorrect.  Please enter the CaSe SenSative password you used when creating your account.");	// Report an error message to the user.
					}
				} else {
					Mess('This account is already verified.  Please login on our <a href="http://' . _ROOT_URL_ . '">homepage</a>');
				}
			} else {
				Mess('Your verification email has expired.  Please create a new account from our <a href="http://' . _ROOT_URL_ . '">homepage</a>');
			}
		} else {
			Mess("Database Connection Issue: " . EasyQErr());
		}
		EasyClose();															// Close the database connection.
	}
}
?>
<HTML>
<HEAD>
<TITLE><?php echo _PRETTY_TITLE_; ?> - Verify New Account Email</TITLE>
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
	<DIV id="VerifyArea" class="ContentBox" style="width:1024px">
		<P>Welcome back to <?php echo _PRETTY_TITLE_; ?>.  Please verify your email address so that we can finalize creating your account.</P>
		<FORM ACTION="" METHOD="POST" NAME="VerifyEmail">
			<INPUT TYPE="HIDDEN" NAME="UID" VALUE="<?php echo $_REQUEST['UID']; ?>">
			<p>Input Password: <INPUT TYPE="PASSWORD" NAME="Password" CLASS="VerifyFormTextBox">
			<DIV CLASS="HelpText">CaSE SENsitive</DIV></p>
			<INPUT TYPE="Submit" Name="Verify" Value="Verify">
		</FORM>
	</DIV>
</DIV>
</BODY>
</HTML>