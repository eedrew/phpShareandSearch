<?php
/** 
 * FileRequest page
 * 
 * Where user's get more information about a file before download it, and can report or like the file.
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
 *	09/07/2013 - Stopped displaying parsed text on this output because some users were concerned with how messy it looked.
 */
require_once './includes/sessionmgt.php';
require_once './includes/databasefuncs.php';
require_once './includes/stdlib1_0.php';
require_once './includes/constants.php';

function EvaluateReport($U_UID){
	$mysqli = new fs_mysqli(_DBASE_HOST_, _DBASE_USERNAME_, _DBASE_PASSWORD_, _DBASE_NAME_);
	if(!$mysqli->connect_errno){
		$sql = "SELECT SUM(Reports) AS NoReports FROM Content
					WHERE U_UID = '$U_UID'";									// Count the number of reports
		if($result = $mysqli->query($sql)){
			$row = $result->fetch_assoc();
			if($row['NoReports'] >= _REPORTS_TO_BLOCK_){						// If there are 5 or more reports.
				$sql = "UPDATE Content SET Hidden = '1' WHERE U_UID = '$U_UID'"; // Hide all this users content.
				if(!$mysqli->query($sql)){
					fs_error_log("Application Error: hiding all user's contributions");
				} else {
					fs_error_log("Application Notice: All of user '$U_UID' content is now hidden.",NULL,NULL,$_SERVER['SCRIPT_NAME']);
				}
				$sql = "UPDATE Accounts SET Blocked = '1' WHERE UID = '$U_UID' LIMIT 1"; //Block the user.
				if(!$mysqli->query($sql)){
					fs_error_log("Application Error: blocking user '$U_UID'.",$mysqli->errno,$mysqli->error,$_SERVER['SCRIPT_NAME']);
				} else {
					fs_error_log("Application Notice: User '$U_UID' blocked.",NULL,NULL,$_SERVER['SCRIPT_NAME']);
				}
			}
			$result->close;
		} else {
			fs_error_log("Application Error: counting reports of a particular user",$mysqli->errno,$mysqli->error,$_SERVER['SCRIPT_NAME']);
		}
		$mysqli->close;
	} else {
		fs_error_log("Application Error: connecting to database while evaluating the report",$mysqli->connect_errno,$mysqli->connect_error,$_SERVER['SCRIPT_NAME']);
	}
}

	//Establish the connection to the database just once.
	if (!EasyCon()){
		//If the connection fails, report it to the user.
		Mess("Error: No connection to to Database. ErrNo: " . mysqli_connect_errno() . ' : ' . mysqli_connect_error());
		fs_error_log("Application Error: Couldn't connect to the database retreiving file information",mysqli_connect_errno(),mysqli_connect_error(),$_SERVER['SCRIPT_NAME']);
		//Because this means that the page will be pretty bare, lets put this error in the content area as well.
		$html = $Message;
	} else {
		//We'll be using this UID enough times, lets just escape it once.
		$SafeUID = EasyEscape($_GET['UID']);

//Get all of the information except the BLOB of the contents so that we can display it on the screen.		
		$sqlquery = "SELECT UID, Title, Author, Description, U_UID, Created, Contributed, Type, Tags, Pages, FileName, FileExt, Reports, Likes, Downloads FROM Content WHERE UID = '$SafeUID' AND Hidden = 0";
		if(EasyQ($sqlquery)){
//As long as there was a result, give the user nearly everything we know about this file.
//Removed file text because it confused people.
			if(CountRows() >= 1){
				$row = EasyRow();
				$html = "<p><b>Title:</b> " . $row['Title'] . "<br>\n<b>Author:</b> ". $row['Author'] . "<BR>\n<b>Description:</b> ". $row['Description'] . "<BR>\n<b>File Name (click to download):</b> <A HREF=". '"download.php?UID='.$row['UID'].'">'.$row['FileName']."</A></p>\r\n";
				$ItemContributorUID = $row['U_UID']; //Used later in case someone likes or reports a post.
//$Likes & $Reports are the values that would be updated in the database if the user chooses to like or report this.  They have already been incirimented here.
				$Likes = $row['Likes']+1;
				$Reports = $row['Reports']+1;
				EasyFree();
			} else {
				Mess("Error: File not found.");
				error_log("Application Error: Couldn't find file (no results): '".$sqlquery."' in $_SERVER[SCRIPT_NAME]");
				$html = $Message;
			}
		} else {
			Mess("Error: File not found.");
			error_log("Application Error: Database Error: '".EasyQErr()."' in $_SERVER[SCRIPT_NAME]");
			$html = $Message;
		}

//This code takes care of handling the users LIKE or REPORT response to the file.
		if($_POST["Response"]){
			if($_SESSION['Permissions'] >= 10){
				//We'll be using the IP enough times, lets just escape it once.
				$SafeIP = EasyEscape($_SERVER['REMOTE_ADDR']);
				$sqlquery = "SELECT UID FROM LikeReportHistory WHERE C_UID='".$SafeUID."' AND U_UID='".EasyEscape($_SESSION['UID'])."'";	
				if(EasyQ($sqlquery)){
	//If they user has already liked or reported this, don't let them do it again... or if they are the one who gave us the file.
					if(CountRows() >= 1){
						Mess("Error: You may not like or report a file more than once.");
						error_log("Application Error: Attempt at liking/reporting a file more than once: UID:'$SafeUID' IP:'$SafeIP' in $_SERVER[SCRIPT_NAME]");
					} elseif($ItemContributorUID == $_SESSION['UID']){
						Mess("Error: Because you contributed this file, you may not like nor report it.");
						error_log("Application Error: Attempt at liking/reporting their own file: UID: '$SafeUID' U_UID:'".$ItemContributorUID."' in $_SERVER[SCRIPT_NAME]");
					} elseif($_POST["Response"] == "Like") {
	//If they liked it, capture their IP and increment the likes.
						$sqlquery = "INSERT INTO LikeReportHistory (U_UID, C_UID, LikeIt) VALUES ('".EasyEscape($_SESSION['UID'])."', '$SafeUID', '1')";
						if(EasyInsUpd($sqlquery)){
							$sqlquery = "UPDATE Content SET Likes = '$Likes' WHERE UID = '".$SafeUID."' LIMIT 1";
							if(EasyInsUpd($sqlquery)){
								Mess("Content 'Liked' successfully.  Thanks for helping to make "._PRETTY_TITLE_." better!");
							} else {
								Mess("Error: Database Error: ".EasyQErr());
								error_log("Application Error: Couldn't like successfully(second Q): '".EasyQErr()."' in $_SERVER[SCRIPT_NAME]");
							}
						} else {
							Mess("Error: Database Error: ".EasyQErr());
							error_log("Application Error: Couldn't like successfully(first Q): '".EasyQErr()."' in $_SERVER[SCRIPT_NAME]");
						}
					} elseif($_POST["Response"] == "Report"){
	//If they reported it, capture their IP and increment the reports.
						if(!is_null($_POST["Reason"]) && strlen($_POST["Reason"]) >= 4){
							$sqlquery = "INSERT INTO LikeReportHistory (U_UID, C_UID, Report, Reason) VALUES ('".EasyEscape($_SESSION['UID'])."', '$SafeUID', '1', '".EasyEscape($_POST['Reason'])."')";
							if(EasyInsUpd($sqlquery)){
								$sqlquery = "UPDATE Content SET Reports = '$Reports' WHERE UID = '".$SafeUID."' LIMIT 1";
								if(EasyInsUpd($sqlquery)){
									Mess("Content reported successfully.  Thanks for helping to make "._PRETTY_TITLE_." better!  Once this file gets enough reports, all of the contributor's files will be removed from the database and the contributor will be banned from contributing again.");
									EvaluateReport($ItemContributorUID);
								} else {
									Mess("Error: Database Error: ".EasyQErr());
									error_log("Application Error: Couldn't report successfully(second Q): '".EasyQErr()."' in $_SERVER[SCRIPT_NAME]");
								}
							} else {
								Mess("Error: Database Error: ".EasyQErr());
								error_log("Application Error: Couldn't report successfully(first Q): '".EasyQErr()."' in $_SERVER[SCRIPT_NAME]");
							}
						} else {
							Mess("Error: Please provide a sufficient reason for reporting this content.");
							error_log("Application Error: No reason reported for report in $_SERVER[SCRIPT_NAME]");
						}
					}
				} else {
					Mess("Error: Could not Like/Report file. Err: ".EasyQErr());
					error_log("Application Error: Couldn't like/report successfully: '".EasyQErr()."' in $_SERVER[SCRIPT_NAME]");
				}
			} else {
				Mess("Error: You cannot like or report content without being logged in.  Please login first.");
				error_log("Application Error: User tried to like/report without being logged in.  This shouldn't be possible in $_SERVER[SCRIPT_NAME]");
			}
		}
		EasyClose(); //Close the connection to the database.
	}
	
