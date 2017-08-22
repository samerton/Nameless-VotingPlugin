<?php 
/*
 *	Made by Samerton
 *  https://github.com/NamelessMC/Nameless/
 *  NamelessMC version 2.0.0-pr2
 *
 *  License: MIT
 *
 *  VotingPlugin module - admin vote settings page
 */

// Can the user view the AdminCP?
if($user->isLoggedIn()){
	if(!$user->canViewACP()){
		// No
		Redirect::to(URL::build('/'));
		die();
	} else {
		// Check the user has re-authenticated
		if(!$user->isAdmLoggedIn()){
			// They haven't, do so now
			Redirect::to(URL::build('/admin/auth'));
			die();
		}
	}
} else {
	// Not logged in
	Redirect::to(URL::build('/login'));
	die();
}
 
 
$page = 'admin';
$admin_page = 'vote';
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