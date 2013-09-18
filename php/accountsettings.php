<?php
/**
 * Account Settings
 *
 * This is the page the user visits to change all of their personal settings.
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
 *	09/17/2013	- Added DocBlock
 */


require_once './includes/sessionmgt.php';
require_once './includes/constants.php';
require_once './includes/stdlib1_0.php';
require_once './includes/accountmgt.php';

//If the user isn't logged in, send them home.
if($_SESSION['Permissions'] < 10){
	header("Location: http://"._ROOT_URL_);
	exit;
}
if($_POST['ChangeSettings']){
	UpdateAccountSettings();
}
?>
<HTML>
<HEAD>
<TITLE><?php echo _PRETTY_TITLE_; ?> - Account Settings</TITLE>
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
		<DIV id="AccountSettings" class="ContentBox" style="display:inline-block;margin-left:auto;margin-right:auto;text-align:left;padding:50px;">
			
				<FORM NAME="EditAcctSettings" ACTION="" METHOD="POST">
				<p><LABEL FOR="FirstName">First Name:</LABEL><BR><INPUT TYPE="TEXT" NAME="FirstName" ID="FirstName" CLASS="ASBox" VALUE="<?php echo $_SESSION['FirstName']; ?>"></p>
				<p><LABEL FOR="LastName">Last Name:</LABEL><BR><INPUT TYPE="TEXT" NAME="LastName" ID="LastName" CLASS="ASBox" VALUE="<?php echo $_SESSION['LastName']; ?>"></p>
				<P><LABEL FOR="CAType">Type:<br><SELECT NAME="CAType" ID="CAType" CLASS="ASBox">
					<OPTION VALUE="1" <?php if($_SESSION['Type'] == '1'){ echo 'SELECTED'; }?>>Minister</OPTION>
					<OPTION VALUE="2" <?php if($_SESSION['Type'] == '2'){ echo 'SELECTED'; }?>>Sunday School Teacher/Leader</OPTION>
					<OPTION VALUE="3" <?php if($_SESSION['Type'] == '3'){ echo 'SELECTED'; }?>>Lay Leader</OPTION>
					<OPTION VALUE="4" <?php if($_SESSION['Type'] == '4'){ echo 'SELECTED'; }?>>Church Member</OPTION>
					<OPTION VALUE="5" <?php if($_SESSION['Type'] == '5'){ echo 'SELECTED'; }?>>Other</OPTION>
				</SELECT>
				<p><LABEL FOR="Email">Email Address (Username):</LABEL><BR><INPUT TYPE="EMAIL" NAME="Email" CLASS="ASBox" ID="Email" VALUE="<?php echo $_SESSION['EmailAddress']; ?>"></p>
				<p><LABEL FOR="Password1">New Password:</LABEL><BR><INPUT TYPE="PASSWORD" NAME="Password1" CLASS="ASBox" ID="Password1"></p>
				<p><LABEL FOR="Password2">Verify New Password:</LABEL><BR><INPUT TYPE="PASSWORD" NAME="Password2" CLASS="ASBox" ID="Password2"></p>
				<p><INPUT TYPE="Checkbox" NAME="Subscribe" ID="Subscribe" VALUE="1" <?php if($_SESSION['Subscribed'] == 1){echo 'checked';} ?>><LABEL FOR="Subscribe">Please send me weekly<br>updates of newly shared content</LABEL></p>
				<BR>
				<p><LABEL FOR="OldPassword">Current Password:</LABEL><BR><INPUT TYPE="PASSWORD" NAME="OldPassword" CLASS="ASBox"  ID="OldPassword"></p><BR>
				<DIV CLASS="center"><INPUT TYPE="submit" VALUE="Change Settings" NAME="ChangeSettings"></DIV>
				</FORM>
		</DIV>
</DIV>
</BODY>
</HTML>