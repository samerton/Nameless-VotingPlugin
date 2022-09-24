<?php 
/*
 *	Made by Samerton
 *  https://github.com/NamelessMC/Nameless/
 *  NamelessMC version 2.0.2
 *
 *  License: MIT
 *
 *  VotingPlugin module
 */

class VotingPlugin_Module extends Module {
	private $_vote_language, $_user;

	public function __construct($vote_language, $pages, $user){
		$this->_vote_language = $vote_language;
		$this->_user = $user;

		$name = 'VotingPlugin';
		$author = '<a href="https://samerton.me" target="_blank" rel="nofollow noopener">Samerton</a>';
		$module_version = '1.0.3';
		$nameless_version = '2.0.2';

		parent::__construct($this, $name, $author, $module_version, $nameless_version);

		// Define URLs which belong to this module
		$pages->add('VotingPlugin', '/vote', 'pages/vote.php');
		$pages->add('VotingPlugin', '/panel/vote', 'pages/panel.php');
	}

	public function onInstall(){
		// Queries
		$queries = new Queries();

		try {
			$queries->createTable("vote_settings", " `id` int(11) NOT NULL AUTO_INCREMENT, `name` varchar(20) NOT NULL, `value` varchar(2048) NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");

			// Insert data
			$queries->create('vote_settings', array(
				'name' => 'vote_message',
				'value' => 'You can manage this vote module in StaffCP -> Vote'
			));
			$queries->create('vote_sites', array(
				'site' => 'https://mccommunity.net/',
				'name' => 'MCCommunity (Example)'
			));
			$queries->create('vote_sites', array(
				'site' => 'http://planetminecraft.com/',
				'name' => 'PlanetMinecraft (Example)'
			));
		} catch(Exception $e){
			// Error - table probably already exists
		}

		try {
			$queries->createTable("vote_sites", " `id` int(11) NOT NULL AUTO_INCREMENT, `site` varchar(512) NOT NULL, `name` varchar(64) NOT NULL, PRIMARY KEY (`id`)", "ENGINE=InnoDB DEFAULT CHARSET=latin1");
		} catch(Exception $e){
			// Error - table probably already exists
		}

		try {
			// Update main admin group permissions
			$group = DB::getInstance()->get('groups', ['id', '=', 2])->results();
			$group = $group[0];

			$group_permissions = json_decode($group->permissions, TRUE);
			$group_permissions['admincp.vote'] = 1;

			$group_permissions = json_encode($group_permissions);
			$queries->update('groups', 2, array('permissions' => $group_permissions));
		} catch(Exception $e){
			// Error
		}

		// Config check
		self::configCheck($this->_user, $this->_vote_language, 'notice');
	}

	public function onUninstall(){
		// No actions necessary
	}

	public function onEnable(){
		// Config check
		self::configCheck($this->_user, $this->_vote_language, 'notice');
	}

	public function onDisable(){
		// No actions necessary
	}

	public function onPageLoad($user, $pages, $cache, $smarty, $navs, $widgets, $template){
		// Permissions
		PermissionHandler::registerPermissions('VotingPlugin', array(
			'admincp.vote' => $this->_vote_language->get('language', 'vote')
		));

		// Navigation link location
		$cache->setCache('nav_location');
		if(!$cache->isCached('voting_plugin_location')){
			$link_location = 1;
			$cache->store('voting_plugin_location', 1);
		} else {
			$link_location = $cache->retrieve('voting_plugin_location');
		}

		// Navigation icon
		$cache->setCache('navbar_icons');
		if(!$cache->isCached('vote_icon')) {
			$icon = '';
		} else {
			$icon = $cache->retrieve('vote_icon');
		}

		// Navigation order
		$cache->setCache('navbar_order');
		if(!$cache->isCached('vote_order')){
			// Create cache entry now
			$vote_order = 3;
			$cache->store('vote_order', 3);
		} else {
			$vote_order = $cache->retrieve('vote_order');
		}

		switch($link_location){
			case 1:
				// Navbar
				$navs[0]->add('vote', $this->_vote_language->get('language', 'vote'), URL::build('/vote'), 'top', null, $vote_order, $icon);
				break;
			case 2:
				// "More" dropdown
				$navs[0]->addItemToDropdown('more_dropdown', 'vote', $this->_vote_language->get('language', 'vote'), URL::build('/vote'), 'top', null, $icon, $vote_order);
				break;
			case 3:
				// Footer
				$navs[0]->add('vote', $this->_vote_language->get('language', 'vote'), URL::build('/vote'), 'footer', null, $vote_order, $icon);
				break;
		}

		if(defined('BACK_END')){
			self::configCheck($user, $this->_vote_language, 'notice');

			if($user->hasPermission('admincp.vote')){
				$cache->setCache('panel_sidebar');
				if(!$cache->isCached('voting_plugin_order')){
					$order = 19;
					$cache->store('voting_plugin_order', 21);
				} else {
					$order = $cache->retrieve('voting_plugin_order');
				}

				if(!$cache->isCached('voting_plugin_icon')){
					$icon = '<i class="nav-icon fas fa-cogs"></i>';
					$cache->store('voting_plugin_icon', $icon);
				} else {
					$icon = $cache->retrieve('voting_plugin_icon');
				}

				$navs[2]->add('vote_divider', mb_strtoupper($this->_vote_language->get('language', 'vote'), 'UTF-8'), 'divider', 'top', null, $order, '');
				$navs[2]->add('vote', $this->_vote_language->get('language', 'vote'), URL::build('/panel/vote'), 'top', null, ($order + 0.1), $icon);
			}
		}
	}

	public static function configCheck($user, $language, $sendNotice = false){
		// Config check
		if(!file_exists(ROOT_PATH . '/modules/VotingPlugin/config.php')){
			if(!is_writable(ROOT_PATH . '/modules/VotingPlugin')){
				if($sendNotice && $user->hasPermission('admincp.modules')){
					if($sendNotice === 'session')
						Session::flash('admin_modules_error', $language->get('language', 'config_not_writable'));
					else
						Core_Module::addNotice(URL::build('/panel/modules'), $language->get('language', 'config_not_writable'));
				}
			} else {
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

					$fp = fopen(ROOT_PATH . '/modules/VotingPlugin/config.php', 'wb');
					fwrite($fp, $vp_config);
					fclose($fp);
				} catch(Exception $e){
					// Error creating config, probably perms
				}
			}
		} else {
			require_once(ROOT_PATH . '/modules/VotingPlugin/config.php');
			if(!empty($voting_plugin['host']))
				define('VOTING_PLUGIN', true);
		}
	}
	
	public function getDebugInfo(): array {
        return [];
    }
}
