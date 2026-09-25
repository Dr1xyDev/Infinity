#!/bin/sh
# Compila memis.so (aceleracion nativa FFI del nucleo Infinity)
# Uso: sh ./native/build.sh [host|cross-aarch64|android|android-arm32]
set -e

DIR="$(cd "$(dirname "$0")" && pwd)"
LIB="$DIR/lib"
mkdir -p "$LIB"

MODE="${1:-host}"

case "$MODE" in
	host)
		cc -O3 -fPIC -shared -o "$LIB/memis.so" "$DIR/memis.c"
		echo "memis.so (host) OK -> $LIB/memis.so"
		;;
	cross-aarch64)
		aarch64-linux-gnu-gcc -O3 -fPIC -shared -static-libgcc \
			-o "$LIB/memis-linux-aarch64.so" "$DIR/memis.c"
		echo "memis-linux-aarch64.so OK -> $LIB/memis-linux-aarch64.so"
		;;
	android)
		${ANDROID_NDK_ROOT:?ANDROID_NDK_ROOT no definido}/toolchains/llvm/prebuilt/linux-x86_64/bin/aarch64-linux-android21-clang \
			-O3 -fPIC -shared -o "$LIB/memis-android-arm64-v8a.so" "$DIR/memis.c"
		echo "memis-android-arm64-v8a.so OK -> $LIB/memis-android-arm64-v8a.so"
		;;
	android-arm32)
		${ANDROID_NDK_ROOT:?ANDROID_NDK_ROOT no definido}/toolchains/llvm/prebuilt/linux-x86_64/bin/armv7a-linux-androideabi21-clang \
			-O3 -fPIC -shared -o "$LIB/memis-android-armeabi-v7a.so" "$DIR/memis.c"
		echo "memis-android-armeabi-v7a.so OK -> $LIB/memis-android-armeabi-v7a.so"
		;;
	*)
		echo "Uso: sh ./native/build.sh [host|cross-aarch64|android|android-arm32]" >&2
		exit 1
		;;
esac
