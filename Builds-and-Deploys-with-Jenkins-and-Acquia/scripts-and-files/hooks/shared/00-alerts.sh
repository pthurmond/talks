#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
source "$DIR/../../shared/slack-alerts.sh"

slack "#6DC84B" "PT: Testing his code..." "I am testing it CAPTAIN!"
