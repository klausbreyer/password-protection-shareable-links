# Makefile

# Variables
PLUGIN_FILE = password-protection-shareable-links.php
README_FILE = readme.txt

HOME_DIR := $(HOME)
SOURCE_DIR := $(HOME_DIR)/versioned/wordpress/wp-content/plugins/password-protection-shareable-links/
SVN_ROOT := $(HOME_DIR)/versioned/password-protection-shareable-links-svn/
TRUNK_DIR := $(SVN_ROOT)/trunk/
TAGS_DIR := $(SVN_ROOT)/tags/
EXCLUDE_FILE := $(SOURCE_DIR)/rsync-exclude.txt
REPO_URL := https://plugins.svn.wordpress.org/password-protection-shareable-links

# Define default shell
SHELL := /bin/bash

# Kill any process using port 8000
kill:
	lsof -t -i tcp:8000 | xargs kill -9 || true

# Start local server and watch for changes
start: kill
	/opt/homebrew/opt/php@8.3/bin/php -S localhost:8000 -t ../../../ & ./tailwindcss -i ./styles.css -o ./css/password-protection-shareable-links.css --watch

# Download Tailwind CSS based on OS
tailwind-download:
ifeq ($(shell uname -s), Darwin)
	curl -sLO https://github.com/tailwindlabs/tailwindcss/releases/latest/download/tailwindcss-macos-arm64
	mv tailwindcss-macos-arm64 tailwindcss
else ifeq ($(shell uname -s), Linux)
	curl -sLO https://github.com/tailwindlabs/tailwindcss/releases/latest/download/tailwindcss-linux-x64
	mv tailwindcss-linux-x64 tailwindcss
endif
	chmod +x tailwindcss

# Watch Tailwind CSS changes
watch:
	./tailwindcss -i ./styles.css -o ./css/password-protection-shareable-links.css --watch

# Build Tailwind CSS
tailwind-build:
	./tailwindcss -i ./styles.css -o ./css/password-protection-shareable-links.css --minify

# Convert .po files to .mo files
convert-po-mo:
	for file in languages/*.po; do \
		msgfmt -o $${file%.po}.mo $$file; \
	done

# Build the plugin (CSS and translations)
build: tailwind-build convert-po-mo

# Package the plugin into a zip file
package:
	rm -f $$(basename $$(pwd)).zip
	zip -r $$(basename $$(pwd)).zip ./* \
		-x "*wordpress-stubs.php" \
		-x "*tailwindcss" \
		-x "*password-protection-shareable-links.zip" \
		-x "*.git*" \
		-x "*Makefile" \
		-x "*composer.json" \
		-x "*composer.lock" \
		-x "*package-lock.json" \
		-x "*package.json" \
		-x "*node_modules*" \
		-x "tailwind.config.js" \
		-x "styles.css" \
		-x ".gitignore"

# Bump the version number
bump:
ifndef TYPE
	$(error TYPE is not set. Usage: make bump TYPE=patch|minor|major)
endif
	@echo "Bumping $(TYPE) version..."
	@CURRENT_VERSION=$$(grep -m1 'Version:' $(PLUGIN_FILE) | sed 's/.*Version: //'); \
	MAJOR=$$(echo $$CURRENT_VERSION | cut -d. -f1); \
	MINOR=$$(echo $$CURRENT_VERSION | cut -d. -f2); \
	PATCH=$$(echo $$CURRENT_VERSION | cut -d. -f3); \
	if [ "$(TYPE)" = "patch" ]; then \
		NEW_PATCH=$$(($$PATCH + 1)); \
		NEW_VERSION="$$MAJOR.$$MINOR.$$NEW_PATCH"; \
	elif [ "$(TYPE)" = "minor" ]; then \
		NEW_MINOR=$$(($$MINOR + 1)); \
		NEW_VERSION="$$MAJOR.$$NEW_MINOR.0"; \
	elif [ "$(TYPE)" = "major" ]; then \
		NEW_MAJOR=$$(($$MAJOR + 1)); \
		NEW_VERSION="$$NEW_MAJOR.0.0"; \
	else \
		echo "Invalid TYPE. Use patch, minor, or major."; \
		exit 1; \
	fi; \
	echo "New version: $$NEW_VERSION"; \
	sed -i '' "s/^\\([[:space:]]*\\*[[:space:]]*Version:\\)[[:space:]]*.*/\\1 $$NEW_VERSION/" $(PLUGIN_FILE); \
	sed -i '' "s/^\\(Stable tag:\\)[[:space:]]*.*/\\1 $$NEW_VERSION/" $(README_FILE)

# SVN sync
svn-sync:
	@echo "Syncing files to SVN..."
	rsync -av --delete --exclude-from='$(EXCLUDE_FILE)' './' '$(TRUNK_DIR)' \
		--exclude '*.git*' \
		--exclude 'Makefile' \
		--exclude 'tailwindcss' \
		--exclude 'node_modules' \
		--exclude 'styles.css' \
		--exclude 'tailwind.config.js' \
		--exclude 'package.json' \
		--exclude 'package-lock.json' \
		--exclude 'composer.json' \
		--exclude 'composer.lock' \
		--exclude '.gitignore' \
		--exclude '*.zip' \
		--exclude 'npm-debug.log'

# SVN commit
svn-commit:
ifndef MSG
	$(error MSG is not set. Usage: make svn-commit MSG="Your commit message")
endif
	@echo "Committing to SVN with message: $(MSG)"
	cd '$(TRUNK_DIR)' && \
	svn add --force * --auto-props --parents --depth infinity -q && \
	svn status | grep '^!' | awk '{print $$2}' | xargs svn delete 2>/dev/null || true && \
	svn commit -m "$(MSG)"

# SVN tag
svn-tag:
	@VERSION=$${VERSION:-$$(grep -m1 'Version:' $(PLUGIN_FILE) | sed 's/.*Version: //')}; \
	echo "Tagging version $$VERSION in SVN..."; \
	svn copy "$(REPO_URL)/trunk" "$(REPO_URL)/tags/$$VERSION" -m "Tagging version $$VERSION for release"

# Combined release target
release:
ifndef TYPE
	$(error TYPE is not set. Usage: make release TYPE=patch|minor|major MSG="Release message")
endif
ifndef MSG
	$(error MSG is not set. Usage: make release TYPE=patch|minor|major MSG="Release message")
endif
	@echo "Starting release process..."
	$(MAKE) bump TYPE=$(TYPE)
	$(MAKE) build
	$(MAKE) svn-sync
	@CURRENT_VERSION=$$(grep -m1 'Version:' $(PLUGIN_FILE) | sed 's/.*Version: //'); \
	echo "Current version: $$CURRENT_VERSION"; \
	$(MAKE) svn-commit MSG="$(MSG)"
	$(MAKE) svn-tag

# Default target
.PHONY: all
all: release
