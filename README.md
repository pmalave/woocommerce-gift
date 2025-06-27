# WooCommerce Producto Regalo

Este plugin permite añadir automáticamente un producto de regalo al carrito de WooCommerce cuando el cliente añade un producto activador específico.

Funciona sin necesidad de cupones y es compatible con la mayoría de temas, incluyendo Divi. Además, incluye un panel de configuración en el escritorio de WordPress.

---

## ✨ Características

- Regala automáticamente un producto cuando se añade otro concreto al carrito.
- Panel de configuración visual (sin tocar código).
- Compatible con temas personalizados como Divi.
- Muestra el precio original tachado y `0,00 €` como regalo.
- Evita duplicados y se elimina si se borra el producto activador.
- Solución visual con JavaScript para plantillas que ignoran filtros nativos.

---

## ⚙️ Requisitos

- WordPress 5.8+
- WooCommerce 5.0+
- PHP 7.4 o superior

---

## 🚀 Instalación

1. Descarga el plugin en formato `.zip`.
2. Ve a **Plugins → Añadir nuevo** en tu panel de WordPress.
3. Sube el archivo ZIP y actívalo.
4. Ve a **Ajustes → Producto Regalo** para configurarlo.

---

## 🔧 Configuración

En el panel de administración del plugin podrás:

- Seleccionar el **producto activador**.
- Seleccionar el **producto a regalar**.

El sistema se encargará automáticamente de añadir y mostrar el regalo cuando corresponda.

---

## 📦 Estructura del plugin

- `woocommerce_before_calculate_totals`: Para asignar el precio del regalo.
- `woocommerce_get_item_data`: Para mostrar un mensaje debajo del nombre del producto.
- `woocommerce_cart_item_price` / `woocommerce_cart_item_subtotal`: Para mostrar el precio tachado.
- `JavaScript en wp_footer`: Para solucionar temas que no respetan filtros de WooCommerce.

---

## ✅ Licencia

Este plugin está bajo licencia [MIT](https://opensource.org/licenses/MIT). Puedes modificarlo y adaptarlo a tus necesidades sin restricción.

---

## ✍️ Autor

**Pablo Malavé**  
[pmalave.com](https://pmalave.com)

---

> Si usas este plugin en producción, te recomiendo hacer pruebas primero en staging y limpiar la caché tras activarlo.
