<?php 
/*
 *	Made by Samerton
 *  https://github.com/NamelessMC/Nameless/
 *  NamelessMC version 2.1.1
 *
 *  License: MIT
 *
 *  VotingPlugin module - panel vote settings page
 */

/**
 * @var Cache $cache
 * @var Language $language
 * @var Language $votingplugin_language
 * @var Navigation $cc_nav
 * @var Navigation $navigation
 * @var Navigation $staffcp_nav
 * @var Pages $pages
 * @var Smarty $smarty
 * @var TemplateBase $template
 * @var User $user
 * @var Widgets $widgets
 */

if (!$user->handlePanelPageLoad('admincp.vote')) {
    require_once(ROOT_PATH . '/403.php');
    die();
}

const PAGE = 'panel';
const PARENT_PAGE = 'vote';
const PANEL_PAGE = 'vote';

$page_title = $votingplugin_language->get('vote');

require_once ROOT_PATH . '/core/templates/backend_init.php';

if (isset($_GET['action'])) {
	switch ($_GET['action']) {
		case 'new':
			if (Input::exists()) {
				$errors = array();
				if (Token::check(Input::get('token'))) {
					// process addition of site
					$validation = Validate::check($_POST, array(
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
					))->messages([
                        'vote_site_name' => [
                            Validate::REQUIRED => $votingplugin_language->get( 'site_name_required'),
                            Validate::MIN => $votingplugin_language->get('site_name_minimum'),
                            Validate::MAX => $votingplugin_language->get('site_name_maximum'),
                        ],
                        'vote_site_url' => [
                            Validate::REQUIRED => $votingplugin_language->get('site_url_required'),
                            Validate::MIN => $votingplugin_language->get('site_url_minimum'),
                            Validate::MAX => $votingplugin_language->get('site_url_maximum'),
                        ],
                    ]);

					if ($validation->passed()) {
						// input into database
						try {
							DB::getInstance()->insert('vote_sites', array(
								'site' => Input::get('vote_site_url'),
								'name' => Input::get('vote_site_name'),
							));
							Session::flash('staff_vote', $votingplugin_language->get('site_created_successfully'));
							Redirect::to(URL::build('/panel/vote'));

						} catch (Exception $e) {
							$errors[] = $e->getMessage();
						}
					} else {
                        $errors = $validation->errors();
                    }
				} else {
					$errors[] = $language->get('general', 'invalid_token');
				}
			}

			$smarty->assign(array(
				'NEW_SITE' => $votingplugin_language->get('new_vote_site'),
				'BACK' => $language->get('general', 'back'),
				'BACK_LINK' => URL::build('/panel/vote'),
				'VOTE_SITE_NAME' => $votingplugin_language->get('site_name'),
				'VOTE_SITE_URL' => $votingplugin_language->get('site_url'),
			));

			$template_file = 'votingplugin/vote_new.tpl';
			break;
		case 'edit':
			// Get page
			if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
				Redirect::to(URL::build('/panel/vote'));
			}

			$site = DB::getInstance()->get('vote_sites', array('id', '=', $_GET['id']));
			if (!$site->count()) {
				Redirect::to(URL::build('/panel/vote'));
			}
			$site = $site->first();

			if (Input::exists()) {
				$errors = array();
				if (Token::check(Input::get('token'))) {
					// process addition of site
					$validation = Validate::check($_POST, array(
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
					))->messages([
                        'vote_site_name' => [
                            Validate::REQUIRED => $votingplugin_language->get('site_name_required'),
                            Validate::MIN => $votingplugin_language->get('site_name_minimum'),
                            Validate::MAX => $votingplugin_language->get('site_name_maximum'),
                        ],
                        'vote_site_url' => [
                            Validate::REQUIRED => $votingplugin_language->get('site_url_required'),
                            Validate::MIN => $votingplugin_language->get('site_url_minimum'),
                            Validate::MAX => $votingplugin_language->get('site_url_maximum'),
                        ],
                    ]);

					if ($validation->passed()) {
						// input into database
						try {
							DB::getInstance()->update('vote_sites', $site->id, array(
								'site' => Input::get('vote_site_url'),
								'name' => Input::get('vote_site_name')
							));
							Session::flash('staff_vote', $votingplugin_language->get('site_edited_successfully'));
							Redirect::to(URL::build('/panel/vote'));

						} catch(Exception $e){
							$errors[] = $e->getMessage();
						}

					} else {
						$errors = $validation->errors();
					}
				} else {
					$errors[] = $language->get('general', 'invalid_token');
				}
			}

			$smarty->assign(array(
				'EDIT_SITE' => $votingplugin_language->get('edit_site'),
				'BACK' => $language->get('general', 'back'),
				'BACK_LINK' => URL::build('/panel/vote'),
				'VOTE_SITE_NAME' => $votingplugin_language->get('site_name'),
				'VOTE_SITE_NAME_VALUE' => Output::getClean($site->name),
				'VOTE_SITE_URL' => $votingplugin_language->get('site_url'),
				'VOTE_SITE_URL_VALUE' => Output::getClean($site->site),
			));

			$template_file = 'votingplugin/vote_edit.tpl';
			break;

		case 'delete':
			if (isset($_GET['id']) && is_numeric($_GET['id'])) {
				try {
					DB::getInstance()->delete('vote_sites', array('id', '=', $_GET['id']));
				} catch(Exception $e){
					die($e->getMessage());
				}

				Session::flash('staff_vote', $votingplugin_language->get('site_deleted_successfully'));
				Redirect::to(URL::build('/panel/vote'));
			}
			break;

		default:
			Redirect::to(URL::build('/panel/vote'));
			break;
	}
} else {
	// Deal with input
	if (Input::exists()) {
		$errors = array();
		if (Token::check(Input::get('token'))) {
			$validation = Validate::check($_POST, array(
				'message' => array(
					'max' => 2048
				),
				'link_location' => array(
					'required' => true
				),
				'icon' => array(
					'max' => 64
				)
			))->messages([
                'message' => [
                    Validate::MAX => $votingplugin_language->get('message_maximum'),
                ],
                'link_location' => [
                    Validate::REQUIRED => $votingplugin_language->get('link_location_required'),
                ],
                'icon' => [
                    Validate::MAX => $votingplugin_language->get('icon_maximum', null, ['count' => 64]),
                ],
            ]);

			if ($validation->passed()) {
				try {
					// Get link location
					if (isset($_POST['link_location'])) {
						switch ($_POST['link_location']) {
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
					$cache->store('vote_icon', Input::get('icon'));

					// Update Vote Message
					$message_id = DB::getInstance()->get('vote_settings', array('name', '=', 'vote_message'));
					$message_id = $message_id->first()->id;
					DB::getInstance()->update('vote_settings', $message_id, array(
						'value' => Input::get('message'),
					));

					$success = $votingplugin_language->get('updated_successfully');
				} catch(Exception $e){
					$errors[] = $e->getMessage();
				}
			} else {
				$errors[] = $validation->errors();
			}
		} else {
			$errors[] = $language->get('general', 'invalid_token');
		}
	}

	// Get vote sites from database
	$vote_sites = DB::getInstance()->get('vote_sites', array('id', '<>', 0));
	$sites_array = array();
	if ($vote_sites->count()) {
		foreach ($vote_sites->results() as $site) {
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
	$icon = $cache->retrieve('vote_icon');

	// Get vote message
	$vote_message = DB::getInstance()->get('vote_settings', array('name', '=', 'vote_message'));
	$vote_message = Output::getClean($vote_message->first()->value);

	$smarty->assign(array(
		'NEW_SITE' => $votingplugin_language->get('new_vote_site'),
		'NEW_SITE_LINK' => URL::build('/panel/vote/', 'action=new'),
		'LINK_LOCATION' => $votingplugin_language->get('link_location'),
		'LINK_LOCATION_VALUE' => $link_location,
		'LINK_NAVBAR' => $language->get('admin', 'page_link_navbar'),
		'LINK_MORE' => $language->get('admin', 'page_link_more'),
		'LINK_FOOTER' => $language->get('admin', 'page_link_footer'),
		'LINK_NONE' => $language->get('admin', 'page_link_none'),
		'ICON' => $votingplugin_language->get('icon'),
		'ICON_EXAMPLE' => Output::getClean($votingplugin_language->get('icon_example')),
		'ICON_VALUE' => Output::getClean(Output::getDecoded($icon)),
		'SITE_LIST' => $sites_array,
		'NO_VOTE_SITES' => $votingplugin_language->get('no_vote_sites'),
		'MESSAGE' => $votingplugin_language->get('message'),
		'MESSAGE_VALUE' => $vote_message,
		'ARE_YOU_SURE' => $language->get('general', 'are_you_sure'),
		'CONFIRM_DELETE_SITE' => $votingplugin_language->get('delete_site'),
		'YES' => $language->get('general', 'yes'),
		'NO' => $language->get('general', 'no')
	));

	$template_file = 'votingplugin/vote.tpl';
}


// Load modules + template
Module::loadPage($user, $pages, $cache, $smarty, [$navigation, $cc_nav, $staffcp_nav], $widgets, $template);

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
	'VOTE' => $votingplugin_language->get('vote'),
	'TOKEN' => Token::get(),
	'SUBMIT' => $language->get('general', 'submit')
));

$template->onPageLoad();

require ROOT_PATH . '/core/templates/panel_navbar.php';

// Display template
$template->displayTemplate($template_file, $smarty);
