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
    'version' => '1.1.1-dev',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'author' => 'Felix Nagel',
    'author_email' => 'info@felixnagel.com',
    'constraints' => array(
        'depends' => array(
            'php' => '5.4.0-7.0.99',
            'typo3' => '6.2.0-8.99.99',
            'reports' => '',
        ),
        'conflicts' => array(),
        'suggests' => array(),
    ),
);
