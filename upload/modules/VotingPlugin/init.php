<?php 
/*
 *	Made by Samerton
 *  https://github.com/NamelessMC/Nameless/
 *  NamelessMC version 2.0.0-pr7
 *
 *  License: MIT
 *
 *  VotingPlugin initialisation file
 */

// Initialise voting plugin language
$votingplugin_language = new Language(ROOT_PATH . '/modules/VotingPlugin/language', LANGUAGE);

// Initialise module
require_once(ROOT_PATH . '/modules/VotingPlugin/module.php');
$module = new VotingPlugin_Module($votingplugin_language, $pages, $user);
