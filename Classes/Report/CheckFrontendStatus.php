<?php

namespace TYPO3\FrontendStatus\Report;

/***************************************************************
*  Copyright notice
*
*  (c) 2011-2015 Felix Nagel <info@felixnagel.com>
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

use TYPO3\CMS\Reports\StatusProviderInterface;
use TYPO3\CMS\Reports\ExtendedStatusProviderInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Reports\Status;

/**
 * Hook into the backend module 'Reports'
 */
class CheckFrontendStatus implements StatusProviderInterface, ExtendedStatusProviderInterface {
	/**
	 * Compile environment status report
	 *
	 * @return \TYPO3\CMS\Reports\Status[]
	 */
	public function getStatus() {
		return $this->getStatusInternal();
	}

	/**
	 * Returns the detailed status of an extension or (sub)system
	 *
	 * @return \TYPO3\CMS\Reports\Status[]
	 */
	public function getDetailedStatus() {
		return $this->getStatusInternal();
	}

	/**
	 * Compiles a collection of system status checks as a status report.
	 *
	 * @return \TYPO3\CMS\Reports\Status[]
	 * @throws \TYPO3\CMS\Install\Exception
	 */
	protected function getStatusInternal() {
		$reports = array();
		$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['festatus']);

		if ($extConf['check_header_status']) {
			$reports['checkHeaderStatus'] = $this->checkHeaderStatus();
		}

		if ($extConf['check_page_unavailable_force']) {
			$reports['checkPageUnavailable_force'] = $this->checkPageUnavailable();
		}

		if ($extConf['check_dev_ip_mask']) {
			$reports['checkDevIPmask'] = $this->checkDevIpMask();
		}

		if ($extConf['check_display_errors']) {
			$reports['checkDisplayErrors'] = $this->checkDisplayErrors();
		}

		if (version_compare(TYPO3_branch, '6.2', '>=')) {
			if ($extConf['check_application_context']) {
				$reports['checkApplicationContext'] = $this->checkApplicationContext();
			}
		}

		return $reports;
	}

	/**
	 * Check if website is online
	 *
	 * @return Status
	 */
	protected function checkApplicationContext() {
		$title = 'Application context';
		$message = '';
		$status = Status::OK;
		$value = 'Ok';

		$applicationContext = GeneralUtility::getApplicationContext();
		if ($applicationContext->isProduction()) {
			if ($applicationContext !== 'Production') {
				$status = Status::INFO;
				$value = 'Info';
			}
		} else {
			$value = 'Error';
			$message = 'Application context is not a production context.';
			$status = Status::ERROR;
		}

		$message .= ' Current context is "' . (string) $applicationContext . '"';

		return $this->createStatusReport($title, $value, $message, $status);
	}

	/**
	 * Check if website is online
	 *
	 * @return Status
	 */
	protected function checkHeaderStatus() {
		$title = 'Header Check';
		$message = '';
		$status = Status::OK;

		if ($this->isOnline(GeneralUtility::getIndpEnv('TYPO3_SITE_URL'))) {
			$value = 'Ok';
		} else {
			$value = 'Error';
			$message = 'Status header sent was not "200 Ok"';
			$status = Status::ERROR;
		}

		return $this->createStatusReport($title, $value, $message, $status);
	}

	/**
	 * Check if TYPO3_CONF_VARS for displaying error in FE
	 *
	 * @return Status
	 */
	protected function checkDisplayErrors() {
		$title = 'Error display';
		$message = '';
		$status = Status::OK;

		// 0 = none, 1 = all, 2 = if IP matches
		if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['displayErrors'] == '1') {
			$value = 'Enabled';
			$message = 'Errors will be displayed as displayErrors is configured to show all errors.';
			$status = Status::ERROR;
		} elseif ($GLOBALS['TYPO3_CONF_VARS']['SYS']['displayErrors'] == '2') {
			$value = 'Enabled';
			$message = 'Errors might be displayed when client IP matches devIPmask.';
			$status = Status::NOTICE;
		} else {
			$value = 'Disabled';
		}

		return $this->createStatusReport($title, $value, $message, $status);
	}

	/**
	 * Check if TYPO3_CONF_VARS if devIPmask is set
	 *
	 * @return Status
	 */
	protected function checkDevIpMask() {
		$title = 'Dev IP mask';
		$message = '';
		$status = Status::OK;
		$devIpMmask = $GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask'];

		if ($devIpMmask == '*') {
			$value = 'Matches all';
			$message = 'devIPmask matches all IP addresses.';
			$status = Status::ERROR;
		} elseif (preg_match('#\*#', $devIpMmask)) {
			$value = 'Wildcard included';
			$message = 'devIPmask includes at least one catch all wildcard.';
			$status = Status::NOTICE;
		} else {
			$value = 'Ok';
		}

		return $this->createStatusReport($title, $value, $message, $status);
	}

	/**
	 * Check if TYPO3_CONF_VARS if FE us set to maintenance mode
	 *
	 * @return Status
	 */
	protected function checkPageUnavailable() {
		$title = 'Page forced unavailable';
		$message = '';
		$status = Status::OK;

		if ($GLOBALS['TYPO3_CONF_VARS']['FE']['pageUnavailable_force']) {
			$value = 'Enabled';
			$message = 'Page is in maintenance mode.';
			$status = Status::ERROR;
		} else {
			$value = 'Disabled';
		}

		return $this->createStatusReport($title, $value, $message, $status);
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
			return FALSE;
		}

		$url = array_map('trim', $url);
		$url['port'] = (!isset($url['port'])) ? 80 : (int)$url['port'];

		if (isset($url['host']) && $url['host'] != gethostbyname($url['host'])) {
			$fp = fsockopen($url['host'], $url['port'], $errno, $errstr, 30);

			// socket not opened
			if (!$fp) {
				return FALSE;
			}

			// socket opened
			fputs($fp, 'HEAD $path HTTP/1.1\r\nHost: $url[host]\r\n\r\n');
			$headers = fread($fp, 4096);
			fclose($fp);

			// matching header
			if (preg_match('#^HTTP/.*\s+[(200)]+\s#i', $headers)) {
				return TRUE;
			} else {
				return FALSE;
			}
		}

		return FALSE;
	}

	/**
	 * @param string $title
	 * @param string $value
	 * @param string $message
	 * @param int $status
	 *
	 * @return \TYPO3\CMS\Reports\Status
	 */
	protected function createStatusReport($title, $value, $message, $status) {
		return GeneralUtility::makeInstance('TYPO3\\CMS\\Reports\\Status', $title, $value, $message, $status);
	}
}
