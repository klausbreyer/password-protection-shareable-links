kill:
	lsof -t -i tcp:8000 | xargs kill -9

start:
	make kill
	/opt/homebrew/opt/php@8.3/bin/php -S localhost:8000 -t ../../../ & ./tailwindcss -i ./styles.css -o ./css/password-protection-shareable-links.css --watch


tailwind-download:
ifeq ($(shell uname -s), Darwin)
	curl -sLO https://github.com/tailwindlabs/tailwindcss/releases/latest/download/tailwindcss-macos-arm64
else ifeq ($(shell uname -s), Linux)
	curl -sLO https://github.com/tailwindlabs/tailwindcss/releases/latest/download/tailwindcss-linux-x64
endif
	mv tailwindcss-* tailwindcss
	chmod +x tailwindcss

watch:
	./tailwindcss -i ./styles.css -o ./css/password-protection-shareable-links.css --watch

tailwind-build:
	./tailwindcss -i ./styles.css -o ./css/password-protection-shareable-links.css --minify

convert-po-mo:
	for file in languages/*.po; do \
		msgfmt -o $${file%.po}.mo $$file; \
	done

build:
	make tailwind-build
	make convert-po-mo


package:
	rm -f $$(basename $$(pwd)).zip
	zip -r $$(basename $$(pwd)).zip ./* -x "*wordpress-stubs.php" -x "*tailwindcss" -x "*password-protection-shareable-links.zip" -x "*.git*" -x "*Makefile" -x "*composer.json" -x "*composer.lock" -x "*package-lock.json" -x "*package.json" -x "*node_modules*" -x "tailwind.config.js" -x "styles.css" -x ".gitignore"

# SVN Stuff

# Variables
HOME_DIR := $(HOME)
SOURCE_DIR := $(HOME_DIR)/versioned/wordpress/wp-content/plugins/password-protection-shareable-links/
SVN_ROOT := $(HOME_DIR)/versioned/password-protection-shareable-links-svn/
TRUNK_DIR := $(SVN_ROOT)/trunk/
TAGS_DIR := $(SVN_ROOT)/tags/
EXCLUDE_FILE := $(SOURCE_DIR)rsync-exclude.txt
REPO_URL := https://plugins.svn.wordpress.org/password-protection-shareable-links

# Sync files with rsync
# Usage: make svn-sync
svn-sync:
	@echo "Syncing files..."
	rsync -av --delete --exclude-from='$(EXCLUDE_FILE)' '$(SOURCE_DIR)' '$(TRUNK_DIR)'

# Commit changes to SVN
# Usage: make svn-commit MSG="Your commit message"
svn-commit:
ifndef MSG
	$(error MSG is not set. Usage: make svn-commit MSG="Your commit message")
endif
	@echo "Committing with message: $(MSG)"
	cd '$(TRUNK_DIR)' && \
	svn add --force * --auto-props --parents --depth infinity -q && \
	svn status | grep '^!' | awk '{print $$2}' | xargs svn delete 2>/dev/null && \
	svn commit -m "$(MSG)"

# Tag a new version
# Usage: make svn-tag VERSION=1.2.11
svn-tag:
ifndef VERSION
	$(error VERSION is not set. Usage: make svn-tag VERSION=1.2.11)
endif
	@echo "Tagging version $(VERSION)..."
	svn copy '$(REPO_URL)/trunk' '$(REPO_URL)/tags/$(VERSION)' -m "Tagging version $(VERSION) for release"


# Release target
# Usage: make release VERSION=1.2.11 MSG="Release message"
release: svn-sync svn-commit svn-tag
# Makefile
# Makefile

PLUGIN_FILE=password-protection-shareable-links.php
README_FILE=readme.txt

bump_patch:
	@echo "Bumping patch version..."
	@VERSION=$$(grep -m1 'Version:' $(PLUGIN_FILE) | sed 's/.*Version: //'); \
	MAJOR=$$(echo $$VERSION | cut -d. -f1); \
	MINOR=$$(echo $$VERSION | cut -d. -f2); \
	PATCH=$$(echo $$VERSION | cut -d. -f3); \
	NEW_PATCH=$$(($$PATCH + 1)); \
	NEW_VERSION="$$MAJOR.$$MINOR.$$NEW_PATCH"; \
	echo "New version: $$NEW_VERSION"; \
	sed -i '' "s/^\([[:space:]]*\*[[:space:]]*Version:\)[[:space:]]*.*/\1 $$NEW_VERSION/" $(PLUGIN_FILE); \
	sed -i '' "s/^\(Stable tag:\)[[:space:]]*.*/\1 $$NEW_VERSION/" $(README_FILE)

bump_minor:
	@echo "Bumping minor version..."
	@VERSION=$$(grep -m1 'Version:' $(PLUGIN_FILE) | sed 's/.*Version: //'); \
	MAJOR=$$(echo $$VERSION | cut -d. -f1); \
	MINOR=$$(echo $$VERSION | cut -d. -f2); \
	NEW_MINOR=$$(($$MINOR + 1)); \
	NEW_VERSION="$$MAJOR.$$NEW_MINOR.0"; \
	echo "New version: $$NEW_VERSION"; \
	sed -i '' "s/^\([[:space:]]*\*[[:space:]]*Version:\)[[:space:]]*.*/\1 $$NEW_VERSION/" $(PLUGIN_FILE); \
	sed -i '' "s/^\(Stable tag:\)[[:space:]]*.*/\1 $$NEW_VERSION/" $(README_FILE)

bump_major:
	@echo "Bumping major version..."
	@VERSION=$$(grep -m1 'Version:' $(PLUGIN_FILE) | sed 's/.*Version: //'); \
	MAJOR=$$(echo $$VERSION | cut -d. -f1); \
	NEW_MAJOR=$$(($$MAJOR + 1)); \
	NEW_VERSION="$$NEW_MAJOR.0.0"; \
	echo "New version: $$NEW_VERSION"; \
	sed -i '' "s/^\([[:space:]]*\*[[:space:]]*Version:\)[[:space:]]*.*/\1 $$NEW_VERSION/" $(PLUGIN_FILE); \
	sed -i '' "s/^\(Stable tag:\)[[:space:]]*.*/\1 $$NEW_VERSION/" $(README_FILE)
