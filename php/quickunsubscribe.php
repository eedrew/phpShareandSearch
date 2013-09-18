<?php
/**
 * Quick Unsubscribe page
 *
 * Users can edit their account settings to unsubscribe, but just in case they don't want to deal with logging into their account
 * we give them another option.  Like if they don't know the password.
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
 * Changes:
 *	09/17/2013 - Added DocBlock and some comments.
 */

require_once './includes/sessionmgt.php';							// Manages user's session and provides account box.
require_once './includes/constants.php';							// Most all site customization is here.
require_once './includes/stdlib1_0.php';							// Standard library of functions.
require_once './includes/databasefuncs.php';						// A handful of database functions.


//If the user reaches this page signed-in, simply redirect them to the account settings page - we'd rather they edited the settings there.
if($_SESSION['Permissions'] >= 10){
	header("Location: http://"._ROOT_URL_."/accountsettings.php");
	exit;
}

if(!$_GET['UID'] || !$_GET['EmailAddress']){			// If the user reached this page without their UID and EmailAddress in the request, then we cannot continue
	Mess("The information in the link that directed you here is invalid.  To unsubscribe, please login above then then visit Account Settings.");	// Provide an error message.
	fs_error_log('A user reached the quickunsubscribe.php page without a UID and EmailAddress',$_GET['UID'],$_GET['EmailAddress'],$_SERVER['SCRIPT_NAME']);	// Log in error_log.
	$disableSubmit = ' disabled="disabled" ';			// Disable their ability to unsubscribe, because we won't have enough information anyway.
}

if($_POST['Unsubscribe']){
	$mysqli = new fs_mysqli(_DBASE_HOST_, _DBASE_USERNAME_, _DBASE_PASSWORD_, _DBASE_NAME_);	// Connect to the database.
	if(!$mysqli->connect_errno){																// If successful
		$ERequest = $mysqli->fs_real_escape_array($_REQUEST);									// Escape the whole $_REQUEST array
		$sql = "UPDATE Accounts SET Subscribed = '0' WHERE UID='$ERequest[UID]' AND EmailAddress = '$ERequest[EmailAddress]' LIMIT 1";
		if($mysqli->query($sql)){
			if($mysqli->affected_rows == 1){
				Mess("Unsubscribe successful.  If you wish to rejoin the mailing list, please login to your accout and visit Account Settings");
				$disableSubmit = ' disabled="disabled" ';										// Once the user is unsubscribed, take away the unsubscribe button.
			} else {
				Mess("Error unsubscribing you.  Please email "._ADMIN_EMAIL_." to unsubscribe");
				fs_error_log("No records were updated when trying to unsubscribe",$_GET['UID'],$_GET['EmailAddress'],$_SERVER['SCRIPT_NAME']);
			}
		} else {
			Mess("Error unsubscribing you.  Please email "._ADMIN_EMAIL_." to unsubscribe");
			fs_error_log("Error running unsubscribe query",$mysqli->errno,$mysqli->error,$_SERVER['SCRIPT_NAME']);
		}
	} else {
		Mess("Error connecting to the database, please try again later");
		fs_error_log("Error connectin to the database for a quick unsubscribe",$mysqli->connect_errno,$mysqli->connect_error,$_SERVER['SCRIPT_NAME']);
	}
}
?>
<HTML>
<HEAD>
<TITLE><?php echo _PRETTY_TITLE_; ?> - Unsubscribe</TITLE>
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
		<DIV id="Quick Unsubscribe" class="ContentBox" style="display:inline-block;margin-left:auto;margin-right:auto;text-align:left;padding:50px;">
			<p>The link you clicked was in an email addressed to <b><?php echo $_GET['EmailAddress']; ?></b>. If this isn't your email address, please don't unsubscribe the user who probably wants to keep receiving our emails.</p>
				<FORM NAME="QuickUnsubscribe" ACTION="" METHOD="POST">
					<P>Are you sure you want to unsubscribe from weekly content updates from <?php echo _PRETTY_TITLE_; ?>?</P>
					<INPUT TYPE="submit" NAME="Unsubscribe" VALUE="Yes, I'm Sure" <?php echo $disableSubmit; ?>>
				</FORM>
			<p>If you change your mind later and wish to resubscribe, please login and visit Account Settings.</p>
		</DIV>
</DIV>
</BODY>
</HTML>