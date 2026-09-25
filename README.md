# Infinity 👾
Software de server basado en Genisys 
Version v1.1 release
- se añadio soporte php 8.4 completo y únicamente funciona con ese php
- se añadio generación vanilla
- se añadio la posibilidad de cargar plugins vía folder
- se añadieron conandos /makephar <plugin> y /extractphar <plugin_name>
- se añadio soporte de poner motd en Infinity.yml
- se anadio soporte para definir slots java en Infinity.yml
- se añadio soporte pa ocultar software en Infinity.yml
- sistema antiddos y antibot mejorado
- mejora y optimizacion en la jugabilidad
- bioma de montañas mejorada
- bugs de sintaxis arreglados
- Generacion de estructuras ( desert village, normal village y desert piramid ) (```use el plugin de InfintyEstructures para habilitar la extension```)

## ⚙️ Tareas pendientes:
- añadir soporte IA para los mobs
- arreglar otros bugs ( descuriendo )
- Añadir bioma de meseta vanilla ( idea fue descartada para esta versión)

## ⚡ Aceleración nativa (memis.so):
El nucleo acelera sus hot paths CPU mediante `memis.so` (PHP FFI, `pocketmine/utils/Memis.php`):
- skylight de chunks completos (anvil por secciones y mcregion/leveldb flat) en un solo paso nativo
- recálculo de heightmap nativo
- fastNoise2D/3D (simplex + interpolación) para la generación
- empaquetado de heightmap, biomeColors y nibbles

- **Colocación manual**: el `.so` ya NO viene incluido. Copia tú mismo el binario correcto para tu SO/arquitectura a la carpeta **`libs/`** del servidor (se crea sola al arrancar): `memis.so`, o directamente el binario sin renombrar (`memis-android-arm64-v8a.so`, `memis-linux-aarch64.so`, `memis-linux-x86_64.so`…) — el núcleo acepta cualquier `.so` que haya dentro. Alternativa: `INFINITY_NATIVE_LIB=/ruta/memis.so`. Sin el `.so`, el núcleo arranca en modo PHP puro sin problema.
- Si el `.so` existe pero no carga, el boot muestra el motivo: `Memis native acceleration: 0 (PHP puro; no se pudo cargar .../libs/xxx.so (error: ...))` (normalmente es que usaste el binario de otra arquitectura/OS).
- Binarios precompilados: se generan con el workflow **manual** `Build memis.so` (Actions → Run workflow) para `linux-x86_64`, `linux-arm64-cobalt` y `android`, y se hacen **commit** automáticamente al repo en `native/builded/<target>/` (además de quedar como artefactos descargables).
- Verificación de versión de API y **fallback PHP byte-idéntico** si falta o es incompatible
- Estado en el boot: `Memis native acceleration: ...`
- Compilar manualmente: `sh ./native/build.sh` (host) · `cross-aarch64` · `android` (ver workflow `memis-build.yml`, 100% manual)
- Verificar salida byte-idéntica: `php native/verify_memis.php`

## 🛠 Binario PHP 8.4:
https://github.com/Dr1xyDev/PHP-Binaries-Infinity
