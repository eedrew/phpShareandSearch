<?php
/**
 * Constants.PHP
 *
 * Perhaps the most important page in phpShare&Search... this file is where most customization is done.
 * Editing these constants changes most everything about how phpShare&Search works.
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
 *	09/17/2013 	- Added Docblock
 *				- Added _MAX_EMAILS_PER_TIMEFRAME_ in place of a hard coded 500 email limit
 */

define("_GOOGLE_ANALYTICS_CODE_","XX-99999999-9");								// Google special code
define("_GOOGLE_ANALYTICS_URL_","yourdomain.com");								// Google analytics URL

// Used in various locations to make the code portable
define("_PRETTY_TITLE_",		"phpShare&Search Demo");							//Name of the website
define("_TAGLINE_",				"An online resource sharing tool available open source!");	// Tagline for the website
define("_ADMIN_EMAIL_",			"siebert.public@gmail.com");						// Administrative email address, used as from address in email functions and also supplied to users occasionally with error messages.
define("_ROOT_PATH_",			"/home/ranemgt/public_html/snsdemo");	//UNIX location of the root
define("_ROOT_URL_",			"snsdemo.oneinternetlane.com");						// Base URL of the website or application.
define("_IMG_DIR_",				'/imgs/');											// Location of all images.
define("_VISITORS_MESSAGE_",	'This example message gets displayed to non-logged in users');		// A message for new users.

// Database connection information
define("_DBASE_HOST_",			"localhost");
define("_DBASE_USERNAME_",		"dbase_uname");
define("_DBASE_PASSWORD_",		"dbase_pw");											//Those are Zeros replacing the letter O.
define("_DBASE_NAME_",			"dbase_name");

// The maximum file size limits how large of resources can be uploaded to the database.
define("_MAX_FILE_SIZE_",		'104857600');										//104857600 is 10MB
define("_MAX_EMAILS_PER_TIMEFRAME_",	'500');											//Some hosts have a maximum number of emails sent per hour (or whatever timeframe).  If your server has this, enter the value here.  If it doens't, just put it a big number.

// Session variables.
define("_SESSION_LIFE_",		60*60*24*90);										//Determines how long sessions stay active for logged in users.
define("_CRYPT_SALT_",			'$6$FSSALTRQRRS6TEEN');								//CRYPT_SHA512 - SHA-512 hash with a sixteen character salt prefixed with $6$. If the salt string starts with 'rounds=<N>$', the numeric value of N is used to indicate how many times the hashing loop should be executed, much like the cost parameter on Blowfish. The default number of rounds is 5000, there is a minimum of 1000 and a maximum of 999,999,999. Any selection of N outside this range will be truncated to the nearest limit.

// Programmability Constants
define("_REPORTS_TO_BLOCK_",	5);													//Number of reports before a user is blocked.
?>