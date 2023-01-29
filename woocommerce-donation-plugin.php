<?php
/*
  Plugin Name: Woocommerce Plugin Donation
  Plugin URI: https://github.com/bianqui149/woocommerce-donation-plugin
  Description: This plugins adds a donation field on the cart page. It does it before the total cart calculation
  Version: 1.0.0
  Author: Bianqui Julian
  Author URI: https://github.com/bianqui149/woocommerce-donation-plugin
 */

add_action('woocommerce_before_cart_totals', 'woocommerce_after_cart_table_cp');

if (!function_exists('woocommerce_after_cart_table_cp')) {

    /**
     * It checks if the donation exists in the cart, if it doesn't, it displays the donation form.
     */
    function woocommerce_after_cart_table_cp()
    {

        global $woocommerce;

        if (!donation_exists_for_cart_page()) {
            unset($woocommerce->session->donation_cp);
        }

        if (!donation_exists_for_cart_page()) {
?>
            <tr class="donation-block">
                <td colspan="6">

                    <p class="message">
                    <h2>
                        <?php echo esc_html("Add Donation"); ?>
                    </h2>
                    </p>

                    <form action="" method="post" id="donation_form">
                        <div class="input text">
                            <input type="number" name="donation_cp" id="donation_cp" class="input-text" min="1" value placeholder="Enter the Amount" />
                            <br> <input type="submit" name="donate-btn" class="btn" value="<?php echo esc_html("Donate"); ?>" />
                        </div>
                    </form>
                
                </td>
            </tr>
            <br>
<?php
        }
    }
}


if (!function_exists('donation_exists_for_cart_page')) {

    /**
     * If the cart contains a product with the slug "donation_cp", return true.
     */
    function donation_exists_for_cart_page()
    {

        global $woocommerce;

        if (sizeof($woocommerce->cart->get_cart()) > 0) {

            foreach ($woocommerce->cart->get_cart() as $cart_item_key => $values) {

                $_product = $values['data'];

                $product_slug = get_post_field('post_name', $_product->id);

                if ($product_slug == "donation_cp") {
                    return true;
                }
            }
        }
        return false;
    }
}

add_action('template_redirect', 'process_donation_cp');

if (!function_exists('process_donation_cp')) {

    /**
     * If the user has submitted a donation amount, add it to the session and add the donation product
     * to the cart
     */
    function process_donation_cp()
    {
        global $woocommerce;

        $donation = isset($_POST['donation_cp']) && !empty($_POST['donation_cp']) ? floatval($_POST['donation_cp']) : false;

        if ($donation && isset($_POST['donate-btn'])) {
            // add to session
            if ($donation > 0) {
                $woocommerce->session->donation_cp = $donation;
                if (sizeof($woocommerce->cart->get_cart()) > 0) {
                    $product = get_page_by_path('donation_cp', OBJECT, 'product');
                    $woocommerce->cart->add_to_cart($product->ID);
                }
            }
        }
    }
}


add_filter('woocommerce_free_price_html', 'change_free_text_cp', 12, 2);

if (!function_exists('change_free_text_cp')) {

    /**
     * If the product slug is donation_cp, and the session variable donation_cp is set, then return the
     * string "Donation added" instead of the price.
     * 
     * @param price The price of the product.
     * @param product_object The product object.
     * 
     * @return The price of the product.
     */
    function change_free_text_cp($price, $product_object)
    {

        global $woocommerce;

        if (!is_admin()) {

            if (isset($product_object->id)) {
                $product_slug = get_post_field('post_name', $product_object->id);

                if ($product_slug == "donation_cp") {

                    if (isset($woocommerce->session->donation_cp)) {
                        if ($woocommerce->session->donation_cp) {
                            return 'Donation  added';
                        }
                    }
                }
            }
        }

        return $price;
    }
}


add_filter('woocommerce_get_price', 'get_price_cp', 10, 2);

if (!function_exists('get_price_cp')) {

    /**
     * If the product slug is donation_cp, then return the value of the session variable donation_cp,
     * otherwise return the price
     * 
     * @param price The price of the product.
     * @param product The product object.
     * 
     * @return The price of the product.
     */
    function get_price_cp($price, $product)
    {

        global $woocommerce;

        $product_slug = get_post_field('post_name', $product->id);

        if ($product_slug == "donation_cp") {

            if (isset($_POST['donation_cp'])) {
                return isset($woocommerce->session->donation_cp) ? floatval($woocommerce->session->donation_cp) : 0;
            }
            return isset($woocommerce->session->donation_cp) ? floatval($woocommerce->session->donation_cp) : 0;
        }

        return $price;
    }
}
