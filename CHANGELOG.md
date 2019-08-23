# PLUGINMANAGEMENT CHANGE LOG

## Version 1.0.1 (August 23rd, 2019)

### Bug Fixes

- when installing a new plugin, an error message is shown before redirecting back to plugins page (but the plugin is installed correctly). [fixes #48]

### Refactoring

- dont check for updates on page load, add check for updates button

### Performance Enhancements

- after checking for updated versions, cache the results while performing various actions within the plugins page, bypassing successive api requests to the plugin release pages.
- do not check for updates on core plugins

## Version 1.0.0 (August 15th, 2019)

### Chores

- Initial Release

