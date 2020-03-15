<?php
/*
 *	Made by Samerton
 *  https://github.com/NamelessMC/Nameless/
 *  NamelessMC version 2.0.0-pr7
 *
 *  License: MIT
 *
 *  VotingPlugin module - vote page
 */

// Always define page name
define('PAGE', 'vote');
$page_title = $votingplugin_language->get('language', 'vote');
require_once(ROOT_PATH . '/core/templates/frontend_init.php');

// Get message
$vote_message = $queries->getWhere("vote_settings", array("name", "=", "vote_message"));
$vote_message = $vote_message[0]->value;

// Is vote message empty?
if(!empty($vote_message)){
	$message_enabled = true;
}

// Get sites from database
$sites = $queries->getWhere("vote_sites", array("id", "<>", 0));

$sites_array = array();
foreach($sites as $site){
    $sites_array[] = array(
        'name' => Output::getClean($site->name),
        'site' => Output::getClean($site->site),
    );
}

VotingPlugin_Module::configCheck($user, $votingplugin_language);

if(defined('VOTING_PLUGIN')){
	// Get ordering
	if(isset($_GET['order'])){
		switch($_GET['order']){
			case 'all':
				$order = 'AllTimeTotal';
				break;
			case 'daily':
				$order = 'DailyTotal';
				break;
			case 'weekly':
				$order = 'WeeklyTotal';
				break;
			default:
				$order = 'MonthTotal';
				break;
		}
	} else {
		$order = 'MonthTotal';
	}

	$cache->setCache('votingplugin_cache');

	if($cache->isCached('votes_' . $order)){
		$results = $cache->retrieve('votes_' . $order);

	} else {
		require(ROOT_PATH . '/modules/VotingPlugin/config.php');

		try {
			// Connect
			$mysqli = new mysqli($voting_plugin['host'], $voting_plugin['user'], $voting_plugin['password'], $voting_plugin['database'], $voting_plugin['port']);

			if(mysqli_connect_errno()){
				$smarty->assign('ERROR', 'Connection failed: ' . mysqli_connect_error());
			} else {
				$table = $mysqli->real_escape_string($voting_plugin['table']);

				if($stmt = $mysqli->prepare("SELECT uuid, PlayerName, MonthTotal, AllTimeTotal, DailyTotal, WeeklyTotal FROM $table WHERE $order IS NOT NULL ORDER BY $order * 1 DESC LIMIT 25")){
					$stmt->execute();

					$stmt->bind_result($uuid, $name, $monthly, $alltime, $daily, $weekly);

					$results = array();
					while($stmt->fetch()){
						// Get user info
						$vote_user = new User($name);
						if($vote_user->exists()){
							$exists = true;
							$profile = URL::build('/profile/' . Output::getClean($vote_user->data()->username));
							$nickname = Output::getClean($vote_user->data()->nickname);
							$style = $user->getGroupClass($vote_user->data()->id);
							$avatar = $user->getAvatar($vote_user->data()->id);
						} else {
							$exists = false;
							$profile = null;
							$nickname = null;
							$style = null;
							$avatar = Util::getAvatarFromUUID($uuid);
						}

						$results[] = array(
							'uuid' => $uuid,
							'name' => $name,
							'nickname' => $nickname,
							'avatar' => $avatar,
							'user_style' => $style,
							'exists' => $exists,
							'profile' => $profile,
							'monthly' => $monthly,
							'alltime' => $alltime,
							'daily' => $daily,
							'weekly' => $weekly
						);
					}

					$stmt->close();

					$cache->store('votes_' . $order, $results, 120);

				} else {
					$smarty->assign('ERROR', $votingplugin_language->get('language', 'unable_to_get_data'));
					$error = true;
				}

				$mysqli->close();
			}
		} catch(Exception $e){
			$smarty->assign('ERROR', $e->getMessage());
		}
	}
} else {
	$smarty->assign('CONFIGURE', $votingplugin_language->get('language', 'please_configure_module'));
}

if(!isset($error)){
	$smarty->assign(array(
		'RESULTS' => $results,
		'USERNAME' => $votingplugin_language->get('language', 'username'),
		'DAILY_VOTES' => $votingplugin_language->get('language', 'daily_votes'),
		'WEEKLY_VOTES' => $votingplugin_language->get('language', 'weekly_votes'),
		'MONTHLY_VOTES' => $votingplugin_language->get('language', 'monthly_votes'),
		'ALL_TIME_VOTES' => $votingplugin_language->get('language', 'all_time_votes'),
		'TOP_VOTERS' => $votingplugin_language->get('language', 'top_voters'),
		'THIS_MONTH' => $votingplugin_language->get('language', 'this_month'),
		'THIS_WEEK' => $votingplugin_language->get('language', 'this_week'),
		'TODAY' => $votingplugin_language->get('language', 'today'),
		'ALL_TIME' => $votingplugin_language->get('language', 'all_time'),
		'THIS_MONTH_LINK' => URL::build('/vote/', 'order=monthly'),
		'THIS_WEEK_LINK' => URL::build('/vote/', 'order=weekly'),
		'TODAY_LINK' => URL::build('/vote/', 'order=daily'),
		'ALL_TIME_LINK' => URL::build('/vote/', 'order=all'),
		'ORDER' => $votingplugin_language->get('language', 'order'),
		'VOTE_SITES' => $votingplugin_language->get('language', 'vote_sites'),
		'MESSAGE_ENABLED' => $message_enabled,
		'MESSAGE' => Output::getClean($vote_message),
		'VOTE_SITES_LIST' => $sites_array,
	));
}

// Load modules + template
Module::loadPage($user, $pages, $cache, $smarty, array($navigation, $cc_nav, $mod_nav), $widgets);

$page_load = microtime(true) - $start;
define('PAGE_LOAD_TIME', str_replace('{x}', round($page_load, 3), $language->get('general', 'page_loaded_in')));

$template->onPageLoad();

$smarty->assign('WIDGETS', $widgets->getWidgets());

require(ROOT_PATH . '/core/templates/navbar.php');
require(ROOT_PATH . '/core/templates/footer.php');

// Display template
$template->displayTemplate('votingplugin/vote.tpl', $smarty);
