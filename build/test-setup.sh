#!/bin/bash
# Script for preparing the system tests in Joomla!

touch output.log
cd tests
composer config -g github-oauth.github.com "${GITHUB_TOKEN}"
composer install --prefer-dist > output.log 2>&1
composer update

vendor/bin/robo prepare:site-for-system-testing > output.log 2>&1
cd ..
wget "https://chromedriver.storage.googleapis.com/2.36/chromedriver_linux64.zip" > output.log 2>&1
ln -s /usr/bin/nodejs /usr/bin/node > output.log 2>&1
npm cache clean -f  > output.log 2>&1
npm install -g n  > output.log 2>&1
n 9  > output.log 2>&1

# Get Chrome Headless
mkdir -p /usr/local/bin
unzip -o "chromedriver_linux64.zip" -d /usr/local/bin
chmod +x /usr/local/bin/chromedriver

# check tests and repo inside tests/www
cd /tests/www
mkdir tests
mkdir repo
cd tests
mkdir releases-redform

# cd $WORKSPACE and link for tests/joomla-cms
cd $WORKSPACE
git submodule update --init --recursive
ln -s $(pwd)/tests/joomla-cms /tests/www/tests/
ln -s $(pwd) /tests/www/repo/

# Install Gulp for Package Generation
cd build
node --version
npm  --version
npm install gulp -g # install globally so that it's available to robo
npm install
mv gulp-config.json.jenkins.dist gulp-config.json
git submodule update --init --recursive
gulp release --skip-version
cd ..
cp /tests/www/tests/releases-redform/redform.zip .
zip --symlinks -r gulp-release.zip /tests/www/tests/releases-redform > output.log 2>&1

# back to tests for run codeception
cd tests
vendor/bin/robo upload:patch-from-jenkins-to-test-server $GITHUB_TOKEN $GITHUB_REPO_OWNER $REPO $CHANGE_ID

#setting php configuration
sed -e 's/max_input_time = 60/max_input_time = 6000/' -i /etc/php/7.1/apache2/php.ini
sed -e 's/max_execution_time = 30/max_execution_time = 6000/' -i /etc/php/7.1/apache2/php.ini
sed -e 's/memory_limit = 128M/memory_limit = 512M/' -i /etc/php/7.1/apache2/php.ini

# Start apache
a2enmod rewrite
service apache2 restart

# Test Setup
cd $WORKSPACE
cd tests
mv acceptance.suite.dist.jenkins.yml acceptance.suite.yml
sed -i "s/{dbhostname}/db-$BUILD_TAG/g" acceptance.suite.yml
mysql --host=db-$BUILD_TAG -uroot -proot -e "DROP DATABASE IF EXISTS redformSetupDb;"
chown -R www-data:www-data joomla-cms
cd $WORKSPACE/tests/
composer install

#check code
vendor/bin/robo check:for-missed-debug-code
vendor/bin/robo check:for-parse-errors
vendor/bin/robo check:codestyle
vendor/bin/robo run:unit-tests
vendor/bin/robo prepare:site-for-system-testing
vendor/bin/robo run:test-setup-jenkins

if [ $? -eq 0 ]
then
	echo "Tests Run were sucessful"
	rm -r _output/
	mysqldump --host=db-$BUILD_TAG -uroot -proot redformSetupDb > backup.sql
	zip --symlinks -r joomla-cms-database.zip backup.sql > output.log 2>&1
	mv joomla-cms-database.zip ..
	zip --symlinks -r joomla-cms.zip joomla-cms > output.log 2>&1
	mv *joomla-cms.zip* ..
	cd ..
  exit 0
else
	echo "Tests Runs Failed" >&2
	#send screenshot of failed test to Slack
	vendor/bin/robo send:system-build-report-error-slack $CLOUDINARY_CLOUD_NAME $CLOUDINARY_API_KEY $CLOUDINARY_API_SECRET $GITHUB_REPO $CHANGE_ID "$SLACK_WEBHOOK" "$SLACK_CHANNEL" "$BUILD_URL"
	cd _output
	ls
	rm -r _output
	cd ../
	exit 1
fi