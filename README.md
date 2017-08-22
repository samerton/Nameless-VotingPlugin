# Nameless-VotingPlugin
A VotingPlugin module for the NamelessMC website software. Displays a list of the top voters from your server as well as a list of available voting sites (configurable through the AdminCP).

## Requirements:
- NamelessMC version 2 (from commit [c4ff769](https://github.com/NamelessMC/Nameless/commit/c4ff769c9d9bdadfa1db810ef69ae55ad7a18ad9) onwards)
- [VotingPlugin](https://www.spigotmc.org/resources/votingplugin.15358/) plugin for Spigot, configured to use a MySQL database

## Installation:
- Upload the contents of the **upload** directory straight into your NamelessMC installation's directory
- Insert your VotingPlugin database details into the file **modules/VotingPlugin/config.php**
- Activate the module in the AdminCP -> Modules tab and set up some vote sites

## Notes:
- Not compatible with other NamelessMC vote modules
