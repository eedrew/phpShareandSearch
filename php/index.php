<?php
/**
 * Main page for phpShare&Search
 *
 * This is an application for sharing documents.
 *  index.php is the first page that visitors see (logged in or not) and it provides them with the ability to
 *	 - Login
 *	 - Register as a new user
 *	 - Share the application (branded by the installer) with others
 *	 - Search the database of shared content
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
 *	9/16/13 - Changed the new visitor message to a constant isntead of hard-coded.
 *  9/15/13 - Added license disclaimer/information as required by GPLv3
 *  9/14/13 - Added DocBlock
 *  9/01/13 - Added sharing buttons.
 *  8/21/13	- Added Google Analytics on index.php and filerequest.php.
 *  8/20/13 - Increased Blob to LongBlob on FileContents on Database.  This increased max filesize from 64K to 4G and got rid of corrupted file error on PDF download of PDFs over 64K.
 *	 - Changed PDF text extration routine to a different one from the Web... it is better.  Replaced \### that weren't being decoded with decoding algorythms.
 *	 - Put in some default text for PDFs that aren't decoded.
 *	8/19/13 - Added Show/Hide advanced search.
 */

// THIS WARNING CAN BE REMOVED IF WE EDIT IT TO HAVE HIDING ERRORS OFF BY DEFAULT AND TURNED ON BY A CONSTANT.
// WARNING: If you get a server error with no error messages, check error_log file or edit includes/sessionmgt.php
// Problem caused by: ini_set('display_errors','0');

//All needed includes...
require_once './includes/sessionmgt.php';	// Controls behaviour of logged in users and provides login box.
require_once './includes/formfuncs.php';	// Contains functions for dealing with upload form and search form on this page.
require_once './includes/accountmgt.php';	// Contains function(s) for dealing with new account creation.
require_once './includes/constants.php';	// Contains constant(s) that are used throughout this program and allows portability between servers/etc.

//This makes sure that the advanced search is shown if there is any data in the drop downs.
//	Basically just evaluates if there was any data in any of the search fields, and if so, shows the advanced search on page laod.
if ($_POST['Search'] && (($_POST['AuthorS'] != '' && !is_null($_POST['AuthorS']))  ||
	($_POST['CreatedSMonth'] != '0' && !is_null($_POST['CreatedSMonth'])) ||
	($_POST['CreatedSDay'] != '0' && !is_null($_POST['CreatedSDay'])) ||
	($_POST['CreatedSYear'] != '0' && !is_null($_POST['CreatedSYear']))||
	($_POST['PagesLo'] != '' && !is_null($_POST['PagesLo'])) ||
	($_POST['PagesHi'] != '' && !is_null($_POST['PagesHi'])) ||
	($_POST['TypeS'] != '' && !is_null($_POST['TypeS'])) ||
	($_POST['ContributedYear'] != '0' && !is_null($_POST['ContributedYear'])) ||
	($_POST['ContributedDay']  != '0' && !is_null($_POST['ContributedDay'])) || 
	($_POST['ContributedMonth'] != '0' && !is_null($_POST['ContributedMonth'])) ||
	($_POST['Likes'] != '' && !is_null($_POST['Likes'])))){
	$ASSearch = 'display:block;';
} else {
	$ASSearch = 'display:none;';		// If no data, don't show the advanced search divs.
}

//Upload Functionality
if ($_POST["Upload"]){
	ProcessUpload();					// Located in formfuncs.php
}

//Handle New Account Requests
if ($_POST["CreateAccount"]){
	ProcessNewAccount();				// Located in accountmgt.php
}

//Create the traditional HTML table of search query results.
$ResultsTableHTML = htmlResults();		// Located in formfuncs.php

?>
<HTML>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<HEAD>
<TITLE><?php echo _PRETTY_TITLE_ .' - '. _TAGLINE_; ?></TITLE>
<link rel="stylesheet" type="text/css" href="/includes/style.css">
<script type="text/javascript" src="./includes/javafuncs.js"></script>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</HEAD>
<BODY>
<?php
include_once("./includes/googleanalytics.php");		// This writes-in the Google Analytics snippit for user tracking
echo SignInBox();									//This dynamically creates the sign-in-box depending on the logged-in or not logged in status.

// Display a message to non-logged in users if setup in constants.php
if(!$Message && $_SESSION['Permissions'] < 10){
	Mess(_VISITORS_MESSAGE_);
}
?>
</DIV>
<DIV id="Content">
	<?php include './includes/header.php'; ?>