?>
<HTML>
<HEAD>
<TITLE><?php echo _PRETTY_TITLE_ . ' - ' . $row['Title']; ?></TITLE>
<link rel="stylesheet" type="text/css" href="./includes/style.css">
<script type="text/javascript" src="./includes/javafuncs.js"></script>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</HEAD>
<BODY>
<?php
include_once "./includes/googleanalytics.php";
echo SignInBox();
 ?>
<DIV id="Content">
<?php include './includes/header.php'; ?>
<!-- PHP Message Echo Box -->
	<DIV id="MessageReportingArea" class="ErrorBox" STYLE="<?php if(!$Message) echo "display:none;"; ?>"><?php echo substr($Message,4); ?></DIV>
		
	<DIV id="FileInfo" class="ContentBox" style="width:1024px">
		<DIV style="<?php if($_SESSION['Permissions'] >= 10) { echo 'display:block;'; } else { echo 'display:none;'; } ?>">
			<p>Please help make <?php echo _PRETTY_TITLE_; ?> better.  If this file is SPAM or inappropriate, please report it.  If you really like this content, please tell others.<DIV CLASS="HelpText">(You will not leave this page after submitting)</DIV></p>
			<FORM NAME="ReportLike" ACTION="filerequest.php?UID=<?php echo $_GET['UID']; ?>" METHOD="POST">Like: <input type="radio" name="Response" value="Like" checked="Checked" onclick="hideThis('ReportReason')">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Report: <input type="radio" name="Response" value="Report" onclick="showThis('ReportReason')"><SPAN ID="ReportReason" style="display:none">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Report Reason: <input type="text" name="Reason" MAXLENGTH="255"></SPAN>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="Submit" value="Submit"></FORM>
		</DIV>
		<DIV style="<?php if($_SESSION['Permissions'] >= 10) { echo 'display:none;'; } else { echo 'display:block;'; } ?>">
			<p style="font-weight:700;font-style:italic">If you like this file, or if it needs reported as inappropriate please login to your account.</p>
		</DIV>
		<p class="BoxHeading">Selected Item Information</p>
		<?php echo $html; ?>			
	</DIV>
</DIV>
<DIV class="ContentBox" style="position:fixed;right:0;bottom:0;width:250px;z-index:10;">
<?php include './includes/share.php'; ?>
</DIV>
</BODY>
</HTML>