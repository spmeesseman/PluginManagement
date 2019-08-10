# Plugins MantisBT Plugin

![app-type](https://img.shields.io/badge/category-mantisbt%20plugins%20anti--spam-blue.svg)
![app-lang](https://img.shields.io/badge/language-php-blue.svg)
[![app-publisher](https://img.shields.io/badge/%20%20%F0%9F%93%A6%F0%9F%9A%80-app--publisher-e10000.svg)](https://github.com/spmeesseman/app-publisher)
[![authors](https://img.shields.io/badge/authors-scott%20meesseman-6F02B5.svg?logo=visual%20studio%20code)](https://github.com/spmeesseman)

[![MantisBT issues open](https://app1.spmeesseman.com/projects/plugins/ApiExtend/api/issues/countbadge/Plugins/open)](https://app1.spmeesseman.com/projects/set_project.php?project=Plugins&make_default=no&ref=bug_report_page.php)
[![MantisBT issues closed](https://app1.spmeesseman.com/projects/plugins/ApiExtend/api/issues/countbadge/Plugins/closed)](https://app1.spmeesseman.com/projects/set_project.php?project=Plugins&make_default=no&ref=bug_report_page.php)
[![MantisBT version current](https://app1.spmeesseman.com/projects/plugins/ApiExtend/api/versionbadge/Plugins/current)](https://app1.spmeesseman.com/projects/set_project.php?project=Plugins&make_default=no&ref=plugin.php?page=Releases/releases)
[![MantisBT version next](https://app1.spmeesseman.com/projects/plugins/ApiExtend/api/versionbadge/Plugins/next)](https://app1.spmeesseman.com/projects/set_project.php?project=Plugins&make_default=no&ref=plugin.php?page=Releases/releases)

- [Plugins MantisBT Plugin](#Plugins-MantisBT-Plugin)
  - [Description](#Description)
  - [Installation](#Installation)
  - [Requirements](#Requirements)
  - [Issues and Feature Requests](#Issues-and-Feature-Requests)
  - [Configuration](#Configuration)
  - [Usage](#Usage)
  - [Todos](#Todos)

## Description

This plugin adds the ability to:

1. Check for new plugin versions
2. Download/install new plugin versions
3. Backup/restore previously installed plugin versions

## Installation

Extract the release archive to the MantisBT installations plugins folder:

    cd /var/www/mantisbt/plugins
    wget -O Plugins.zip https://github.com/mantisbt-plugins/Plugins/releases/download/v1.0.0/Plugins.zip
    unzip Plugins.zip
    rm -f Plugins.zip

Ensure to use the latest released version number in the download url: [![MantisBT version current](https://app1.spmeesseman.com/projects/plugins/ApiExtend/api/versionbadge/Plugins/current)](https://app1.spmeesseman.com/projects) (version badge available via the [ApiExtend Plugin](https://github.com/mantisbt-plugins/ApiExtend))

Install the plugin using the default installation procedure for a MantisBT plugin in `Manage -> Plugins`.

## Requirements

The following PHP components are required by this plugin:

- php-zip

    sudo apt install php7.2-zip
- webserver must have write access to this plugin directory

## Issues and Feature Requests

Issues and requests should be submitted on my [MantisBT](https://app1.spmeesseman.com/projects/set_project.php?project=Plugins&make_default=no&ref=bug_report_page.php) site.

## Configuration

You can set access rights for viewing and/or editing the Plugins options in the MantisBT plugin settings.  The default access rights are:

- View Access => MANAGER
- Edit Access => ADMINISTRATOR

Note that if you have the **Content-Security-Policy** header set within your config_inc.php **$g_custom_headers** config parameter, then you will need to add the https://img.shields.io/ url to the **img-src** section to be able to see badges in the Info page, for example:

    $g_custom_headers = array(
        "Content-Security-Policy: " .
        "frame-src http://gist-it.appshot.com/ 'self'; " .
        "img-src https://img.shields.io/ https://secure.gravatar.com/ 'self' data:; default-src 'self'; frame-ancestors 'self'; " .
        "font-src 'self'; " .
        "style-src 'self'; " .
        "script-src https://cdnjs.cloudflare.com/ http://gist-it.appspot.com/ 'self' 'unsafe-inline'"
    );

## Usage

TODO

## Todos

[ ] Check for available plugins that are not installed
