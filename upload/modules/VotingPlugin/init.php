<?php 
/*
 *	Made by Samerton
 *  https://github.com/NamelessMC/Nameless/
 *  NamelessMC version 2.0.0-pr2
 *
 *  License: MIT
 *
 *  VotingPlugin initialisation file
 */

if(!file_exists('modules/VotingPlugin/config.php')){
	try {
		$vp_config = 	'<?php' . PHP_EOL .
					'$voting_plugin = array(' . PHP_EOL .
					'\'host\' => \'\',' . PHP_EOL .
					'\'port\' => 3306,' . PHP_EOL .
					'\'user\' => \'\',' . PHP_EOL .
					'\'database\' => \'\',' . PHP_EOL .
					'\'password\' => \'\',' . PHP_EOL .
					'\'table\' => \'VotingPlugin_Users\'' . PHP_EOL .
					');' . PHP_EOL;

		$fp = fopen('modules/VotingPlugin/config.php', 'wb');
		fwrite($fp, $vp_config);
		fclose($fp);
		
		// Create database table for vote sites
		if(!$queries->tableExists('vote_sites'))
			$queries->createTable('vote_sites', ' `id` int(11) NOT NULL AUTO_INCREMENT, `site` varchar(512) NOT NULL, `name` varchar(64) NOT NULL, PRIMARY KEY (`id`)', 'ENGINE=InnoDB DEFAULT CHARSET=latin1');

	} catch(Exception $e){
		// Error creating config, probably perms
	}
} else {
	require_once('modules/VotingPlugin/config.php');
	if(!empty($voting_plugin['host']))
		define('VOTING_PLUGIN', true);
}
 
$votingplugin_language = new Language(ROOT_PATH . '/modules/VotingPlugin/language', LANGUAGE);
 
// Define URLs which belong to this module
$pages->add('VotingPlugin', '/vote', 'pages/vote.php');
$pages->add('VotingPlugin', '/admin/vote', 'pages/admin.php');

// Add link to admin sidebar
if(!isset($admin_sidebar)) $admin_sidebar = array();
$admin_sidebar['vote'] = array(
	'title' => $votingplugin_language->get('language', 'vote'),
	'url' => URL::build('/admin/vote')
);

// Check cache for navbar link order
$cache->setCache('navbar_order');
if(!$cache->isCached('vote_order')){
    $vote_order = 5;
    $cache->store('vote_order', 5);
} else {
    $vote_order = $cache->retrieve('vote_order');
}
$navigation->add('vote', $votingplugin_language->get('language', 'vote'), URL::build('/vote'), 'top', null, $vote_order);