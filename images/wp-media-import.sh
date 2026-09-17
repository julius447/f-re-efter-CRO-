#!/usr/bin/env bash
# Importerar de tio bilderna till WordPress mediabibliotek med alt-text, titel och
# bildtext redan satta. Kör från WordPress-roten (där wp-config.php ligger):
#
#   bash images/wp-media-import.sh images
#
# Skriver ut attachment-ID per fil — det är de ID:n som ska in i ACF-repeatern
# (fore_bild / efter_bild). Kräver WP-CLI. Kör en gång; kör du igen får du dubbletter.
set -euo pipefail
DIR="${1:-$(dirname "$0")}"
command -v wp >/dev/null || { echo "wp (WP-CLI) saknas i PATH"; exit 1; }

printf "%-32s " 'elcentral-byte-01-fore.jpg'
wp media import "$DIR/elcentral-byte-01-fore.jpg" --title='Byte av elcentral 01 – före' --alt='Gammalt proppskåp med skruvsäkringar på blå bakskiva, före byte av elcentral' --caption='Före: proppskåp med skruvsäkringar' --porcelain
printf "%-32s " 'elcentral-byte-01-efter.jpg'
wp media import "$DIR/elcentral-byte-01-efter.jpg" --title='Byte av elcentral 01 – efter' --alt='Två nya elcentraler med automatsäkringar och jordfelsbrytare på samma bakskiva, efter byte utfört av Ampy' --caption='Efter: två nya elcentraler, samma plats' --porcelain
printf "%-32s " 'elcentral-byte-02-fore.jpg'
wp media import "$DIR/elcentral-byte-02-fore.jpg" --title='Byte av elcentral 02 – före' --alt='Gammalt proppskåp med skruvsäkringar och handskrivna gruppmärkningar på orange vägg, före byte av elcentral' --caption='Före: proppskåp med skruvsäkringar' --porcelain
printf "%-32s " 'elcentral-byte-02-efter.jpg'
wp media import "$DIR/elcentral-byte-02-efter.jpg" --title='Byte av elcentral 02 – efter' --alt='Två nya elcentraler med automatsäkringar och märkta grupper på samma vägg, efter byte utfört av Ampy' --caption='Efter: två nya elcentraler, samma plats' --porcelain
printf "%-32s " 'elcentral-byte-03-fore.jpg'
wp media import "$DIR/elcentral-byte-03-fore.jpg" --title='Byte av elcentral 03 – före' --alt='Gammalt proppskåp med handskrivna gruppmärkningar i nisch, före byte av elcentral' --caption='Före: proppskåp i nisch' --porcelain
printf "%-32s " 'elcentral-byte-03-efter.jpg'
wp media import "$DIR/elcentral-byte-03-efter.jpg" --title='Byte av elcentral 03 – efter' --alt='Nya elcentraler med automatsäkringar i samma nisch, efter byte utfört av Ampy' --caption='Efter: nya elcentraler i samma nisch' --porcelain
printf "%-32s " 'elcentral-byte-04-fore.jpg'
wp media import "$DIR/elcentral-byte-04-fore.jpg" --title='Byte av elcentral 04 – före' --alt='Gammalt proppskåp med kabelrör på vit vägg, före byte av elcentral' --caption='Före: proppskåp med kabelrör' --porcelain
printf "%-32s " 'elcentral-byte-04-efter.jpg'
wp media import "$DIR/elcentral-byte-04-efter.jpg" --title='Byte av elcentral 04 – efter' --alt='Nya elcentraler med automatsäkringar på samma vägg, efter byte utfört av Ampy' --caption='Efter: nya elcentraler, samma vägg' --porcelain
printf "%-32s " 'elcentral-byte-05-fore.jpg'
wp media import "$DIR/elcentral-byte-05-fore.jpg" --title='Byte av elcentral 05 – före' --alt='Lösa ledare i väggen efter att det gamla skåpet tagits ner, före ny elcentral' --caption='Före: gamla skåpet nedtaget' --porcelain
printf "%-32s " 'elcentral-byte-05-efter.jpg'
wp media import "$DIR/elcentral-byte-05-efter.jpg" --title='Byte av elcentral 05 – efter' --alt='Ny elcentral med automatsäkringar och öppen lucka, utfört av Ampy' --caption='Efter: ny elcentral' --porcelain
