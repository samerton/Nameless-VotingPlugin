<?php
/*
 *	Made by Samerton
 *  https://github.com/NamelessMC/Nameless/
 *  NamelessMC version 2.1.1
 *
 *  License: MIT
 *
 *  VotingPlugin module - vote page
 */

/**
 * @var Cache $cache
 * @var Language $votingplugin_language
 * @var Navigation $cc_nav
 * @var Navigation $navigation
 * @var Navigation $staffcp_nav
 * @var Pages $pages
 * @var Smarty $smarty
 * @var User $user
 * @var TemplateBase $template
 * @var Widgets $widgets
 */

// Always define page name
const PAGE = 'vote';

$page_title = $votingplugin_language->get('vote');

require_once ROOT_PATH . '/core/templates/frontend_init.php';

// Get message
$vote_message = DB::getInstance()->get("vote_settings", array("name", "=", "vote_message"));

// Is vote message empty?
if ($vote_message->count() && $vote_message->first()){
    $vote_message = $vote_message->first()->value;
} else {
    $vote_message = null;
}

// Get sites from database
$sites = DB::getInstance()->get("vote_sites", array("id", "<>", 0))->results() ?? [];

$sites_array = array();
foreach ($sites as $site) {
    $sites_array[] = array(
        'name' => Output::getClean($site->name),
        'site' => Output::getClean($site->site),
    );
}

VotingPlugin_Module::configCheck($user, $votingplugin_language);

$results = [];

if (defined('VOTING_PLUGIN')) {
	// Get ordering
	if (isset($_GET['order'])) {
		switch ($_GET['order']) {
			case 'all':
				$order = 'AllTimeTotal';
				break;
			case 'daily':
				$order = 'DailyTotal';
				break;
			case 'weekly':
				$order = 'WeeklyTotal';
				break;
            case 'last_month':
                $order = 'LastMonthTotal';
                break;
			default:
				$order = 'MonthTotal';
				break;
		}
	} else {
		$order = 'MonthTotal';
	}

	$cache->setCache('votingplugin_cache');

	if ($cache->isCached('votes_' . $order)) {
		$results = $cache->retrieve('votes_' . $order);

	} else {
		require ROOT_PATH . '/modules/VotingPlugin/config.php';

        /** @var array $voting_plugin */

        try {
            $db = DB::getCustomInstance(
                $voting_plugin['host'],
                $voting_plugin['database'],
                $voting_plugin['user'],
                $voting_plugin['password'],
                $voting_plugin['port']
            );

            $query = $db->query(
                <<<SQL
                SELECT
                    uuid,
                    PlayerName,
                    MonthTotal,
                    LastMonthTotal,
                    AllTimeTotal,
                    DailyTotal,
                    WeeklyTotal
                FROM
                    {$voting_plugin['table']}
                    WHERE
                        $order IS NOT NULL
                    ORDER BY
                        $order * 1
                        DESC LIMIT 25
                SQL
            );

            if ($query->count()) {
                $integration = Integrations::getInstance()->getIntegration('Minecraft');

                foreach ($query->results() as $result) {
                    // Get user info
                    $vote_user = new IntegrationUser($integration, str_replace('-', '', $result->uuid), 'identifier');

                    if ($vote_user->exists()) {
                        $exists = true;
                        $profile = $vote_user->getUser()->getProfileURL();
                        $nickname = $vote_user->getUser()->getDisplayname();
                        $style = $vote_user->getUser()->getGroupStyle();
                        $avatar = $vote_user->getUser()->getAvatar();
                    } else {
                        $exists = false;
                        $profile = null;
                        $nickname = null;
                        $style = null;
                        $avatar = AvatarSource::getAvatarFromUUID($result->uuid);
                    }

                    $results[] = array(
                        'uuid' => $result->uuid,
                        'name' => $result->PlayerName,
                        'nickname' => $nickname,
                        'avatar' => $avatar,
                        'user_style' => $style,
                        'exists' => $exists,
                        'profile' => $profile,
                        'last_month' => $result->LastMonthTotal,
                        'monthly' => $result->MonthTotal,
                        'alltime' => $result->AllTimeTotal,
                        'daily' => $result->DailyTotal,
                        'weekly' => $result->WeeklyTotal
                    );
                }

                $cache->store('votes_' . $order, $results, 120);
            }
        } catch (PDOException $e) {
            $smarty->assign('ERROR', $votingplugin_language->get('unable_to_connect_to_database', null, ['error' => $e->getMessage()]));
        }
	}
} else {
	$smarty->assign('CONFIGURE', $votingplugin_language->get('please_configure_module'));
}

$smarty->assign(array(
    'RESULTS' => $results,
    'USERNAME' => $votingplugin_language->get('username'),
    'DAILY_VOTES' => $votingplugin_language->get('daily_votes'),
    'WEEKLY_VOTES' => $votingplugin_language->get('weekly_votes'),
    'MONTHLY_VOTES' => $votingplugin_language->get('monthly_votes'),
    'LAST_MONTHS_VOTES' => $votingplugin_language->get('last_months_votes'),
    'ALL_TIME_VOTES' => $votingplugin_language->get('all_time_votes'),
    'TOP_VOTERS' => $votingplugin_language->get('top_voters'),
    'LAST_MONTH' => $votingplugin_language->get('last_month'),
    'THIS_MONTH' => $votingplugin_language->get('this_month'),
    'THIS_WEEK' => $votingplugin_language->get('this_week'),
    'TODAY' => $votingplugin_language->get('today'),
    'ALL_TIME' => $votingplugin_language->get('all_time'),
    'LAST_MONTH_LINK' => URL::build('/vote/', 'order=last_month'),
    'THIS_MONTH_LINK' => URL::build('/vote/', 'order=monthly'),
    'THIS_WEEK_LINK' => URL::build('/vote/', 'order=weekly'),
    'TODAY_LINK' => URL::build('/vote/', 'order=daily'),
    'ALL_TIME_LINK' => URL::build('/vote/', 'order=all'),
    'ORDER' => $votingplugin_language->get('order'),
    'VOTE_SITES' => $votingplugin_language->get('vote_sites'),
    'MESSAGE_ENABLED' => !empty($vote_message),
    'MESSAGE' => $vote_message ? Output::getClean($vote_message) : '',
    'VOTE_SITES_LIST' => $sites_array,
));

// Load modules + template
Module::loadPage($user, $pages, $cache, $smarty, array($navigation, $cc_nav, $staffcp_nav), $widgets, $template);

$template->onPageLoad();

$smarty->assign('WIDGETS_LEFT', $widgets->getWidgets('left'));
$smarty->assign('WIDGETS_RIGHT', $widgets->getWidgets('right'));

require_once ROOT_PATH . '/core/templates/navbar.php';
require_once ROOT_PATH . '/core/templates/footer.php';

// Display template
$template->displayTemplate('votingplugin/vote.tpl', $smarty);
