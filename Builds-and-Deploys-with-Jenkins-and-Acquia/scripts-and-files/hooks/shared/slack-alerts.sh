#!/bin/bash

slack() {
  local color="$1"
  local title="$2"
  local text="$3"

  # The "icon_emoji" could also be ":drupal:"
  curl -ss -X POST --data-urlencode "payload={\"channel\": \"#builds\", \"username\": \"My Cloud\", \"icon_emoji\": \":mycloud:\", \"attachments\": [{\"color\": \"'$color'\", \"title\": \"'$title'\", \"text\": \"'$text'\"}]}" https://hooks.slack.com/services/SomeThing1/BitStuff2/LongString3
}


### SAMPLE CALL ###
# slack "#6DC84B" "DEV2: POST CODE DEPLOY - Alert from sample Acquia hook script." "Nothing to see here. - Obi Wan Kenobi (Patrick Thurmond)"

### Colors ###
# Blue: #3770AF
# Green: #6DC84B
# Yellow: #F4CC22
# Grey: #E8E8E8


### Including this file... ###
# To include this file just do this:
# . ../../slack-alerts.sh
#
# That goes back two directories before loading it.
