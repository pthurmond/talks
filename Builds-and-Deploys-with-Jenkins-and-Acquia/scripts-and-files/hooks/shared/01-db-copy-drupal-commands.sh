#!/bin/bash

#
# Implements Cloud_hook post_code_deploy
#

site="$1"
target_env="$2"
db_name="$3"
source_env="$$"

drush_alias=$site'.'$target_env
domain=`drush @$drush_alias status uri --format=list`


error() {
  local parent_lineno="$1"
  local coide="$2"
  local me=`basename "$0"`
  slack danger "Cloudhook Error: ${target_env}" "Error in ${me} on or near line ${parent_lineno}"
  exit 1
}

# Steps from acmigrate.php
#
# Pre-Check - Not doing this yet
#   Check source environment
#   Log new tag creation
#   Validate git tag
#   // We should consider automatically doing a DB backup:
#   //    `drush @$site.$target_env ac-database-instance <your-database-name>`
#
# Primary Operations
#   1. Enter maintenance mode. `drush sset system.maintenance_mode 1`
#   2. Deploy code.
#   3. IF destination is not current prod: rsync files with source environment.
#   4. Cache rebuild on target env: `drush cr --no-halt-on-error`
#   5. Re-enter maintenance mode: `drush sset system.maintenance_mode 1`
#   6. DB updates: `drush updb -y`
#   7. Entity updates: `drush entup -y`
#   8. Ensure `components` is enabled: `drush en envmod -y`
#   9. Run envmod command to enable/disable modules/settings: `drush switch-environment @target_env`
#   10. Feature revert: `drush fra -y`
#   11. Clear cache again: `drush cr --no-halt-on-error`
#   12. Exit maintenance mode: `drush sset system.maintenance_mode 0`
#   13. Clear cache one more time: `drush cr --no-halt-on-error`
#   14. Clear varnish cache `drush ac-domain-purge [DOMAIN]`
#
# Post Action Operations - Not doing this yet
#   1. Environment check
#   2. Post results to Slack
#   3. Email results to a DL
#

# Main
drush @$drush_alias cim sync -y --strict=0
drush @$drush_alias updatedb -y --strict=0
drush @$drush_alias entup -y --strict=0
drush @$drush_alias fra -y
drush @$drush_alias cr --no-halt-on-error
drush @$drush_alias ac-domain-purge $domain

