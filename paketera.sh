#!/usr/bin/env bash
# Bygger överlämningspaketet till Chris ur repot. Ingenting kopieras för hand:
# det som ligger i zippen är exakt det som ligger i repot vid körningen.
#
#   bash paketera.sh            → ~/Desktop/Ampy-fore-efter-handover.zip
set -euo pipefail
ROT="$(cd "$(dirname "$0")" && pwd)"
UT="$HOME/Desktop/Ampy-fore-efter-handover"
ZIP="$UT.zip"
rm -rf "$UT" "$ZIP"; mkdir -p "$UT/images"

cp "$ROT/IMPLEMENTATION.md" "$UT/00-START-HERE.md"
cp -R "$ROT/dist" "$UT/dist"
cp -R "$ROT/acf" "$UT/acf"
cp "$ROT"/images/*.jpg "$ROT/images/manifest.csv" "$ROT/images/wp-media-import.sh" "$UT/images/"
cp "$ROT/FOTOPROTOKOLL.md" "$ROT/KODGRANSKNING.md" "$UT/"

( cd "$(dirname "$UT")" && zip -qr "$ZIP" "$(basename "$UT")" -x '*.DS_Store' )
rm -rf "$UT"
echo "$ZIP  ($(du -h "$ZIP" | cut -f1))"
