#!/bin/bash

# tracks the exit status
EXIT=0

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

cd ${DIR}
cd ../
echo "Current directory: $PWD"

declare -a testbench_versions=(
  "6.0" # Laravel 8
  "7.0" # Laravel 9
)

## now loop through the above array
for testbench_version in "${testbench_versions[@]}"
do
   echo "Testing (with Orchestra Testbench version $testbench_version)"

   echo "Installing dependencies"
   rm -f composer-test.*
   rm -rf vendor/*
   cp composer.json composer-test.json
   COMPOSER=composer-test.json /usr/bin/env composer require "orchestra/testbench:~$testbench_version.0" -q

   echo "Running phpunit"
   ./vendor/bin/phpunit --verbose || EXIT=$?

   if [[ ${EXIT} != 0 ]]; then
     echo "Oops, looks like something went wrong! ¯\_(ツ)_/¯"
     exit ${EXIT}
   fi
done

# To see the exit status of a command, run it and then afterwards run #> echo $?
# echo $?
echo

if [[ ${EXIT} == 0 ]]; then
    echo "Yay, green all the things"
fi

echo

exit ${EXIT}