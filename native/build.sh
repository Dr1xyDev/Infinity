#!/bin/sh
# Builds memis.so (FFI native acceleration for the Infinity core)
# Usage: sh ./native/build.sh [host|cross-aarch64|android|android-arm32]
set -e

DIR="$(cd "$(dirname "$0")" && pwd)"
LIB="$DIR/lib"
SRC="$DIR/src"
INC="$DIR/include"
mkdir -p "$LIB"

MODE="${1:-host}"

SOURCES="$SRC/simplex.c $SRC/field.c $SRC/layout.c $SRC/version.c $SRC/light.c $SRC/heightmap.c $SRC/generator.c $SRC/packing.c"

case "$MODE" in
	host)
		cc -O3 -fPIC -shared -o "$LIB/memis.so" $SOURCES -I"$INC"
		echo "memis.so (host) OK -> $LIB/memis.so"
		;;
	cross-aarch64)
		aarch64-linux-gnu-gcc -O3 -fPIC -shared -static-libgcc \
			-o "$LIB/memis-linux-aarch64.so" $SOURCES -I"$INC"
		echo "memis-linux-aarch64.so OK -> $LIB/memis-linux-aarch64.so"
		;;
	android)
		${ANDROID_NDK_ROOT:?ANDROID_NDK_ROOT not set}/toolchains/llvm/prebuilt/linux-x86_64/bin/aarch64-linux-android21-clang \
			-O3 -fPIC -shared -o "$LIB/memis-android-arm64-v8a.so" $SOURCES -I"$INC"
		echo "memis-android-arm64-v8a.so OK -> $LIB/memis-android-arm64-v8a.so"
		;;
	android-arm32)
		${ANDROID_NDK_ROOT:?ANDROID_NDK_ROOT not set}/toolchains/llvm/prebuilt/linux-x86_64/bin/armv7a-linux-androideabi21-clang \
			-O3 -fPIC -shared -o "$LIB/memis-android-armeabi-v7a.so" $SOURCES -I"$INC"
		echo "memis-android-armeabi-v7a.so OK -> $LIB/memis-android-armeabi-v7a.so"
		;;
	*)
		echo "Usage: sh ./native/build.sh [host|cross-aarch64|android|android-arm32]" >&2
		exit 1
		;;
esac
