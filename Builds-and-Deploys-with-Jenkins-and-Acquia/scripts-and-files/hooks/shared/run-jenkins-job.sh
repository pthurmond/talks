#!/bin/bash

########################################################################
# This is an example how to call a jenkins job using bash
#
# Pre-Reqs:
#   1: Add a token to your jenkins job. Configure job,
#      "Build Triggers - Trigger builds remotely (e.g. from scripts)"
#      One way to generate a job token
#
#   $> r="";for e in $(seq 1 5); do r="${r}$(od -vAn -N4 -tu4 < /dev/urandom|sed 's| ||g')"; done;echo $r|openssl sha1 -sha256
#
#   2: Go get your own personal API token
#      You can click on your name at the upper-right, 'Configure > API Token > Show API Token
#
#   3: Run your job e.g. run one that has parameters
#      ./run-jenkins-job.sh <userid> <API-token> jenkins-job-name <job-token> 'PARAMA=value&PARAMB=value'
#
#      run one without params
#      ./run-jenkins-job.sh <userid> <API-token> <jenkins-job-name> <job-token>
#
#   Add any job parameters there.
########################################################################


function usage {
  echo "$0 <jenkins-login> <jenkins-login-token> <jenkins-job> <job-token> 'job_param_a=value&job_param_b=value'"
}

function run_jenkins() {
  local USER="$1"
  local TOKEN="$2"
  local JOB="$3"
  local JTOKEN="$4"
  local PARAMS="$5"

  if [ "" == "${USER}" ]; then
    usage
    exit 1
  fi

  if [ "" == "${TOKEN}" ]; then
    usage
    exit 1
  fi

  if [ "" == "${JOB}" ]; then
    usage
    exit 1
  fi

  if [ "" == "${JTOKEN}" ]; then
    usage
    exit 1
  fi

  # jobs that have params need a different url and the params need to be on the query string
  if [ "" == "${PARAMS}" ]; then
    NOTIFY_URL="job/${JOB}/build"
  else
    NOTIFY_URL="job/${JOB}/buildWithParameters?${PARAMS}"
  fi

  CRUMB_ISSUER_URL='crumbIssuer/api/xml?xpath=concat(//crumbRequestField,":",//crumb)'
  CISERVER="https://qa-jenkins.mydomain.com"

  # Check if "[X] Prevent Cross Site Request Forgery exploits" is activated
  # so we can present a valid crumb or a proper header
  HEADER="Content-Type:text/plain;charset=UTF-8"
  CRUMB=$(curl --user ${USER}:${TOKEN} ${CISERVER}/${CRUMB_ISSUER_URL} 2>/dev/null)

  if [ "$CRUMB" != "" ]; then
    HEADER=$CRUMB
  fi

  curl -X POST ${CISERVER}/${NOTIFY_URL} --header "${HEADER}" --data-urlencode "token=${JTOKEN}" --user "${USER}:${TOKEN}"
  echo "Done!"
}