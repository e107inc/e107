# The WebDriver suite's browser: Chrome's headless shell, floating on Chrome's
# stable channel so an upstream Chromium change surfaces here, driven by the
# chromedriver Chrome for Testing publishes for the same version, or else the
# newest one for the same major, which chromedriver supports.
FROM docker.io/chromedp/headless-shell:stable

RUN set -eu; export DEBIAN_FRONTEND=noninteractive; \
    apt-get update -qq; \
    apt-get install -y -qq --no-install-recommends \
        ca-certificates curl unzip libglib2.0-0t64 libxcb1 libdbus-1-3 >/dev/null; \
    rm -rf /var/lib/apt/lists/*; \
    cft=https://storage.googleapis.com/chrome-for-testing-public; \
    version=$(headless-shell --version); \
    version=${version##* }; \
    curl -fsSLo /tmp/chromedriver.zip "$cft/$version/linux64/chromedriver-linux64.zip" \
        || curl -fsSLo /tmp/chromedriver.zip "$cft/$(curl -fsSL \
            "https://googlechromelabs.github.io/chrome-for-testing/LATEST_RELEASE_${version%%.*}")/linux64/chromedriver-linux64.zip"; \
    unzip -qj /tmp/chromedriver.zip chromedriver-linux64/chromedriver -d /usr/local/bin; \
    rm /tmp/chromedriver.zip; \
    ln -s /headless-shell/headless-shell /usr/bin/chromium

EXPOSE 9515
ENTRYPOINT ["chromedriver", "--port=9515", "--allowed-ips=", "--allowed-origins=*"]
