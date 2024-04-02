start:
	/opt/homebrew/opt/php@8.3/bin/php -S localhost:8000 -t ../../../


tailwind-download:
ifeq ($(shell uname -s), Darwin)
	curl -sLO https://github.com/tailwindlabs/tailwindcss/releases/latest/download/tailwindcss-macos-arm64
else ifeq ($(shell uname -s), Linux)
	curl -sLO https://github.com/tailwindlabs/tailwindcss/releases/latest/download/tailwindcss-linux-x64
endif
	mv tailwindcss-* tailwindcss
	chmod +x tailwindcss

watch:
	./tailwindcss -i ./css/tailwind.css -o ./static/tailwind.css --watch

tailwind-build:
	./tailwindcss -i ./css/tailwind.css -o ./static/tailwind.css --minify
