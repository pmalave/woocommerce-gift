<?php
/*
Plugin Name: WooCommerce Producto Regalo
Description: Agrega un producto regalo al carrito cuando se añade un producto específico. Incluye dashboard de configuración.
Version: 1.5
Author: Pablo Malavé
Author URI: https://pmalave.com
*/

if (!defined('ABSPATH')) exit;

class WC_Producto_Regalo {

    public function __construct() {
        add_action('admin_menu', [$this, 'crear_menu_admin']);
        add_action('admin_init', [$this, 'registrar_configuraciones']);
        add_action('woocommerce_before_calculate_totals', [$this, 'agregar_producto_regalo'], 10);
        add_action('woocommerce_before_calculate_totals', [$this, 'forzar_precio_regalo'], 20);

        add_filter('woocommerce_get_item_data', [$this, 'mostrar_mensaje_regalo'], 10, 2);
        add_filter('woocommerce_cart_item_price', [$this, 'mostrar_precio_tachado'], 10, 3);
        add_filter('woocommerce_cart_item_subtotal', [$this, 'mostrar_subtotal_regalo'], 10, 3);
        add_filter('woocommerce_cart_get_item_data', [$this, 'mostrar_mensaje_en_cualquier_tema'], 10, 2);

        add_filter('woocommerce_get_price_html', [$this, 'forzar_html_precio_divi'], 20, 2);
        add_filter('woocommerce_cart_item_name', [$this, 'mostrar_precio_en_nombre'], 10, 3);

        // Inyectar JS para forzar actualización si hay regalo
        add_action('wp_footer', [$this, 'inyectar_js_actualizar_carrito']);
    }

    public function crear_menu_admin() {
        add_menu_page('Producto Regalo', 'Producto Regalo', 'manage_options', 'producto-regalo', [$this, 'vista_configuracion'], 'dashicons-gift', 56);
    }

    public function registrar_configuraciones() {
        register_setting('producto_regalo_opciones', 'producto_activador_id');
        register_setting('producto_regalo_opciones', 'producto_regalo_id');
    }

