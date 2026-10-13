#!/bin/sh
# PHP's sendmail_path in the self-contained image: each message becomes a file
# in /var/mail/e107 instead of leaving the container.
set -eu
cat > "$(mktemp --suffix=.eml "/var/mail/e107/$(date +%Y%m%d-%H%M%S)-XXXXXX")"