<!-- PHP Message Echo Box -->
		
		<DIV id="MessageReportingArea" class="ErrorBox" STYLE="<?php if(!$Message) echo "display:none;"; ?>"><?php echo substr($Message,4); ?></DIV>
		
<!-- Share Box -->
		<DIV id="ShareArea" class="ContentBox" style="float: left; clear:left; width: 250px; margin-top:0px">
			<p class="BoxHeading">Spread The Word!</p>
	<!-- SOMEDAY EMAIL AS A SHARE OPTION! -->
			<a href="https://twitter.com/share" class="twitter-share-button">Tweet</a>
			<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
			<script src="//platform.linkedin.com/in.js" type="text/javascript">lang: en_US</script><script type="IN/Share" data-counter="right"></script>
			<br><br><iframe src="//www.facebook.com/plugins/like.php?href=<?php echo  urlencode('http://'.$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]); ?>&amp;width=230&amp;height=80&amp;colorscheme=light&amp;layout=standard&amp;action=like&amp;show_faces=true&amp;send=true" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:230px; height:80px;" allowTransparency="true"></iframe>
		</DIV>

<!-- Search -->
		
		<DIV id="SearchArea" class="ContentBox" style="float: right; clear:right; width: 750px; margin-top:0px;">
			<p class="BoxHeading">Search <SPAN style="display:inline; font-size:50%; font-weight:400;"><a href="#" onclick="toggleAdvanced()">(Show/Hide Advanced Search)</a></SPAN></p>
			<FORM NAME="Search" ACTION="" METHOD="post">

<!-- Search (Left) -->

			<DIV class="SubSearchArea" style="float:left; clear:left;">
				<P>Search For:<BR><INPUT TYPE="text" NAME="SearchFor" MAXLENGTH="255" CLASS="SearchFormTextBox" VALUE="<?php echo $_POST['SearchFor']; ?>"><DIV class="HelpText">(e.g. Love, Logic, Children)</DIV></P>
				<DIV class="AdvancedSearch" style="<?php echo $ASSearch; ?>" id="AdvancedSearch1">
					<P>Author:<BR><INPUT TYPE="text" NAME="AuthorS" MAXLENGTH="255" CLASS="SearchFormTextBox" VALUE="<?php echo $_POST['AuthorS']; ?>"></P>
					<! -- dateselect($Name,$cssClass, $Dir, $Format, $StartYr, $EndYr, $SelMo, $SelDy, $SelYr) -->
					<P>Created Since:<BR><? echo dateselect('CreatedS','SearchFormTextBox', 0, '<m><d><y>', 1900, date('Y'),$_POST['CreatedSMonth'],$_POST['CreatedSDay'],$_POST['CreatedSYear']); ?></P>
					<P>No. Pages:<BR><INPUT TYPE="text" NAME="PagesLo" MAXLENGTH="5" CLASS="SearchFormTextBox" STYLE="width:100px" VALUE="<?php echo $_POST['PagesLo']; ?>"> to <INPUT TYPE="text" NAME="PagesHi" MAXLENGTH="5" CLASS="SearchFormTextBox" STYLE="width:100px" VALUE="<?php echo $_POST['PagesHi']; ?>"></P>
				</DIV>
			</DIV>
			
