#!/bin/sh
# Compila libutilsram.so (acelerador nativo del nucleo)
# -ffp-contract=off: sin FMA para que el resultado sea bit-exact con el generador PHP
set -e
cd "$(dirname "$0")/.."
gcc -O2 -ffp-contract=off -shared -fPIC -o libutilsram.so src/libutilsram.c
echo "OK: libutilsram.so"