    public function vista_configuracion() {
        $productos = wc_get_products(['limit' => -1, 'orderby' => 'title', 'order' => 'ASC']);
        ?>
        <div class="wrap">
            <h1>Configuración Producto Regalo</h1>
            <form method="post" action="options.php">
                <?php settings_fields('producto_regalo_opciones'); ?>
                <?php do_settings_sections('producto_regalo_opciones'); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Producto que activa el regalo</th>
                        <td>
                            <select name="producto_activador_id">
                                <option value="">Seleccionar producto</option>
                                <?php foreach ($productos as $producto) : ?>
                                    <option value="<?php echo esc_attr($producto->get_id()); ?>" <?php selected(get_option('producto_activador_id'), $producto->get_id()); ?>>
                                        <?php echo esc_html($producto->get_name()); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Producto a regalar</th>
                        <td>
                            <select name="producto_regalo_id">
                                <option value="">Seleccionar producto</option>
                                <?php foreach ($productos as $producto) : ?>
                                    <option value="<?php echo esc_attr($producto->get_id()); ?>" <?php selected(get_option('producto_regalo_id'), $producto->get_id()); ?>>
                                        <?php echo esc_html($producto->get_name()); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public function agregar_producto_regalo($cart) {
        if (is_admin() && !defined('DOING_AJAX')) return;

        $activador_id = get_option('producto_activador_id');
        $regalo_id = get_option('producto_regalo_id');
        if (!$activador_id || !$regalo_id) return;

        $activador_en_carrito = false;
        $regalo_en_carrito = false;

        foreach ($cart->get_cart() as $key => $item) {
            if ($item['product_id'] == $activador_id) $activador_en_carrito = true;
            if ($item['product_id'] == $regalo_id && isset($item['regalo'])) $regalo_en_carrito = $key;
        }

        if ($activador_en_carrito && !$regalo_en_carrito) {
            $cart->add_to_cart($regalo_id, 1, 0, [], ['regalo' => true]);
        }

        if (!$activador_en_carrito && $regalo_en_carrito) {
            $cart->remove_cart_item($regalo_en_carrito);
        }
    }

    public function forzar_precio_regalo($cart) {
        if (is_admin() || did_action('woocommerce_before_calculate_totals') >= 2) return;

        foreach ($cart->get_cart() as $key => $item) {
            if (isset($item['regalo']) && $item['regalo']) {
                $item['data']->set_price(0.00);
            }
        }
    }

    public function mostrar_mensaje_regalo($item_data, $cart_item) {
        if (isset($cart_item['regalo']) && $cart_item['regalo']) {
            $item_data[] = ['name' => 'Promoción', 'value' => '¡Este producto es un regalo!'];
        }
        return $item_data;
    }

    public function mostrar_precio_tachado($price, $cart_item, $cart_item_key) {
    if (isset($cart_item['regalo']) && $cart_item['regalo']) {
        $producto = wc_get_product($cart_item['product_id']);
        $precio_original = wc_price($producto->get_price());
        return '<del>' . $precio_original . '</del> <ins>' . wc_price(0.00) . '</ins>';
    }
    return $price;
    }


    public function mostrar_subtotal_regalo($subtotal, $cart_item, $cart_item_key) {
        if (isset($cart_item['regalo']) && $cart_item['regalo']) {
            $producto = wc_get_product($cart_item['product_id']);
            $precio_original = wc_price($producto->get_price());
            return '<del>' . $precio_original . '</del> <ins>0,00 €</ins>';
        }
        return $subtotal;
    }

    public function mostrar_mensaje_en_cualquier_tema($item_data, $cart_item) {
        if (isset($cart_item['regalo']) && $cart_item['regalo']) {
            $producto = wc_get_product($cart_item['product_id']);
            $item_data[] = [
                'key'   => __('Promoción', 'woocommerce'),
                'value' => '<del>' . wc_price($producto->get_price()) . '</del> <strong>0,00 € (Regalo)</strong>'
            ];
        }
        return $item_data;
    }

  public function forzar_html_precio_divi($html, $product) {
    if (!is_cart() || !WC()->cart) return $html;

    foreach (WC()->cart->get_cart() as $cart_item) {
        if (
            $cart_item['product_id'] === $product->get_id()
            && isset($cart_item['regalo'])
            && $cart_item['regalo']
        ) {
            $precio_original = wc_price($product->get_regular_price());
            $precio_final = wc_price(0.00);
            return '<del>' . $precio_original . '</del> <ins>' . $precio_final . '</ins>';
        }
    }

    return $html;
    }


    public function mostrar_precio_en_nombre($product_name, $cart_item, $cart_item_key) {
    if (isset($cart_item['regalo']) && $cart_item['regalo']) {
        $producto = wc_get_product($cart_item['product_id']);
        $precio_original = wc_price($producto->get_regular_price());
        $precio_final = wc_price(0.00);
        $extra = "<br><small><del>{$precio_original}</del> <ins>{$precio_final}</ins></small>";
        return $product_name . $extra;
    }
    return $product_name;
    }


    public function inyectar_js_actualizar_carrito() {
    if (!is_cart()) return;
    ?>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        // Solo ejecuta una vez
        if (sessionStorage.getItem("carrito_regalo_actualizado")) return;

        const delay = ms => new Promise(res => setTimeout(res, ms));

        async function corregirPrecioRegalo() {
            await delay(600); // Esperamos a que Divi cargue todo
            const filas = [...document.querySelectorAll('.cart_item')];
            const filaRegalo = filas.find(fila =>
                fila.innerText.toLowerCase().includes("¡este producto es un regalo!")
            );

            if (filaRegalo) {
                const celdas = filaRegalo.querySelectorAll("td");
                const precioCelda = filaRegalo.querySelector(".product-price");
                const subtotalCelda = filaRegalo.querySelector(".product-subtotal");

                // Reemplazar precios visualmente
                const precioOriginal = "3,99 €"; // Puedes hacerlo dinámico con PHP si quieres
                const nuevoPrecio = `<del>${precioOriginal}</del> <ins>0,00 €</ins>`;
                
                if (precioCelda) precioCelda.innerHTML = nuevoPrecio;
                if (subtotalCelda) subtotalCelda.innerHTML = nuevoPrecio;

                sessionStorage.setItem("carrito_regalo_actualizado", "true");
            }
        }

        corregirPrecioRegalo();
    });
    </script>
    <?php
}


}

new WC_Producto_Regalo();
