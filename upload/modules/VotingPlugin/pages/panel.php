<?php 
/*
 *	Made by Samerton
 *  https://github.com/NamelessMC/Nameless/
 *  NamelessMC version 2.0.0-pr7
 *
 *  License: MIT
 *
 *  VotingPlugin module - panel vote settings page
 */

// Can the user view the StaffCP?
if($user->isLoggedIn()){
	if(!$user->canViewACP()){
		// No
		Redirect::to(URL::build('/'));
		die();
	}
	if(!$user->isAdmLoggedIn()){
		// Needs to authenticate
		Redirect::to(URL::build('/panel/auth'));
		die();
	}
	if(!$user->hasPermission('admincp.vote')){
		require_once(ROOT_PATH . '/403.php');
		die();
	}
} else {
	// Not logged in
	Redirect::to(URL::build('/login'));
	die();
}


define('PAGE', 'panel');
define('PARENT_PAGE', 'vote');
define('PANEL_PAGE', 'vote');
$page_title = $votingplugin_language->get('language', 'vote');
require_once(ROOT_PATH . '/core/templates/backend_init.php');

if(isset($_GET['action'])){
	switch($_GET['action']){
		case 'new':
			if(Input::exists()){
				$errors = array();
				if(Token::check(Input::get('token'))){
					// process addition of site
					$validate = new Validate();
					$validation = $validate->check($_POST, array(
						'vote_site_name' => array(
							'required' => true,
							'min' => 2,
							'max' => 64
						),
						'vote_site_url' => array(
							'required' => true,
							'min' => 10,
							'max' => 255
						)
					));

					if($validation->passed()){
						// input into database
						try {
							$queries->create('vote_sites', array(
								'site' => Output::getClean(Input::get('vote_site_url')),
								'name' => Output::getClean(Input::get('vote_site_name'))
							));
							Session::flash('staff_vote', $votingplugin_language->get('language', 'site_created_successfully'));
							Redirect::to(URL::build('/panel/vote'));
							die();
						} catch(Exception $e){
							$errors[] = $e->getMessage();
						}
					} else {
						foreach($validation->errors() as $item){
							if(strpos($item, 'is required') !== false){
								if(strpos($item, 'vote_site_name') !== false)
									$errors[] = $votingplugin_language->get('language', 'site_name_required');

								else if(strpos($item, 'vote_site_url') !== false)
									$errors[] = $votingplugin_language->get('language', 'site_url_required');
							} else if(strpos($item, 'minimum') !== false){
								if(strpos($item, 'vote_site_name') !== false)
									$errors[] = $votingplugin_language->get('language', 'site_name_minimum');

								else if(strpos($item, 'vote_site_url') !== false)
									$errors[] = $votingplugin_language->get('language', 'site_url_minimum');
							} else if(strpos($item, 'maximum') !== false){
								if(strpos($item, 'vote_site_name') !== false)
									$errors[] = $votingplugin_language->get('language', 'site_name_maximum');

								else if(strpos($item, 'vote_site_url') !== false)
									$errors[] = $votingplugin_language->get('language', 'site_url_maximum');
							}
						}
					}
				} else {
					$errors[] = $language->get('general', 'invalid_token');
				}
			}

			$smarty->assign(array(
				'NEW_SITE' => $votingplugin_language->get('language', 'new_vote_site'),
				'BACK' => $language->get('general', 'back'),
				'BACK_LINK' => URL::build('/panel/vote'),
				'VOTE_SITE_NAME' => $votingplugin_language->get('language', 'site_name'),
				'VOTE_SITE_URL' => $votingplugin_language->get('language', 'site_url'),
			));

			$template_file = 'votingplugin/vote_new.tpl';
			break;
		case 'edit':
			// Get page
			if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
				Redirect::to(URL::build('/panel/vote'));
				die();
			}
			$site = $queries->getWhere('vote_sites', array('id', '=', $_GET['id']));
			if(!count($site)){
				Redirect::to(URL::build('/panel/vote'));
				die();
			}
			$site = $site[0];

			if(Input::exists()){
				$errors = array();
				if(Token::check(Input::get('token'))){
					// process addition of site
					$validate = new Validate();
					$validation = $validate->check($_POST, array(
						'vote_site_name' => array(
							'required' => true,
							'min' => 2,
							'max' => 64
						),
						'vote_site_url' => array(
							'required' => true,
							'min' => 10,
							'max' => 255
						)
					));

					if($validation->passed()){
						// input into database
						try {
							$queries->update('vote_sites', $site->id, array(
								'site' => Output::getClean(Input::get('vote_site_url')),
								'name' => Output::getClean(Input::get('vote_site_name'))
							));
							Session::flash('staff_vote', $votingplugin_language->get('language', 'site_edited_successfully'));
							Redirect::to(URL::build('/panel/vote'));
							die();
						} catch(Exception $e){
							$errors[] = $e->getMessage();
						}
					} else {
						foreach($validation->errors() as $item){
							if(strpos($item, 'is required') !== false){
								if(strpos($item, 'vote_site_name') !== false)
									$errors[] = $votingplugin_language->get('language', 'site_name_required');

								else if(strpos($item, 'vote_site_url') !== false)
									$errors[] = $votingplugin_language->get('language', 'site_url_required');
							} else if(strpos($item, 'minimum') !== false){
								if(strpos($item, 'vote_site_name') !== false)
									$errors[] = $votingplugin_language->get('language', 'site_name_minimum');

								else if(strpos($item, 'vote_site_url') !== false)
									$errors[] = $votingplugin_language->get('language', 'site_url_minimum');
							} else if(strpos($item, 'maximum') !== false){
								if(strpos($item, 'vote_site_name') !== false)
									$errors[] = $votingplugin_language->get('language', 'site_name_maximum');

								else if(strpos($item, 'vote_site_url') !== false)
									$errors[] = $votingplugin_language->get('language', 'site_url_maximum');
							}
						}
					}
				} else {
					$errors[] = $language->get('general', 'invalid_token');
				}
			}

			$smarty->assign(array(
				'EDIT_SITE' => $votingplugin_language->get('language', 'edit_site'),
				'BACK' => $language->get('general', 'back'),
				'BACK_LINK' => URL::build('/panel/vote'),
				'VOTE_SITE_NAME' => $votingplugin_language->get('language', 'site_name'),
				'VOTE_SITE_NAME_VALUE' => Output::getClean($site->name),
				'VOTE_SITE_URL' => $votingplugin_language->get('language', 'site_url'),
				'VOTE_SITE_URL_VALUE' => Output::getClean($site->site),
			));

			$template_file = 'votingplugin/vote_edit.tpl';
			break;

		case 'delete':
			if(isset($_GET['id']) && is_numeric($_GET['id'])){
				try {
					$queries->delete('vote_sites', array('id', '=', $_GET['id']));
				} catch(Exception $e){
					die($e->getMessage());
				}

				Session::flash('staff_vote', $votingplugin_language->get('language', 'site_deleted_successfully'));
				Redirect::to(URL::build('/panel/vote'));
				die();
			}
			break;

		default:
			Redirect::to(URL::build('/panel/vote'));
			die();
			break;
	}
} else {
	// Deal with input
	if(Input::exists()){
		$errors = array();
		if(Token::check(Input::get('token'))){
			$validate = new Validate();
			$validation = $validate->check($_POST, array(
				'message' => array(
					'max' => 2048
				),
				'link_location' => array(
					'required' => true
				),
				'icon' => array(
					'max' => 64
				)
			));

			if($validation->passed()){
				try {
					// Get link location
					if(isset($_POST['link_location'])){
						switch($_POST['link_location']){
							case 1:
							case 2:
							case 3:
							case 4:
								$location = $_POST['link_location'];
								break;
							default:
								$location = 1;
						}
					} else
						$location = 1;

					// Update Link location cache
					$cache->setCache('nav_location');
					$cache->store('voting_plugin_location', $location);

					// Update Icon cache
					$cache->setCache('navbar_icons');
					$cache->store('voting_plugin_icon', Input::get('icon'));

					// Update Vote Message
					$message_id = $queries->getWhere('vote_settings', array('name', '=', 'vote_message'));
					$message_id = $message_id[0]->id;
					$queries->update('vote_settings', $message_id, array(
						'value' => Input::get('message'),
					));

					$success = $votingplugin_language->get('language', 'updated_successfully');
				} catch(Exception $e){
					$errors[] = $e->getMessage();
				}
			} else {
				$errors[] = $votingplugin_language->get('language', 'message_maximum');
			}
		} else {
			$errors[] = $language->get('general', 'invalid_token');
		}
	}

	// Get vote sites from database
	$vote_sites = $queries->getWhere('vote_sites', array('id', '<>', 0));
	$sites_array = array();
	if(count($vote_sites)){
		foreach($vote_sites as $site){
			$sites_array[] = array(
				'edit_link' => URL::build('/panel/vote/', 'action=edit&id=' . Output::getClean($site->id)),
				'title' => Output::getClean($site->name),
				'delete_link' => URL::build('/panel/vote/', 'action=delete&id=' . Output::getClean($site->id))
			);
		}
	}

	// Retrieve Link Location from cache
	$cache->setCache('nav_location');
	$link_location = $cache->retrieve('voting_plugin_location');

	// Retrieve Icon from cache
	$cache->setCache('navbar_icons');
	$icon = $cache->retrieve('voting_plugin_icon');

	// Get vote
	$vote_message = $queries->getWhere('vote_settings', array('name', '=', 'vote_message'));
	$vote_message = Output::getClean($vote_message[0]->value);

	$smarty->assign(array(
		'NEW_SITE' => $votingplugin_language->get('language', 'new_vote_site'),
		'NEW_SITE_LINK' => URL::build('/panel/vote/', 'action=new'),
		'LINK_LOCATION' => $votingplugin_language->get('language', 'link_location'),
		'LINK_LOCATION_VALUE' => $link_location,
		'LINK_NAVBAR' => $language->get('admin', 'page_link_navbar'),
		'LINK_MORE' => $language->get('admin', 'page_link_more'),
		'LINK_FOOTER' => $language->get('admin', 'page_link_footer'),
		'LINK_NONE' => $language->get('admin', 'page_link_none'),
		'ICON' => $votingplugin_language->get('language', 'icon'),
		'ICON_EXAMPLE' => Output::getClean($votingplugin_language->get('language', 'icon_example')),
		'ICON_VALUE' => Output::getClean(Output::getDecoded($icon)),
		'SITE_LIST' => $sites_array,
		'NO_VOTE_SITES' => $votingplugin_language->get('language', 'no_vote_sites'),
		'MESSAGE' => $votingplugin_language->get('language', 'message'),
		'MESSAGE_VALUE' => $vote_message,
		'ARE_YOU_SURE' => $language->get('general', 'are_you_sure'),
		'CONFIRM_DELETE_SITE' => $votingplugin_language->get('language', 'delete_site'),
		'YES' => $language->get('general', 'yes'),
		'NO' => $language->get('general', 'no')
	));

	$template_file = 'votingplugin/vote.tpl';
}


