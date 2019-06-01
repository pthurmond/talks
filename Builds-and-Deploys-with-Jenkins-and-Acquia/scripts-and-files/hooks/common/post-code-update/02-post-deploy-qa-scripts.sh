#!/bin/bash

#
# Implements Cloud_hook post_code_deploy
#

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
source "$DIR/../../shared/slack-alerts.sh"
source "$DIR/../../shared/02-post-deploy-qa-scripts.sh"
