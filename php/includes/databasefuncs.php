<?php
/**
 * Several Database Functions
 *
 * ALL BUT ONE of these can be depreciated if all source files are updated to use object oriented mysqli
 * Function List:
 * EasyCON()		-- Establishes a Connection to the database.
 * EasyEscape()		-- Escapes a string assuming $CON as the CONnection.
 * ArrayEscape()	-- Escapes a whole array (like $_POST) assuming $CON as the Connection.
 * class fs_mysqli extends mysqli
 *		function fs_real_escape_array($arr)		-- Escapes an entire array in one shot.
 * EasyQ()			-- Runs a query and puts the result in $RES
 * EasyInsUpd()		-- Runs a query that shouldn't have a result.
 * EasyRow()		-- Returns one associative row from $RES.
 * EasyClose()		-- Closes the database connection, keeps us from having to have $CON be a global everywhere.
 * EasyCountRows()	-- Returns the number of rows in $RES - Keeps $RES or $RES_NUM_ROW from having to be globals.
 * EasyFree()		-- Free's the result set $RES - Keeps us from having to have $RES be a global everywhere.
 * EasyQErr()		-- Returns Query Errors, keeps $CON from having to be a global.
 * EasyAffRows()	-- Returns the number of affected rows from an EasyInsUpd() query.
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

require_once 'constants.php';
require_once 'stdlib1_0.php';

// Establishes a Connection to the database.
function EasyCON(){
	global $Message;
	global $CON;
	$CON = mysqli_connect(_DBASE_HOST_, _DBASE_USERNAME_, _DBASE_PASSWORD_, _DBASE_NAME_);
	if (mysqli_Connect_errno()){
		Mess("Error: No Connection to to Database. ErrNo: " . mysqli_connect_errno() . ' : ' . mysqli_connect_error());
		return FALSE;
	} else {
		return TRUE;
	}
}

// Escapes a single variable assuming $CON as connection to database.
function EasyEscape($var){
	global $CON;
	return mysqli_real_escape_string($CON, $var);
}

// Takes in an array and returns an array that is fully MYSQLI Escaped (Proceedural Version... Use with EasyCon())
function ArrayEscape($arr){
	foreach($arr as &$val){
		$val = EasyEscape($val);
	}
	return $arr;
}

// Extends the mysqli class to include an array escape function... why in the world didn't PHP5 provide us this?
class fs_mysqli extends mysqli {
	function fs_real_escape_array($arr){
		foreach($arr as &$val){
			$val = $this->real_escape_string($val);
		}
		return $arr;
	}
}

//	Does a SQL query assuming $CON from and input SQL string and returns TRUE
//	if successful or FALSE if unsuccessful.  Also sets $RES_NUM_ROWs to the number
//	of rows returned.
function EasyQ($sql){
	global $CON;
	global $RES;
	global $RES_NUM_ROW;
	$RES_NUM_ROW = 0;
	if($RES = mysqli_query($CON,$sql)){
		$RES_NUM_ROW = mysqli_num_rows($RES);
		Return TRUE;
	} else {
		Return FALSE;
	}
}
//Don't Forget:
//	EasyFree();
//	EasyClose();

//For doing inserts and updates
function EasyInsUpd($sql){
	global $CON;
	global $RES;
	if(mysqli_query($CON,$sql)){
		Return TRUE;
	} else {
		Return FALSE;
	}
}

//Peels a row off the result and returns the associative array.
function EasyRow(){
	global $RES;
	return mysqli_fetch_assoc($RES);
}

//Closes the database connection.
function EasyClose(){
	global $CON;
	mysqli_close($CON);
}

//Counts the number of rows in the result set.
function CountRows(){
	global $RES;
	Return mysqli_num_rows($RES);
}

//Free's the result set.
function EasyFree(){
	global $RES;
	mysqli_free_result($RES);
}

//Returns the error message if there was one.
function EasyQErr(){
	global $CON;
	return mysqli_errno($CON).": ".mysqli_error($CON);
}

//Returns the number of rows effected on and update/insert/delete.
function EasyAffRows(){
	global $CON;
	return mysqli_affected_rows($CON);
}
?>