// Load modules + template
Module::loadPage($user, $pages, $cache, $smarty, array($navigation, $cc_nav, $mod_nav), $widgets);

if(Session::exists('staff_vote'))
	$success = Session::flash('staff_vote');

if(isset($success))
	$smarty->assign(array(
		'SUCCESS' => $success,
		'SUCCESS_TITLE' => $language->get('general', 'success')
	));

if(isset($errors) && count($errors))
	$smarty->assign(array(
		'ERRORS' => $errors,
		'ERRORS_TITLE' => $language->get('general', 'error')
	));

$smarty->assign(array(
	'PARENT_PAGE' => PARENT_PAGE,
	'PAGE' => PANEL_PAGE,
	'DASHBOARD' => $language->get('admin', 'dashboard'),
	'VOTE' => $votingplugin_language->get('language', 'vote'),
	'TOKEN' => Token::get(),
	'SUBMIT' => $language->get('general', 'submit')
));

$page_load = microtime(true) - $start;
define('PAGE_LOAD_TIME', str_replace('{x}', round($page_load, 3), $language->get('general', 'page_loaded_in')));

$template->onPageLoad();

require(ROOT_PATH . '/core/templates/panel_navbar.php');

// Display template
$template->displayTemplate($template_file, $smarty);
die();
?>
<!DOCTYPE html>
<html lang="<?php echo (defined('HTML_LANG') ? HTML_LANG : 'en'); ?>">
  <head>
    <!-- Standard Meta -->
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
	
	<?php 
	$title = $language->get('admin', 'admin_cp');
	require('core/templates/admin_header.php'); 
	?>
  
	<!-- Custom style -->
	<style>
	textarea {
		resize: none;
	}
	</style>

  </head>

  <body>
    <?php require('modules/Core/pages/admin/navbar.php'); ?>
    <div class="container">	
	  <div class="row">
		<div class="col-md-3">
		  <?php require('modules/Core/pages/admin/sidebar.php'); ?>
		</div>
		<div class="col-md-9">
		  <div class="card">
		    <div class="card-block">
			  <h3 style="display:inline;"><?php echo $votingplugin_language->get('language', 'vote'); ?></h3>
			  <?php if(!isset($_GET['action'])){ ?>
			  <span class="pull-right"><a href="<?php echo URL::build('/admin/vote/', 'action=new'); ?>" class="btn btn-primary"><?php echo $votingplugin_language->get('language', 'new_vote_site'); ?></a></span>
			  <?php  } else { ?>
			  <span class="pull-right"><a href="<?php echo URL::build('/admin/vote'); ?>" class="btn btn-warning"><?php echo $language->get('general', 'cancel'); ?></a></span>
			  <?php } ?>
			  <br /><br />
			  <?php if(!isset($_GET['action'])){ ?>
			  <div class="panel panel-default">
			    <div class="panel-heading"><?php echo $votingplugin_language->get('language', 'vote_sites'); ?></div>
				<div class="panel-body">
				  <?php
				  $vote_sites = $queries->getWhere('vote_sites', array('id', '<>', 0));
				  if(!count($vote_sites)){
				  ?>
				  <div class="alert alert-warning"><?php echo $votingplugin_language->get('language', 'no_vote_sites'); ?></div>
				  <?php
				  } else {
					  foreach($vote_sites as $vote_site){
						echo Output::getClean($vote_site->name);
						?>
				  <span class="pull-right">
				    <a href="<?php echo URL::build('/admin/vote/', 'action=edit&amp;site=' . $vote_site->id); ?>" class="btn btn-warning btn-sm"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a> 
					<a href="<?php echo URL::build('/admin/vote/', 'action=delete&amp;site=' . $vote_site->id); ?>" class="btn btn-danger btn-sm" onclick="return confirm('<?php echo $votingplugin_language->get('language', 'confirm_delete_vote_site'); ?>');"><i class="fa fa-trash" aria-hidden="true"></i></a>
				  </span>
						<?php
						if(next($vote_sites))
							echo '<hr />';
					  }
				  }
				  ?>
				</div>
			  </div>
			  <?php 
			  } else {
				  if($_GET['action'] == 'delete'){
					  // Verify site ID
					  if(!isset($_GET['site']) || !is_numeric($_GET['site'])){
						  Redirect::to(URL::build('/admin/vote'));
						  die();
					  }
					  
					  try {
						  $queries->delete('vote_sites', array('id', '=', $_GET['site']));
						  
						  Redirect::to(URL::build('/admin/vote'));
						  die();
					  } catch(Exception $e){
						  die($e->getMessage());
					  }
					  
				  } else if($_GET['action'] == 'new'){
					  // Create site
					  // Deal with input
					  if(Input::exists()){
						  $errors = array();
						  
						  if(Token::check(Input::get('token'))){
							  $validate = new Validate();
							  $validation = $validate->check($_POST, array(
								'site_name' => array(
									'required' => true,
									'max' => 64
								),
								'site_url' => array(
									'required' => true,
									'min' => 8,
									'max' => 512
								)
							  ));
							  
							  if($validation->passed()){
								  // Create the site
								  try {
									  $queries->create('vote_sites', array(
										'site' => Output::getClean(Input::get('site_url')),
										'name' => Output::getClean(Input::get('site_name'))
									  ));
									  
									  Redirect::to(URL::build('/admin/vote'));
									  die();

								  } catch(Exception $e){
									  $errors[] = $e->getMessage();
								  }
							  } else {
								foreach($validation->errors() as $item){
									if(strpos($item, 'is required') !== false){
										switch($item){
											case (strpos($item, 'site_name') !== false):
												$errors[] = $votingplugin_language->get('language', 'name_required');
											break;
											case (strpos($item, 'site_url') !== false):
												$errors[] = $votingplugin_language->get('language', 'url_required');
											break;
										}
									} else if(strpos($item, 'minimum') !== false){
										$errors[] = $votingplugin_language->get('language', 'url_min_8');
										
									} else if(strpos($item, 'maximum') !== false){
										switch($item){
											case (strpos($item, 'site_name') !== false):
												$errors[] = $votingplugin_language->get('language', 'name_max_64');
											break;
											case (strpos($item, 'site_url') !== false):
												$errors[] = $votingplugin_language->get('language', 'url_max_256');
											break;
										}
									}
								}
							  }
						  } else
							  $errors[] = $language->get('general', 'invalid_token');
					  }
					  
					  echo '<h4>'. $votingplugin_language->get('language', 'new_vote_site') . '</h4>';
					  
					  if(count($errors)){
						  echo '<div class="alert alert-danger"><ul>';
						  foreach($errors as $error){
							  echo '<li>' . $error . '</li>';
						  }
						  echo '</ul></div>';
					  }
					  ?>
					  <form action="" method="post">
					    <div class="form-group">
						  <label for="inputName"><?php echo $votingplugin_language->get('language', 'site_name'); ?></label>
						  <input type="text" class="form-control" id="inputName" name="site_name" placeholder="<?php echo $votingplugin_language->get('language', 'site_name'); ?>">
						</div>
						<div class="form-group">
						  <label for="inputURL"><?php echo $votingplugin_language->get('language', 'site_url'); ?></label>
						  <input type="text" class="form-control" id="inputURL" name="site_url" placeholder="<?php echo $votingplugin_language->get('language', 'site_url'); ?>">
						</div>
						<div class="form-group">
						  <input type="hidden" name="token" value="<?php echo Token::get(); ?>">
						  <input type="submit" class="btn btn-primary" value="<?php echo $language->get('general', 'submit'); ?>">
						</div>
					  </form>
					  <?php
				  } else if($_GET['action'] == 'edit'){
					  // Verify site ID
					  if(!isset($_GET['site']) || !is_numeric($_GET['site'])){
						  Redirect::to(URL::build('/admin/vote'));
						  die();
					  }
					  
					  $site = $queries->getWhere('vote_sites', array('id', '=', $_GET['site']));
					  if(!count($site)){
						  Redirect::to(URL::build('/admin/vote'));
						  die();
					  }
					  $site = $site[0];
					  
					  if(Input::exists()){
						  $errors = array();
						  
						  if(Token::check(Input::get('token'))){
							  $validate = new Validate();
							  $validation = $validate->check($_POST, array(
								'site_name' => array(
									'required' => true,
									'max' => 64
								),
								'site_url' => array(
									'required' => true,
									'min' => 8,
									'max' => 512
								)
							  ));
							  
							  if($validation->passed()){
								  // Create the site
								  try {
									  $queries->update('vote_sites', $site->id, array(
										'site' => Output::getClean(Input::get('site_url')),
										'name' => Output::getClean(Input::get('site_name'))
									  ));
									  
									  // Re-query
									  $site = $queries->getWhere('vote_sites', array('id', '=', $_GET['site']));
									  $site = $site[0];
									  
									  $success = true;

								  } catch(Exception $e){
									  $errors[] = $e->getMessage();
								  }
							  } else {
								foreach($validation->errors() as $item){
									if(strpos($item, 'is required') !== false){
										switch($item){
											case (strpos($item, 'site_name') !== false):
												$errors[] = $votingplugin_language->get('language', 'name_required');
											break;
											case (strpos($item, 'site_url') !== false):
												$errors[] = $votingplugin_language->get('language', 'url_required');
											break;
										}
									} else if(strpos($item, 'minimum') !== false){
										$errors[] = $votingplugin_language->get('language', 'url_min_8');
										
									} else if(strpos($item, 'maximum') !== false){
										switch($item){
											case (strpos($item, 'site_name') !== false):
												$errors[] = $votingplugin_language->get('language', 'name_max_64');
											break;
											case (strpos($item, 'site_url') !== false):
												$errors[] = $votingplugin_language->get('language', 'url_max_256');
											break;
										}
									}
								}
							  }
						  } else
							  $errors[] = $language->get('general', 'invalid_token');
					  }
					  
					  echo '<h4>'. Output::getClean($site->name) . '</h4>';
					  
					  if(count($errors)){
						  echo '<div class="alert alert-danger"><ul>';
						  foreach($errors as $error){
							  echo '<li>' . $error . '</li>';
						  }
						  echo '</ul></div>';
					  } else if(isset($success)){
						  echo '<div class="alert alert-success">' . $votingplugin_language->get('language', 'site_edited_successfully') . '</div>';
					  }
					  ?>
					  <form action="" method="post">
					    <div class="form-group">
						  <label for="inputName"><?php echo $votingplugin_language->get('language', 'site_name'); ?></label>
						  <input type="text" class="form-control" id="inputName" name="site_name" value="<?php echo Output::getClean($site->name); ?>" placeholder="<?php echo $votingplugin_language->get('language', 'site_name'); ?>">
						</div>
						<div class="form-group">
						  <label for="inputURL"><?php echo $votingplugin_language->get('language', 'site_url'); ?></label>
						  <input type="text" class="form-control" id="inputURL" name="site_url" value="<?php echo Output::getClean($site->site); ?>" placeholder="<?php echo $votingplugin_language->get('language', 'site_url'); ?>">
						</div>
						<div class="form-group">
						  <input type="hidden" name="token" value="<?php echo Token::get(); ?>">
						  <input type="submit" class="btn btn-primary" value="<?php echo $language->get('general', 'submit'); ?>">
						</div>
					  </form>
					  <?php
				  } else {
					  // Invalid action
					  Redirect::to(URL::build('/admin/vote'));
					  die();
				  }
			  }
			  ?>
			</div>
		  </div>
		</div>
      </div>
    </div>
	<?php require('modules/Core/pages/admin/footer.php'); ?>

    <?php require('modules/Core/pages/admin/scripts.php'); ?>
  </body>
</html>