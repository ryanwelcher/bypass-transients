# Suspend Transients #

We all know that caching is FTW. But, caching can be a nuisance when trying to develop or debug. That's where this plugin comes in.
You can bypass any get_transient() calls on a per page basis by clicking the link in the Admin Bar.

## Features ###
1. Bypass Transients with a single click
2. Supports Transients saved to the database
3. Object Cache support - currently only tested with memecached
4. Will automatically detected any new transients set.
5. Debug Bar integration to provide details such as lists of known, found and bypassed transients.

## Disclaimer ##
This plugin should NOT be run in a production environment. It is meant for local and MAYBE staging development. By it's very nature this will slow 
your site down.
