kill:
	lsof -t -i tcp:8000 | xargs kill -9

start:
	make kill
	/opt/homebrew/opt/php@8.3/bin/php -S localhost:8000 -t ../../../ & ./tailwindcss -i ./styles.css -o ./dist/styles.css --watch


tailwind-download:
ifeq ($(shell uname -s), Darwin)
	curl -sLO https://github.com/tailwindlabs/tailwindcss/releases/latest/download/tailwindcss-macos-arm64
else ifeq ($(shell uname -s), Linux)
	curl -sLO https://github.com/tailwindlabs/tailwindcss/releases/latest/download/tailwindcss-linux-x64
endif
	mv tailwindcss-* tailwindcss
	chmod +x tailwindcss

watch:
	./tailwindcss -i ./styles.css -o ./dist/styles.css --watch

tailwind-build:
	./tailwindcss -i ./styles.css -o ./dist/styles.css --minify

convert-po-mo:
	for file in languages/*.po; do \
		msgfmt -o $${file%.po}.mo $$file; \
	done

build:
	make tailwind-build
	make convert-po-mo


package:
	rm -f $$(basename $$(pwd)).zip
	zip -r $$(basename $$(pwd)).zip ./* -x "*wordpress-stubs.php" -x "*tailwindcss" -x "*passpass.zip" -x "*.git*" -x "*Makefile" -x "*composer.json" -x "*composer.lock" -x "*package-lock.json" -x "*package.json" -x "*node_modules*" -x "tailwind.config.js" -x "styles.css" -x ".gitignore"
