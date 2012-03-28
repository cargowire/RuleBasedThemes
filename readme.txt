=== Rule Based Themes ===
Contributors: Craig Rowe
Tags: rules, theming, sub-themes
Tested in 3.3.1

Create rules based on date, times or the response from a public xml api call that can then be used generate a class for the body tag (or other element).

== Description ==

This plugin provides a mechanism to define rules that are applied via rbtme_get_theme() to return a theme string that can be used, for example, in a body class.  Rules are stored directly as a PHP associative array on the filesystem.  There is no database storage.