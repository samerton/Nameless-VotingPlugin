# Nameless-VotingPlugin
A VotingPlugin module for the NamelessMC website software. Displays a list of the top voters from your server as well as a list of available voting sites (configurable through the StaffCP).

## Requirements:
- NamelessMC version 2.2.1 onwards
- [VotingPlugin](https://www.spigotmc.org/resources/votingplugin.15358/) plugin for Spigot, configured to use a MySQL database

## Installation:
- Upload the contents of the **upload** directory straight into your NamelessMC installation's directory
- Ensure the **modules/VotingPlugin** directory is writable so the config can be generated
- Activate the module in the StaffCP -> Modules tab
- Insert your VotingPlugin database details into the file **modules/VotingPlugin/config.php**
- Set up some vote sites in the StaffCP -> Vote tab

## Notes:
- Not compatible with other NamelessMC vote modules
- If you are using a custom template, make sure you add the template file (in the **custom/templates/DefaultRevamp** directory) to your custom template, unless they already come with support for this module.
