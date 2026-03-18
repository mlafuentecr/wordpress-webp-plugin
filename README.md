# WebP by Perfecten

![WebP by Perfecten](images/plugin-banner.svg)

Plugin profesional para WordPress desarrollado para **Perfecten**. Convierte imagenes subidas a formato WebP de manera segura, optimiza el peso final y agrega un panel propio dentro del admin de WordPress.

## Caracteristicas

- Conversion automatica a WebP al subir imagenes.
- Conserva el archivo original en disco para evitar roturas en flujos existentes.
- Solo reemplaza el archivo usado por WordPress cuando el WebP es mas pequeno.
- Corrige orientacion EXIF antes de generar el WebP.
- Redimensiona imagenes grandes manteniendo proporcion.
- Panel propio en el admin de WordPress.
- Acceso rapido a configuracion desde la lista de plugins.
- Avisos administrativos sobre estado del plugin y compatibilidad del servidor.

## Requisitos

- WordPress 6.0 o superior.
- PHP 8.0 o superior.
- Extension **Imagick** instalada.
- Soporte **WebP** habilitado dentro de Imagick/ImageMagick.

## Instalacion

1. Copia la carpeta del plugin dentro de `wp-content/plugins/`.
2. Activa **WebP by Perfecten** desde el panel de WordPress.
3. En el menu lateral del admin, abre **WebP by Perfecten**.
4. Activa la conversion automatica y ajusta ancho, alto y calidad segun tu proyecto.

## Estructura

```text
webp-plugin/
|-- index.php
|-- images/
|   |-- index.php
|   `-- plugin-banner.svg
|-- README.md
`-- webp-by-perfecten.php
```

## Como funciona

Cuando se sube una imagen compatible (`jpg`, `png`, `gif`, `heic`, `heif`), el plugin:

1. Abre el archivo con Imagick.
2. Corrige orientacion EXIF si es necesario.
3. Redimensiona la imagen si supera los limites configurados.
4. Genera una version `.webp`.
5. Compara pesos y solo usa el WebP si realmente reduce el tamano.

## Tip recomendado

Para sitios corporativos o ecommerce, una calidad entre `80` y `85` suele dar un balance solido entre compresion y nitidez.

## Soporte tecnico

Marca: **Perfecten**

Si vas a distribuirlo comercialmente, conviene reemplazar las URLs de ejemplo del encabezado del plugin por el dominio real de la compania.
