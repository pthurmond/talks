#!/bin/bash

#
# Implements Cloud_hook post_code_deploy
#

site="$1"
target_env="$2"
db_name="$3"
source_env="$$"

domainPrepend='catalog'

if [[ $target_env = *"2"* ]]; then
  domainPrepend='romulus'
  target_env="${target_env/2/}"
fi

qaDomain="${domainPrepend}${target_env}.mydomain.com"

echo "QA Domain: ${qaDomain}"

SUBDIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
source "${SUBDIR}/run-jenkins-job.sh"

# Run the standard automated test for QA
if [ $target_env != "dev" ] && [ $target_env != "dev2" ]; then
  run_jenkins myuser 123someKind0fStr1ng "MyGroup/My-Group-Smoke-Test" dootdootdoot "url=${qaDomain}"

  slack "#6DC84B" "QA Automation Script has been started..." "The automated test scripts from QA have been launched."
fi
