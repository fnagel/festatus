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
	'description' => 'Add some frontend related checks to the reports module: check multiple TYPO3 conf vars and test frontend status. Email reports via default scheduler task.',
	'category' => 'misc',
	'version' => '1.0.0',
	'state' => 'beta',
	'uploadfolder' => 0,
	'createDirs' => '',
	'clearcacheonload' => 0,
	'author' => 'Felix Nagel',
	'author_email' => 'info@felixnagel.com',
	'constraints' => array(
		'depends' => array(
			'typo3' => '6.2.0-7.6.99',
			'reports' => '',
		),
		'conflicts' => array(),
		'suggests' => array(),
	),
);