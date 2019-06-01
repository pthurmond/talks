#!/bin/bash

#
# Implements Cloud_hook post_code_deploy
#

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
source "$DIR/../../shared/slack-alerts.sh"
source "$DIR/../../shared/01-db-copy-drupal-commands.sh"

slack "#6DC84B" "The database named ${db_name} has been copied from ${source_env} to: ${target_env}." "All Drupal update processes have been run."
