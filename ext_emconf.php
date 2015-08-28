<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "festatus".
 *
 * Auto generated 11-03-2013 21:42
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'FE Status report',
	'description' => 'Adds a simple check to the reports module to make sure your FE is available by checking FE header status and TYPO3 conf vars. Email reports via default scheduler task.',
	'category' => 'misc',
	'shy' => 0,
	'version' => '0.0.6',
	'dependencies' => 'reports',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'beta',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Felix Nagel',
	'author_email' => 'info@felixnagel.com',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.5.0-6.2.99',
			'reports' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'suggests' => array(
	),
);

?>