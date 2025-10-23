#!/usr/bin/env bash

WORKING_DIR=$(pwd)

# Remove old tmp folder
rm -rf "$WORKING_DIR/tmp"

# Ask for some parameters to install the test env.
echo "Choose a database Name for tests [elementor-tests]:"
read -r DB_NAME
echo "What is your database username [admin]?"
read -r DB_USER
echo "What is your database password [admin]?"
read -r DB_PASS
echo "What is your database host (specify port if needed) [127.0.0.1:PORT]?"
read -r DB_HOST
echo "Choose WordPress version for testing [latest]:"
read -r WP_VERSION

DB_NAME=${DB_NAME:-"elementor-tests"}
DB_USER=${DB_USER:-"admin"}
DB_PASS=${DB_PASS:-"admin"}
DB_HOST=${DB_HOST:-"127.0.0.1"}
WP_VERSION=${WP_VERSION:-"latest"}

WP_TESTS_DIR=${WP_TESTS_DIR-"$WORKING_DIR/tmp/wordpress-tests-lib"}
WP_CORE_DIR=${WP_CORE_DIR-"$WORKING_DIR/tmp/wordpress/"}
ELEMENTOR_PLUGIN_DIR=${ELEMENTOR_PLUGIN_DIR-"$WORKING_DIR/tmp/"}


# Download util with better error handling
download() {
    local url="$1"
    local output="$2"
    
    if [ `which curl` ]; then
        curl --location --fail --show-error --silent --output "$output" "$url"
        local exit_code=$?
    elif [ `which wget` ]; then
        wget -nv -O "$output" "$url"
        local exit_code=$?
    else
        echo "Error: Neither curl nor wget found. Please install one of them."
        exit 1
    fi
    
    if [ $exit_code -ne 0 ]; then
        echo "Error: Failed to download $url"
        rm -f "$output"
        exit 1
    fi
}

## Determine the WP_TEST_TAG.
if [[ $WP_VERSION =~ [0-9]+\.[0-9]+(\.[0-9]+)? ]]; then
	WP_TESTS_TAG="tags/$WP_VERSION"
else
	# http serves a single offer, whereas https serves multiple. we only want one
	download http://api.wordpress.org/core/version-check/1.7/ /tmp/wp-latest.json
	grep '[0-9]+\.[0-9]+(\.[0-9]+)?' /tmp/wp-latest.json
	LATEST_VERSION=$(grep -o '"version":"[^"]*' /tmp/wp-latest.json | sed 's/"version":"//')
	if [[ -z "$LATEST_VERSION" ]]; then
		echo "Latest WordPress version could not be found"
		exit 1
	fi
	WP_TESTS_TAG="tags/$LATEST_VERSION"
fi

set -ex

check_for_svn() {
  if [ ! `which svn` ]; then
    echo 'Please install "svn" and re run this script.'
    echo 'Mac users: `brew install svn`'
    exit 1
  fi
}

install_wp() {
	if [ -d "$WP_CORE_DIR" ]; then
		return;
	fi

	mkdir -p "$WP_CORE_DIR"

	if [ $WP_VERSION == 'latest' ]; then
		local ARCHIVE_NAME='latest'
	else
		local ARCHIVE_NAME="wordpress-$WP_VERSION"
	fi

	download https://wordpress.org/${ARCHIVE_NAME}.tar.gz  /tmp/wordpress.tar.gz
	tar --strip-components=1 -zxmf /tmp/wordpress.tar.gz -C "$WP_CORE_DIR"

	download https://raw.githubusercontent.com/markoheijnen/wp-mysqli/master/db.php "$WP_CORE_DIR/wp-content/db.php"
}

install_test_suite() {
	# portable in-place argument for both GNU sed and Mac OSX sed
	if [[ $(uname -s) == 'Darwin' ]]; then
		local ioption='-i .bak'
	else
		local ioption='-i'
	fi

	# set up testing suite if it doesn't yet exist
	if [ ! -d "$WP_TESTS_DIR" ]; then
		# set up testing suite
		mkdir -p "$WP_TESTS_DIR"/includes
		svn co --quiet https://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/includes/ "$WP_TESTS_DIR/includes/"
	fi

	cd "$WP_TESTS_DIR"

	if [ ! -f wp-tests-config.php ]; then
		download https://develop.svn.wordpress.org/${WP_TESTS_TAG}/wp-tests-config-sample.php "$WP_TESTS_DIR/wp-tests-config.php"
		sed $ioption "s:dirname( __FILE__ ) . '/src/':'$WP_CORE_DIR':" "$WP_TESTS_DIR"/wp-tests-config.php
		sed $ioption "s/youremptytestdbnamehere/$DB_NAME/" "$WP_TESTS_DIR"/wp-tests-config.php
		sed $ioption "s/yourusernamehere/$DB_USER/" "$WP_TESTS_DIR"/wp-tests-config.php
		sed $ioption "s/yourpasswordhere/$DB_PASS/" "$WP_TESTS_DIR"/wp-tests-config.php
		sed $ioption "s|localhost|${DB_HOST}|" "$WP_TESTS_DIR"/wp-tests-config.php
	fi
}

install_db() {
	# parse DB_HOST for port or socket references
	local PARTS=(${DB_HOST//\:/ })
	local DB_HOSTNAME=${PARTS[0]};
	local DB_SOCK_OR_PORT=${PARTS[1]};
	local EXTRA=""

	if ! [ -z $DB_HOSTNAME ] ; then
		if [ $(echo $DB_SOCK_OR_PORT | grep -e '^[0-9]\{1,\}$') ]; then
			EXTRA=" --host=$DB_HOSTNAME --port=$DB_SOCK_OR_PORT --protocol=tcp"
		elif ! [ -z $DB_SOCK_OR_PORT ] ; then
			EXTRA=" --socket=$DB_SOCK_OR_PORT"
		elif ! [ -z $DB_HOSTNAME ] ; then
			EXTRA=" --host=$DB_HOSTNAME --protocol=tcp"
		fi
	fi

	# create database
	mysqladmin create $DB_NAME --user="$DB_USER" --password="$DB_PASS"$EXTRA
}

install_elementor_plugin() {
	echo "Installing Elementor plugin..."
	rm -rf ${ELEMENTOR_PLUGIN_DIR}/elementor
	
	# Download the plugin
	local zip_file="$WORKING_DIR/tmp/elementor.zip"
	rm -f "$zip_file"
	
	echo "Downloading Elementor from WordPress.org..."
	download https://downloads.wordpress.org/plugin/elementor.latest-stable.zip "$zip_file"
	
	# Validate the downloaded file is a valid zip
	if ! unzip -t "$zip_file" >/dev/null 2>&1; then
		echo "Error: Downloaded file is not a valid zip archive"
		echo "File size: $(ls -lh "$zip_file" 2>/dev/null | awk '{print $5}' || echo 'File not found')"
		echo "File type: $(file "$zip_file" 2>/dev/null || echo 'Cannot determine file type')"
		rm -f "$zip_file"
		exit 1
	fi
	
	# Extract the plugin
	echo "Extracting Elementor plugin..."
	if ! unzip -q "$zip_file" -d "${ELEMENTOR_PLUGIN_DIR}"; then
		echo "Error: Failed to extract Elementor plugin"
		rm -f "$zip_file"
		exit 1
	fi
	
	# Clean up
	rm -f "$zip_file"
	echo "Elementor plugin installed successfully"
}

check_for_svn
install_wp
install_test_suite
install_elementor_plugin
install_db