<!-- Search (Right) -->
			
			<DIV class="SubSearchArea" style="float:right; clear:right">
				<P>Search In:
				<BR><SELECT class="SearchFormTextBox" name="SearchIn">
					<OPTION VALUE="Title, Description, FullText, Tags" <?php if($_POST['SearchIn'] == 'Title, Description, FullText, Tags'){echo 'SELECTED';}?>>Everything</OPTION>
					<OPTION VALUE="Title, Description, Tags"<?php if($_POST['SearchIn'] == 'Title, Description, Tags'){echo 'SELECTED';}?>>Title, Description, Tags</OPTION>
					<OPTION VALUE="Title, FullText, Tags"<?php if($_POST['SearchIn'] == 'Title, FullText, Tags'){echo 'SELECTED';}?>>Title, Full Text, Tags</OPTION>
					<OPTION VALUE="Title, FullText"<?php if($_POST['SearchIn'] == 'Title, FullText'){echo 'SELECTED';}?>>Title, Full Text</OPTION>
					<OPTION VALUE="Title, Tags"<?php if($_POST['SearchIn'] == 'Title, Tags'){echo 'SELECTED';}?>>Title, Tags</OPTION>
					<OPTION VALUE="Title"<?php if($_POST['SearchIn'] == 'Title'){echo 'SELECTED';}?>>Title Only</OPTION>
				</SELECT><DIV class="HelpText">&nbsp;</DIV></P>
				<DIV class="AdvancedSearch" style="<?php echo $ASSearch; ?>" id="AdvancedSearch2">
					<P>Type:
					<BR><SELECT class="SearchFormTextBox" name="TypeS">
						<OPTION VALUE="">Any</OPTION>
						<OPTION VALUE="1" <?php if($_POST['TypeS'] == '1'){echo 'SELECTED';}?>>Worship</OPTION>
						<OPTION VALUE="2" <?php if($_POST['TypeS'] == '2'){echo 'SELECTED';}?>>Adult Sunday School</OPTION>
						<OPTION VALUE="3" <?php if($_POST['TypeS'] == '3'){echo 'SELECTED';}?>>Pre-Adult Sunday School</OPTION>
						<OPTION VALUE="4" <?php if($_POST['TypeS'] == '4'){echo 'SELECTED';}?>>Children's Lesson</OPTION>
						<OPTION VALUE="5" <?php if($_POST['TypeS'] == '5'){echo 'SELECTED';}?>>Camp/Retreat Activities</OPTION>
					</SELECT></P>
					<! -- dateselect($Name,$cssClass, $Dir, $Format, $StartYr, $EndYr, $SelMo, $SelDy, $SelYr) -->
					<P>Uploaded Since:<BR><? echo dateselect('Contributed','ContributeFormTextBox', 0, '<m><d><y>', 2013, date('Y'),$_POST['ContributedMonth'],$_POST['ContributedDay'],$_POST['ContributedYear']); ?></P>
					<P>Minimum Likes:<BR><INPUT TYPE="text" NAME="Likes"  MAXLENGTH="5" CLASS="SearchFormTextBox" VALUE="<?php echo $_POST['Likes']; ?>"></P>
				</DIV>
			</DIV>
			<DIV CLASS="center"><INPUT TYPE="submit" VALUE="Search" NAME="Search"></DIV>
			</FORM>

		</DIV>
<!-- Account Creation -->
<!-- Using DIVs, show either the account creation or the upload options -->
		<DIV id="CreateAccountArea" class="ContentBox" style="float: left; width: 250px;clear:left; <?php if($_SESSION['Permissions'] >= 10){ echo 'display:none;'; } else { echo 'display:block;'; }?>">
			<p class="BoxHeading">Create Account!</p>
			<p>Creating an account gives you access to:
			<ul>
				<li>Upload/Share Resources</li>
				<li>Like or Report Others' Resources</li>
				<li>Subscribe to weekly updates on new resources</li>
			</ul></p>
			<FORM NAME="CreateAccount" ACTION="" METHOD="post">
				<P>First Name:<br><INPUT TYPE="text" NAME="FirstName" MAXLENGTH="100" CLASS="CreateAccountTextBox" value="<?php echo $_POST['FirstName']; ?>"></P>
				<P>Last Name:<br><INPUT TYPE="text" NAME="LastName" MAXLENGTH="100" CLASS="CreateAccountTextBox" value="<?php echo $_POST['LastName']; ?>"></P>
				<P>Type:<br><SELECT class="CreateAccountTextBox" name="CAType" CLASS="CreateAccountTextBox">
					<OPTION VALUE="0" <?php if($_POST['CAType'] == '0'){ echo 'SELECTED'; }?>>--Select One--</OPTION>
					<OPTION VALUE="1" <?php if($_POST['CAType'] == '1'){ echo 'SELECTED'; }?>>Minister</OPTION>
					<OPTION VALUE="2" <?php if($_POST['CAType'] == '2'){ echo 'SELECTED'; }?>>Sunday School Teacher/Leader</OPTION>
					<OPTION VALUE="3" <?php if($_POST['CAType'] == '3'){ echo 'SELECTED'; }?>>Lay Leader</OPTION>
					<OPTION VALUE="4" <?php if($_POST['CAType'] == '4'){ echo 'SELECTED'; }?>>Church Member</OPTION>
					<OPTION VALUE="5" <?php if($_POST['CAType'] == '5'){ echo 'SELECTED'; }?>>Other</OPTION>
				</SELECT>
				<P>Email Address:<br><INPUT TYPE="email" NAME="Email" MAXLENGTH="100" CLASS="CreateAccountTextBox" value="<?php echo $_POST['Email']; ?>"><DIV CLASS="HelpText">(This will be your username)</DIV></P>
				<P>Password:<br><INPUT TYPE="password" NAME="Password1" maxlength="20" CLASS="CreateAccountTextBox"><DIV CLASS="HelpText">(8-20 alphanumeric characters or symbols)</DIV></P>
				<P>Verify Password:<br><INPUT TYPE="password" NAME="Password2" maxlength="20" CLASS="CreateAccountTextBox"></P>
				<p style="padding-left: 25px; text-indent: -25px;"><input type="checkbox" name="Subscribe" value="1" checked sytle="border:1px solid black;"> Please send me weekly updates of newly shared content.</p>
				<DIV CLASS="center"><INPUT TYPE="submit" VALUE="Create Account" NAME="CreateAccount"></DIV>
			</FORM>
		</DIV>
