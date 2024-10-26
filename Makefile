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
# Makefile

# Variablen
HOME_DIR := $(HOME)
SOURCE_DIR := $(HOME_DIR)/versioned/wordpress/wp-content/plugins/password-protection-shareable-links/
SVN_ROOT := $(HOME_DIR)/versioned/password-protection-shareable-links-svn/
TRUNK_DIR := $(SVN_ROOT)/trunk/
TAGS_DIR := $(SVN_ROOT)/tags/
EXCLUDE_FILE := $(SOURCE_DIR)rsync-exclude.txt
REPO_URL := https://plugins.svn.wordpress.org/password-protection-shareable-links

PLUGIN_FILE := $(SOURCE_DIR)password-protection-shareable-links.php
README_FILE := $(SOURCE_DIR)readme.txt

# Funktion zum Erhöhen der Versionsnummer
define bump_version
echo "Erhöhe $(1) Version..."; \
VERSION=$$(grep -m1 'Version:' $(PLUGIN_FILE) | sed 's/.*Version:[[:space:]]*//'); \
MAJOR=$$(echo $$VERSION | cut -d. -f1); \
MINOR=$$(echo $$VERSION | cut -d. -f2); \
PATCH=$$(echo $$VERSION | cut -d. -f3); \
if [ "$(1)" = "patch" ]; then \
	PATCH=$$(($$PATCH + 1)); \
elif [ "$(1)" = "minor" ]; then \
	MINOR=$$(($$MINOR + 1)); \
	PATCH=0; \
elif [ "$(1)" = "major" ]; then \
	MAJOR=$$(($$MAJOR + 1)); \
	MINOR=0; \
	PATCH=0; \
fi; \
NEW_VERSION="$$MAJOR.$$MINOR.$$PATCH"; \
echo "Neue Version: $$NEW_VERSION"; \
sed -i '' "s/^\([[:space:]]*\*[[:space:]]*Version:\)[[:space:]]*.*/\1 $$NEW_VERSION/" $(PLUGIN_FILE); \
sed -i '' "s/^\(Stable tag:\)[[:space:]]*.*/\1 $$NEW_VERSION/" $(README_FILE);
endef

# Funktion zum Durchführen des Releases
define do_release
VERSION=$$(grep -m1 'Version:' $(PLUGIN_FILE) | sed 's/.*Version:[[:space:]]*//'); \
echo "Synchronisiere Dateien..."; \
rsync -av --delete --exclude-from='$(EXCLUDE_FILE)' '$(SOURCE_DIR)/' '$(TRUNK_DIR)/'; \
echo "Committe zu SVN..."; \
cd '$(TRUNK_DIR)' && \
svn add --force . --auto-props --parents --depth infinity -q; \
svn status | grep '^!' | awk '{print $$2}' | xargs svn delete 2>/dev/null || true; \
svn commit -m "Release Version $$VERSION"; \
echo "Tagge Version $$VERSION..."; \
svn copy '$(REPO_URL)/trunk' '$(REPO_URL)/tags/$$VERSION' -m "Tagging Version $$VERSION for release";
endef

# Release-Ziele
release-patch:
	@$(call bump_version,patch)
	@$(call do_release)

release-minor:
	@$(call bump_version,minor)
	@$(call do_release)

release-major:
	@$(call bump_version,major)
	@$(call do_release)

# Neue Ziele zum Erhöhen der Version ohne SVN
bump-patch:
	@$(call bump_version,patch)

bump-minor:
	@$(call bump_version,minor)

bump-major:
	@$(call bump_version,major)

.PHONY: release-patch release-minor release-major bump-patch bump-minor bump-major
