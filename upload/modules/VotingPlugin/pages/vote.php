<?php 
/*
 *	Made by Samerton
 *  https://github.com/NamelessMC/Nameless/
 *  NamelessMC version 2.0.0-pr2
 *
 *  License: MIT
 *
 *  VotingPlugin index page
 */

// Always define page name
define('PAGE', 'vote');
?>

<!DOCTYPE html>
<html lang="<?php echo (defined('HTML_LANG') ? HTML_LANG : 'en'); ?>">
  <head>
    <!-- Standard Meta -->
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
	<meta name="description" content="View top voters and a list of vote sites for the <?php echo $sitename; ?> community" />

    <!-- Site Properties -->
	<?php 
	$title = $votingplugin_language->get('language', 'vote');
	require('core/templates/header.php'); 
	?>
  
  </head>

  <body>
    <?php 
	require('core/templates/navbar.php'); 
	require('core/templates/footer.php'); 
	
	if(defined('VOTING_PLUGIN')){
		try {
			// Connect
			$mysqli = new mysqli($voting_plugin['host'], $voting_plugin['user'], $voting_plugin['password'], $voting_plugin['database'], $voting_plugin['port']);
			
			if(mysqli_connect_errno()){
				$smarty->assign('ERROR', 'Connection failed: ' . mysqli_connect_error());
			} else {
				$table = $mysqli->real_escape_string($voting_plugin['table']);
				
				// Get ordering
				if(isset($_GET['order'])){
					switch($_GET['order']){
						case 'all':
							$order = 'AllTimeTotal';
							$table_order = 4;
						break;
						case 'daily':
							$order = 'DailyTotal';
							$table_order = 1;
						break;
						case 'weekly':
							$order = 'WeeklyTotal';
							$table_order = 2;
						break;
						default:
							$order = 'MonthTotal';
							$table_order = 3;
						break;
					}
				} else {
					$order = 'MonthTotal';
					$table_order = 3;
				}

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
					
					$vote_sites = $queries->getWhere('vote_sites', array('id', '<>', 0));

					$smarty->assign(array(
						'RESULTS' => $results,
						'VOTE_SITES' => $votingplugin_language->get('language', 'vote_sites'),
						'VOTE_SITES_LIST' => $vote_sites,
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
						'ORDER' => $votingplugin_language->get('language', 'order')
					));
				} else {
					$smarty->assign('ERROR', $votingplugin_language->get('language', 'unable_to_get_data'));
				}
				
				$mysqli->close();
			}
		} catch(Exception $e){
			$smarty->assign('ERROR', $e->getMessage());
		}
	} else {
		$smarty->assign('CONFIGURE', 'Please configure the module in the modules/VotingPlugin/config.php file first!');
	}

	// Load Smarty template
	$smarty->display('custom/templates/' . TEMPLATE . '/votingplugin/vote.tpl');

	require('core/templates/scripts.php'); 
	?>
	<script src="<?php if(defined('CONFIG_PATH')) echo CONFIG_PATH . '/'; else echo '/'; ?>core/assets/plugins/dataTables/jquery.dataTables.min.js"></script>
	<script src="<?php if(defined('CONFIG_PATH')) echo CONFIG_PATH . '/'; else echo '/'; ?>core/assets/plugins/dataTables/dataTables.bootstrap4.min.js"></script>
	
	<script type="text/javascript">
        $(document).ready(function() {
            $('.dataTables-topList').dataTable({
                responsive: true,
				language: {
					"lengthMenu": "<?php echo $language->get('table', 'display_records_per_page'); ?>",
					"zeroRecords": "<?php echo $language->get('table', 'nothing_found'); ?>",
					"info": "<?php echo $language->get('table', 'page_x_of_y'); ?>",
					"infoEmpty": "<?php echo $language->get('table', 'no_records'); ?>",
					"infoFiltered": "<?php echo $language->get('table', 'filtered'); ?>",
					"search": "<?php echo $language->get('general', 'search'); ?> "
				},
				bPaginate: false,
				bFilter: false,
				bInfo: false,
				pageLength: 25,
				order: [[<?php echo $table_order; ?>, 'desc']]
            });
		});
	</script>
  </body>
</html>