<!-- Uploads -->

		<DIV id="UploadArea" class="ContentBox" style="float: left; width: 250px;clear:left; <?php if($_SESSION['Permissions'] >= 10){ echo 'display:block;'; } else { echo 'display:none;'; }?>">
		<!--<DIV id="UploadArea" class="ContentBox" style="float: left; width: 250px;clear:left;">-->
			<p class="BoxHeading">Contribute a Resource!</p>
			<FORM NAME="Contribute" ACTION="" METHOD="post"  ENCTYPE="multipart/form-data">
				<P><INPUT TYPE="file" NAME="File"><DIV CLASS="HelpText">(.txt,.pdf,.docx,.doc supported)</DIV></P>
				<P>Document Title:<br><input TYPE="text" NAME="Title" value="<?php echo $_POST['Title']; ?>" class="ContributeFormTextBox" maxlenght="255"></P>
				<P>Description:<br>
				<TEXTAREA NAME="Description" class="ContributeFormTextBox" maxlength="255" style="height:100px"><?php echo $_POST['Description']; ?></TEXTAREA></P>
				<P>Author:<br><input TYPE="text" NAME="Author" value="<?php echo $_POST['Author']; ?>" class="ContributeFormTextBox" maxlength="100"></P>
				<! -- dateselect($Name,$cssClass, $Dir, $Format, $StartYr, $EndYr, $SelMo, $SelDy, $SelYr) -->
				<P>Created:<br><? echo dateselect('Created','ContributeFormTextBox', 0, '<m><d><y>', 1900, date('Y'),$_POST['CreatedMonth'],$_POST['CreatedDay'],$_POST['CreatedYear']); ?></P>
				<P>Type:<br>
				<SELECT class="ContributeFormTextBox" name="Type">
					<OPTION value="0">-- Select One --</OPTION>
					<OPTION value="1" <?php if($_POST['Type'] == '1'){ echo 'SELECTED'; }?>>Worship</OPTION>
					<OPTION VALUE="2" <?php if($_POST['Type'] == '2'){ echo 'SELECTED'; }?>>Adult Sunday School</OPTION>
					<OPTION VALUE="3" <?php if($_POST['Type'] == '3'){ echo 'SELECTED'; }?>>Pre-Adult Sunday School</OPTION>
					<OPTION VALUE="4" <?php if($_POST['Type'] == '4'){ echo 'SELECTED'; }?>>Worship Children's Lesson</OPTION>
					<OPTION VALUE="5" <?php if($_POST['Type'] == '5'){ echo 'SELECTED'; }?>>Camp/Retreat Activities</OPTION>
				</SELECT>
				</P>
				<P>Tags:<br><input TYPE="text" NAME="Tags" value="<?php echo $_POST['Tags']; ?>" class="ContributeFormTextBox"><div class="HelpText" maxlenght="255">(e.g. Revised Common Lectionary, Advent, Matthew 4:18-25)</div></P>
				<P>Pages:<br><input TYPE="text" NAME="Pages" value="<?php echo $_POST['Pages']; ?>"  class="ContributeFormTextBox" maxlenght="5"></P>
				<INPUT TYPE="HIDDEN" NAME="MAX_FILE_SIZE" VALUE="<php echo _MAX_FILE_SIZE_; ?>">
				<DIV CLASS="center"><INPUT TYPE="submit" VALUE="Upload" NAME="Upload"></DIV>
				<P>By uploading I certify I have the authority/permission to share this resource.</P>
			</FORM>
		</DIV>

<!-- Results -->

		<DIV id="ResultsArea" class="ContentBox" style="float: right; clear:right; width: 750px;">
			<?php echo $ResultsTableHTML; ?>
		</DIV>



</DIV>
</BODY>
</HTML>