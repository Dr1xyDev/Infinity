#!/bin/sh
# Compilar NATIVO en Termux (Android ARM64) o en cualquier Linux ARM64 (Cobalt/etc).
# En Termux primero:  pkg install clang
set -e
cd "$(dirname "$0")/.."
mkdir -p out
if command -v clang >/dev/null 2>&1; then CC=clang; else CC=gcc; fi
ARCH="$(uname -m)"
case "$ARCH" in
	aarch64|arm64) ;;
	*) echo "AVISO: uname -m = $ARCH (no es ARM64). El .so no servira en tu ARM64." ;;
esac
if [ -n "$TERMUX_VERSION" ] || [ -d /data/data/com.termux ]; then OS=android; else OS=linux; fi
OUT="out/libutilsram-$OS-aarch64.so"
# -ffp-contract=off es CRITICO en ARM64: sin el, el compilador usa FMA y el
# resultado deja de ser bit-exact con el generador PHP (mundos distintos).
$CC -O2 -ffp-contract=off -shared -fPIC -o "$OUT" src/libutilsram.c
echo "OK: $OUT"
# Copia como libutilsram.so para que PHP-FFI la cargue directo
cp "$OUT" libutilsram.so && echo "OK: libutilsram.so (copiada para FFI)"
