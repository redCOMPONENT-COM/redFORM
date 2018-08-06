pipeline {
	agent any
	options {
		timeout(time: 2, unit: 'HOURS')
		buildDiscarder(logRotator(numToKeepStr: '4', daysToKeepStr: '10', artifactDaysToKeepStr: '15', artifactNumToKeepStr: '4'))
		disableConcurrentBuilds()
	}
	triggers {
		pollSCM '30 08 22 06 *'
	}
	stages {
		stage('Docker Setup') {
			steps {
				dockerNetworkCreate("tn-${BUILD_TAG}")
				dockerDBRun("db-${BUILD_TAG}", 'root', "tn-${BUILD_TAG}")
			}
		}
		stage ('Tests Setup') {
			environment {
				GITHUB_TOKEN='4d92f9e8be0eddc0e54445ff45bf1ca5a846b609'
				GITHUB_REPO='redCOMPONENT-COM/redform'
				CLOUDINARY_CLOUD_NAME='redcomponent'
				CLOUDINARY_API_KEY='365447364384436'
				CLOUDINARY_API_SECRET='Q94UM5kjZkZIrau8MIL93m0dN6U'
				SLACK_WEBHOOK='https://hooks.slack.com/services/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'
				SLACK_CHANNEL='#aesir-ec-builds'
				GITHUB_REPO_OWNER='redCOMPONENT-COM'
				REPO='redform'
			}
			agent {
				docker {
					image 'jatitoam/docker-systemtests'
					args  "--network tn-${BUILD_TAG} --user 0 --privileged=true"
				}
			}
			steps {
				retry(2) {
					sh "build/test-setup.sh"
				}
				stash includes: 'tests/vendor/**', name: 'vendor'
				stash includes: 'joomla-cms.zip', name: 'joomla-cms'
				stash includes: 'chromedriver_linux64.zip', name: 'chromeD'
				stash includes: 'redform.zip', name: 'redform'
				stash includes: 'joomla-cms-database.zip', name: 'database-dump'
				stash includes: 'gulp-release.zip', name: 'gulp-release'
			}
			post {
				 always {
					cleanWs()
				}
			}
		}
		stage ('Deploy Package') {
			environment {
				GITHUB_TOKEN='4d92f9e8be0eddc0e54445ff45bf1ca5a846b609'
				GITHUB_REPO_OWNER='redCOMPONENT-COM'
				REPO='vanir'
			}
			steps {
				unstash 'redform'
				sh 'ssh -p4975 installers@test.redcomponent.com "mkdir -p public_html/vanir/PR/$CHANGE_ID"'
				sh 'scp -P4975 redform.zip installers@test.redcomponent.com:/home/installers/public_html/vanir/PR/$CHANGE_ID/redform.zip'
			}
			post {
				always {
				cleanWs()
				}
			}
		}
		stage('Automated Tests - Batch 1') {
			environment {
				GITHUB_TOKEN='4d92f9e8be0eddc0e54445ff45bf1ca5a846b609'
				GITHUB_REPO='redCOMPONENT-COM/vanir'
				CLOUDINARY_CLOUD_NAME='redcomponent'
				CLOUDINARY_API_KEY='365447364384436'
				CLOUDINARY_API_SECRET='Q94UM5kjZkZIrau8MIL93m0dN6U'
				SLACK_WEBHOOK='https://hooks.slack.com/services/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'
				SLACK_CHANNEL='#aesir-ec-builds'
			}
			parallel {
				stage('orders') {
					agent {
						docker {
							image 'jatitoam/docker-systemtests'
							args   "--network tn-${BUILD_TAG} --user 0 --privileged=true"
						}
					}
					steps {
						script {
							env.STAGE = 'orders'
						}
						unstash 'vendor'
						unstash 'joomla-cms'
						unstash 'chromeD'
						unstash 'redform'
						unstash 'database-dump'
						retry(2) {
							sh "build/system-tests.sh acceptance/frontend/Orders"
						}
					}
				}
			}
			post {
				always {
					cleanWs()
					ws(pwd() + "@tmp") {
						cleanWs()
					}
				}
			}
		}

	}
	post {
		always {
			dockerDBRemove("db-${BUILD_TAG}");
			dockerNetworkRemove("tn-${BUILD_TAG}");
			dockerVolumeRemove();
			cleanWs()
			wipeWorkspaces() // Always run this last
		}
	}
}

def dockerNetworkCreate(name) {
	dockerNetworkRemove(name)
	sh "docker network create ${name}"
}

def dockerNetworkRemove(name) {
	sh "docker network rm ${name} || true"
}

def dockerDBRun(hostname, password, network) {
	dockerDBRemove(hostname)
	sh "docker run --name ${hostname} --network ${network} -e MYSQL_ROOT_PASSWORD=${password} -d mysql:5.7"
}

def dockerDBRemove(hostname) {
	sh "docker stop ${hostname} || true"
	sh "docker rm ${hostname} || true"
}

def dockerVolumeRemove() {
	sh "docker volume prune -f"
}

def wipeWorkspaces()
{
	dir('/var/lib/jenkins/workspace'){
		sh 'pwd'
		sh 'find -maxdepth 1 -name $(basename ${WORKSPACE})@\\* ! -name $(basename ${WORKSPACE})@tmp'

		sh 'sudo rm -rf -- $(basename ${WORKSPACE})_*/'
		// Removes the clones & tmp folders created for parallelization
		sh 'find -maxdepth 1 -name $(basename ${WORKSPACE})@\\* ! -name $(basename ${WORKSPACE})@tmp -exec sudo rm -rf {} \\;'
	}

	// Wipes the main workspace afterwards leaving an empty dir
	deleteDir()
}