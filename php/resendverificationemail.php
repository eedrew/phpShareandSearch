<?php
/**
 * Resend Verification Email
 *
 * If a user tries to login and hasn't verified, they have the option of having the verification email sent again.
 * there is a limitation, however, of how many times they can request this.  We don't want them annoying the person
 * whose email they actually entered, if they entered the wrong email address.
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
 * 09/17/2013 - Added DocBlock and some better commenting.
 * 09/01/2013 - Added error_log functions and also checked to see if the user is already logged in.
 */
 
require_once './includes/sessionmgt.php';						// Manages user sessions and provides the accounts box html.
require_once './includes/stdlib1_0.php';						// A standard library of functions
require_once './includes/constants.php';						// All the configuration necessary to customize yours site... well almost all.
require_once './includes/mailfuncs.php';						// Emails the verification email.
require_once './includes/databasefuncs.php';					// Various database functions.

if(EasyCon()){												// Send Verification Email is expecting us to already be connected to the database.
	if($_SESSION['Permissions'] >= 10){						// If the user is already logged in, don't let them verify.
		Mess("You're already logged in, no need to verify now.");	// Give the user the error message.
		error_log("Application Error: A user tried to resend the verification email after he was already logged in, in $_SERVER[SCRIPT_NAME]"); // Log the error message.
	} else {
		SendVerificationEmail($_GET['To']);					// Actually resend the verification email
	}
	EasyClose();											// Close the connection to the database.
} else {
	Mess("Error: Database connection issue.");				// Give the user the error message.
	error_log("Application Error: Database Error: '".mysqli_connect_errno().": ".mysqli_connect_err()."' in $_SERVER[SCRIPT_NAME]"); //Log it to the error_log
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
echo SignInBox() //This dynamically creates the sign-in-box depending on the logged-in or not logged in status.
?>
<DIV id="Content">
<?php include_once("./includes/header.php"); ?>

<!-- PHP Message Echo Box -->
		
	<DIV id="MessageReportingArea" class="ErrorBox" STYLE="<?php if(!$Message) echo "display:none;"; ?>"><?php echo substr($Message,4); ?></DIV>

<!-- Main Body -->	
</DIV>
</BODY>
</HTML>