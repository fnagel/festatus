<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011-2014 Felix Nagel <info@felixnagel.com>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Hook into the backend module "Reports" checking whether there are extensions installed that conflicting with htmlArea RTE
 *
 * @version $Id: class.tx_rtehtmlarea_statusreport_conflictscheck.php $
 */
class tx_festatus_checkfrontend implements tx_reports_StatusProvider {

	/**
	 * Compiles a collection of system status checks as a status report.
	 *
	 * @see typo3/sysext/reports/interfaces/tx_reports_StatusProvider::getStatus()
	 */
	public function getStatus() {
		$reports = array();
		$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['festatus']);

		if ($extConf['check_header_status']) {
			$reports['checkHeaderStatus'] = $this->checkHeaderStatus();
		}

		if ($extConf['check_page_unavailable_force']) {
			$reports['checkPageUnavailable_force'] = $this->checkPageUnavailable_force();
		}

		if ($extConf['check_dev_ip_mask']) {
			$reports['checkDevIPmask'] = $this->checkDevIPmask();
		}

		if ($extConf['check_display_errors']) {
			$reports['checkDisplayErrors'] = $this->checkDisplayErrors();
		}

		return $reports;
	}
	/**
	 * Check if website is online
	 *
	 * @return tx_reports_reports_status_Status
	 */
	protected function checkHeaderStatus() {
		$title = 'Header Check';

		if ($this->isOnline(t3lib_div::getIndpEnv('TYPO3_SITE_URL'))) {
			$value = "Ok";
			$message = '';
			$status = tx_reports_reports_status_Status::OK;
		} else {
			$value = 'Error';
			$message = 'Status header sent was not "200 Ok"';
			$status = tx_reports_reports_status_Status::ERROR;
		}

		return t3lib_div::makeInstance('tx_reports_reports_status_Status',
			$title,
			$value,
			$message,
			$status
		);
	}

	/**
	 * Check if TYPO3_CONF_VARS for displaying error in FE
	 *
	 * @return tx_reports_reports_status_Status
	 */
	protected function checkDisplayErrors() {
		$title = 'Error display';

		// 0 = none, 1 = all, 2 = if IP matches
		if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['displayErrors'] == '1') {
			$value = "Enabled";
			$message = 'Errors will be displayed as displayErrors is configured to show all errors.';
			$status = tx_reports_reports_status_Status::ERROR;
		} elseif ($GLOBALS['TYPO3_CONF_VARS']['SYS']['displayErrors'] == '2') {
			$value = "Enabled";
			$message = 'Errors might be displayed when client IP matches devIPmask.';
			$status = tx_reports_reports_status_Status::NOTICE;
		} else {
			$value = 'Disabled';
			$message = '';
			$status = tx_reports_reports_status_Status::OK;
		}

		return t3lib_div::makeInstance('tx_reports_reports_status_Status',
			$title,
			$value,
			$message,
			$status
		);
	}

	/**
	 * Check if TYPO3_CONF_VARS if devIPmask is set
	 *
	 * @return tx_reports_reports_status_Status
	 */
	protected function checkDevIPmask() {
		$title = 'Dev IP mask';
		$devIPmask = $GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask'];

		if ($devIPmask == '*') {
			$value = "Matches all";
			$message = 'devIPmask matches all IP addresses.';
			$status = tx_reports_reports_status_Status::ERROR;
		} elseif (preg_match('#\*#', $devIPmask)) {
			$value = "Wildcard included";
			$message = 'devIPmask includes at least one catch all wildcard.';
			$status = tx_reports_reports_status_Status::NOTICE;
		} else {
			$value = 'Ok';
			$message = '';
			$status = tx_reports_reports_status_Status::OK;
		}

		return t3lib_div::makeInstance('tx_reports_reports_status_Status',
			$title,
			$value,
			$message,
			$status
		);
	}

	/**
	 * Check if TYPO3_CONF_VARS if FE us set to maintenance mode
	 *
	 * @return tx_reports_reports_status_Status
	 */
	protected function checkPageUnavailable_force() {
		$title = 'Page forced unavailable';

		if ($GLOBALS['TYPO3_CONF_VARS']['FE']['pageUnavailable_force']) {
			$value = "Enabled";
			$message = 'Page is in maintenance mode.';
			$status = tx_reports_reports_status_Status::ERROR;
		} else {
			$value = 'Disabled';
			$message = '';
			$status = tx_reports_reports_status_Status::OK;
		}

		return t3lib_div::makeInstance('tx_reports_reports_status_Status',
			$title,
			$value,
			$message,
			$status
		);
	}

	/**
	 * Checks if an domain is online
	 * taken from http://neo22s.com/check-if-url-exists-and-is-online-php/
	 *
	 * @param string $url The URL to check
	 *
	 * @return boolean
	 */
	protected function isOnline($url) {
		$url = @parse_url($url);
		if (!$url) {
			return false;
		}

		$url = array_map('trim', $url);
		$url['port'] = (!isset($url['port'])) ? 80 : (int)$url['port'];

		$path = (isset($url['path'])) ? $url['path'] : '/';
		$path .= (isset($url['query'])) ? "?$url[query]" : '';

		if (isset($url['host']) && $url['host'] != gethostbyname($url['host'])) {
			 $fp = fsockopen($url['host'], $url['port'], $errno, $errstr, 30);

			// socket not opened
			if (!$fp) {
				return false;
			}

			// socket opened
			fputs($fp, "HEAD $path HTTP/1.1\r\nHost: $url[host]\r\n\r\n");
			$headers = fread($fp, 4096);
			fclose($fp);

			// matching header
			if(preg_match('#^HTTP/.*\s+[(200)]+\s#i', $headers)){
				return true;
			} else {
				return false;
			}
		 } else {
			return false;
		}
	}
}

?>