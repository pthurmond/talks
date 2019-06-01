#!/bin/bash

#
# Implements Cloud_hook post_code_deploy
#

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
source "$DIR/../../shared/slack-alerts.sh"
source "$DIR/../../shared/01-drupal-commands.sh"

slack "#6DC84B" "Code deployed to: ${target_env}." "The branch/tag ${deployed_tag} has been fully deployed to ${target_env}. All deployment processes have been run."
