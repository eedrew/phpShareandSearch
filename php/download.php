<?php
/**
 * Stream Download
 *
 * This file streams the requested file as a dowload from the database
 * It also logs the download by username (or IP address) (since you don't have to be logged in to retrieve files
 * This technically means some users could be counted twice as unique downloads when they really aren't, but this seems minor.
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
 *	09/17/2013 - Added DocBlock
 */
require_once './includes/sessionmgt.php';
require_once './includes/databasefuncs.php';

if (!EasyCon()){
	//If the connection fails, tell the user and die.
	echo "Error: No connection to to Database. ErrNo: " . mysqli_connect_errno() . ': ' . mysqli_connect_error();
	error_log("Application Error: Database Error: '". mysqli_connect_errno() . ': ' . mysqli_connect_error()."' in $_SERVER[SCRIPT_NAME]");
	exit(1);
} else {
	$SafeUID = EasyEscape($_GET['UID']);
	$SafeIP = EasyEscape($_SERVER['REMOTE_ADDR']);
	//If the connecton was successful, write the query to get the data from this resource (as long as it isn't hidden)
	$sqlquery = "SELECT * FROM Content WHERE UID = '$SafeUID' AND Hidden = 0";
	if(EasyQ($sqlquery)){
		//If they query was successful, see if we got a result.  There should only ever be one.
		if(CountRows() >= 1){
			//Pull the data out of the $result handle.
			$row = EasyRow();
			$downloads = $row['Downloads'] + 1; // We'll need to later to update the number of downloads count.
			//Start the header file to the user.
			header('Content-Disposition: attachment; filename="'.$row['FileName'].'"');
			header("Content-type: ".$row['MIMEType']);
			//Squirt out the data.
			echo $row['FileContents'];
			//Free the result set.
			EasyFree();
			
			//Next see if the user has already downloaded this file before.
			$sqlquery = "SELECT * FROM DownloadHistory WHERE C_UID = '$SafeUID' AND (IPAddress = '$SafeIP' OR U_UID = '".EasyEscape($_SESSION[UID])."')";
			if(EasyQ($sqlquery)){
			//If the user hasn't, then make an entry into the database showing that they downloaded it now and update the count.
				if(CountRows() < 1){
					//First add an entry to the downloads history.
					$sqlquery = "INSERT INTO DownloadHistory (C_UID, IPAddress, U_UID, DateDownloaded) VALUES ('$SafeUID', '$SafeIP', '$_SESSION[UID]', '" . date("Y-m-d") . "')";
					EasyInsUpd($sqlquery);
					//Then increment the downloads field in the database for this resource.
					$sqlquery = "UPDATE Content SET Downloads='$downloads' WHERE UID='$SafeUID' LIMIT 1";
					EasyInsUpd($sqlquery);
				}
				EasyFree();
			}
			exit;
		} else {
			//If there were no rows returned, then they must be referencing a non-existant file or one that has been hidden.  Tell them and die.
			echo "File Not Found.  Please press 'back'.";
			error_log("Application Error: File Download Failed (not found): '".$_GET['UID']."' in $_SERVER[SCRIPT_NAME]");
			exit(1);
		}
	} else {
		//If the query fails, provide the error to the user.
		echo "Error: Database Error: " . EasyQErr();
		error_log("Application Error: Database Error: '".EasyQErr()."' in $_SERVER[SCRIPT_NAME]");
		exit(1);
	}
}