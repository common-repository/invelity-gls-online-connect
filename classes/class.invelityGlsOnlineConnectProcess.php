<?php

class InvelityGlsOnlineConnectProcess
{
    private $launcher;
    private $options;
    public $successful = [];
    public $unsuccessful = [];

    /**
     * Loads plugin textdomain and sets the options attribute from database
     */
    public function __construct(InvelityGlsOnlineConnect $launecher)
    {
        $this->launcher = $launecher;
        load_plugin_textdomain($this->launcher->getPluginSlug(), false, dirname(plugin_basename(__FILE__)) . '/lang/');
        $this->options = get_option('invelity_gls_export_options');
        add_action('admin_footer-edit.php', [$this, 'custom_bulk_admin_footer']);
        add_action('load-edit.php', [$this, 'custom_bulk_action']);
        add_action('admin_notices', [$this, 'custom_bulk_admin_notices']);
    }

    /**
     * Adds option to export invoices to orders page bulk select
     */
    function custom_bulk_admin_footer()
    {
        global $post_type;

        if ($post_type == 'shop_order') {
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function () {
                    jQuery('<option>').val('gls_listky').text('<?php _e('Export GLS online')?>').appendTo("select[name='action']");
                    jQuery('<option>').val('gls_listky').text('<?php _e('Export GLS online')?>').appendTo("select[name='action2']");
                });
            </script>
            <?php
        }
    }

    /**
     * Sets up action to be taken after export option is selected
     * If export is selected, provides export and refreshes page
     * After refresh, notices are shown
     */
    function custom_bulk_action()
    {

        global $typenow;
        $post_type = $typenow;

        if ($post_type == 'shop_order') {
            $wp_list_table = _get_list_table('WP_Posts_List_Table');  // depending on your resource type this could be WP_Users_List_Table, WP_Comments_List_Table, etc
            $action = $wp_list_table->current_action();


            $allowed_actions = ["gls_listky"];
            if (!in_array($action, $allowed_actions)) {
                return;
            }

            // security check
            check_admin_referer('bulk-posts');

            // make sure ids are submitted.  depending on the resource type, this may be 'media' or 'ids'

            if (isset($_REQUEST['post'])) {
                $post_ids = array_map('intval', $_REQUEST['post']);
            }


            if (empty($post_ids)) {
                return;
            }

            // this is based on wp-admin/edit.php
            $sendback = remove_query_arg(['exported', 'untrashed', 'deleted', 'ids'], wp_get_referer());
            if (!$sendback) {
                $sendback = admin_url("edit.php?post_type=$post_type");
            }

            $pagenum = $wp_list_table->get_pagenum();
            $sendback = add_query_arg('paged', $pagenum, $sendback);

            switch ($action) {
                case 'gls_listky':
                    date_default_timezone_set("Europe/Bratislava");

                    require_once($this->launcher->settings['plugin-path'] . 'lib/nusoap/nusoap.php');
                    $_HTTP = !empty($_SERVER['HTTPS']) ? 'https://' : 'http://';

                    if($this->options['country_version'] == 'sk'){
                        $wsdl_path = 'https://online.gls-slovakia.sk/webservices/soap_server.php?wsdl';
                        $senderCountry = 'Slovensko';
                    }elseif($this->options['country_version'] == 'cz'){
                        $wsdl_path = 'https://online.gls-czech.com/webservices/soap_server.php?wsdl';
                        $senderCountry = 'Czech Republic';
                    }


                    $client = new nusoap_client($wsdl_path, 'wsdl');
                    $client->soap_defencoding = 'UTF-8';
                    $client->decode_utf8 = false;

                    $err = $client->getError();
                    if ($err) {
                        echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
                        die();
                    }

                    $pickupdate = date('Y-m-d', strtotime('+1 day'));
                    while ($this->isSviatok($pickupdate)) {
                        $pickupdate = date('Y-m-d', strtotime('+1 day', $pickupdate));
                    }

                    $weekDay = date('w', strtotime($pickupdate));

                    if (($weekDay == 5)) //Check if the day is saturday or not.
                    {
                        $pickupdate = date('Y-m-d', strtotime('+2 day', $pickupdate));
                    } elseif ($weekDay == 6) {
                        $pickupdate = date('Y-m-d', strtotime('+1 day', $pickupdate));
                    }

                    $sendback = remove_query_arg(['exported', 'untrashed', 'deleted', 'ids'], wp_get_referer());
                    if (!$sendback) {
                        $sendback = admin_url("edit.php?post_type=$post_type");
                    }
                    $pagenum = $wp_list_table->get_pagenum();
                    $sendback = add_query_arg('paged', $pagenum, $sendback);

                    $error = false;
                    foreach ($post_ids as $postId) {
                        global $woocommerce;
                        $order = new WC_Order($postId);

                        if ($order->has_shipping_method('local_pickup')) {
                            $this->unsuccessful[] = [
                                'orderId' => $postId,
                                'message' => __('Order has local pickup shipping method', $this->launcher->getPluginSlug()),
                            ];
                            continue;
                        }

                        if(method_exists($order, 'get_payment_method')){
                            $paymentMethod = $order->get_payment_method();
                        }else{
                            $paymentMethod = get_post_meta( $postId, '_payment_method', true );
                        }

                        $total = $order->get_total();


                        $in = [
                            'username'          => $this->options['username'],
                            'password'          => $this->options['password'],
                            'senderid'          => $this->options['senderid'],
                            'sender_name'       => $this->options['sender_name'],
                            'sender_address'    => $this->options['sender_address'],
                            'sender_city'       => $this->options['sender_city'],
                            'sender_zipcode'    => $this->options['sender_zip'],
                            'sender_country'    => $senderCountry,
                            'sender_contact'    => $this->options['sender_contact'],
                            'sender_phone'      => $this->options['sender_phone'],
                            'sender_email'      => $this->options['sender_email'],
                            'consig_name'       => $this->filter_name($order),
                            'consig_address'    => $this->filter_shipping_address_1($order),
                            'consig_city'       => $this->filter_shipping_city($order),
                            'consig_zipcode'    => $this->filter_shipping_postcode($order),
                            'consig_country'    => $this->filter_shipping_country($order),
                            'consig_contact'    => $this->filter_name($order),
                            'consig_phone'      => $this->filter_phone($order),
                            'consig_email'      => $this->filter_email($order),
                            'pcount'            => $this->options['pcount'],
                            'pickupdate'        => $pickupdate,
                            'content'           => $this->filter_order_id($order),
                            'clientref'         => $this->options['clientref'],
                            'codamount'         => ($paymentMethod == 'cod') ? '' . round(floatval($total), 2) : '0',
                            'codref'            => $this->filter_order_id($order),
                            'services'          => [],
                            'printertemplate'   => 'A4_2x2',
                            'printit'           => false,
                            'timestamp'         => date("YmdHis", time()),
                            'hash'              => 'xsd:string',
                            'is_autoprint_pdfs' => false,
                            'customlabel'       => false,
                        ];


                        if ($this->get_shipping_method($order) == 'inv_gls_parcel_shop') {
                            $in['services'][] =   [
                                'code' => 'PSD',
                                'info' =>$this->get_psd_service($order),
                            ];
                        }else{
                            if(isset($this->options['fds']) && $this->options['fds'] == 'on'){
                                $in['services'][] =   [
                                    'code' => 'FDS',
                                ];
                            }
                            if(isset($this->options['fss']) && $this->options['fss'] == 'on'){
                                $in['services'][] =   [
                                    'code' => 'FSS',
                                    'info' => $this->filter_phone($order),
                                ];
                            }
                            if(isset($this->options['sm2']) && $this->options['sm2'] == 'on'){
                                $in['services'][] = [
                                    'code' => 'SM2',
                                    'info' => $this->filter_phone($order),
                                ];
                            }
                        }


                        $in['hash'] = $this->getHash($in);
                        $return = $client->call('printlabel', $in);


                        if ($client->fault) {
                            $this->unsuccessful[] = [
                                'orderId' => $postId,
                                'message' => __("General error", $this->launcher->getPluginSlug()),
                            ];
                        } else {
                            // Check for errors
                            $err = $client->getError();//
                            if ($err || isset($return['errcode'])) {
                                $this->unsuccessful[] = [
                                    'orderId' => $postId,
                                    'message' => __($return['errdesc'], $this->launcher->getPluginSlug()),
                                ];

                            } else {
                                $this->successful[] = [
                                    'orderId' => $postId,
                                ];
                            }
                        }
                    }
                    $sucessfull = urlencode(serialize($this->successful));
                    $unsucessfull = urlencode(serialize($this->unsuccessful));
                    $sendback = add_query_arg(['gls-sucessfull' => $sucessfull, 'gls-unsucessfull' => $unsucessfull], $sendback);
                    $sendback = remove_query_arg([
                        'action',
                        'action2',
                        'tags_input',
                        'post_author',
                        'comment_status',
                        'ping_status',
                        '_status',
                        'post',
                        'bulk_edit',
                        'post_view',
                    ], $sendback);
                    wp_redirect($sendback);
                    exit();
                    break;
                default:
                    return;
            }
        }
    }

    function getHash($data)
    {
        $hashBase = '';
        foreach ($data as $key => $value) {
            if ($key != 'services'
                && $key != 'hash'
                && $key != 'timestamp'
                && $key != 'printit'
                && $key != 'printertemplate'
                && $key != 'customlabel'
            ) {
                $hashBase .= $value;
            }
        }
        return sha1($hashBase);
    }

    function getEaster($year)
    { //Generates holidays. Default: Slovakia
        $sviatky = [];
        $s = ['01-01', '01-06', '', '', '05-01', '05-08', '07-05', '08-29', '09-01', '09-15', '11-01', '11-17', '12-24', '12-25', '12-26'];
        $easter = date('m-d', easter_date($year));
        $sdate = strtotime($year . '-' . $easter);
        $s[2] = date('m-d', strtotime('-2 days', $sdate)); //Firday
        $s[3] = date('m-d', strtotime('+1 day', $sdate)); //Monday
        foreach ($s as $day) {
            $sviatky[] = $year . '-' . $day;
        }
        return $sviatky;
    }

    function isSviatok($date)
    {
        $year = apply_filters('InvelityGlsOnlineConnectProcessIsSviatokYearFilter', date('Y'));
        $thisyear = $this->getEaster($year);
        $nextyear = $this->getEaster($year + 1); //generates next year for delivering after December in actual year
        $sviatky = [];
        $sviatky = array_merge($thisyear, $nextyear);
        $sviatky[] = '2018-10-30';
        $sviatky = apply_filters('InvelityGlsOnlineConnectProcessIsSviatokFilter', $sviatky);
        if (in_array($date, $sviatky)) {
            return true;
        }
        return false;
    }

    /**
     * Displays the notice
     */
    function custom_bulk_admin_notices()
    {
        global $post_type, $pagenow;

        if ($pagenow == 'edit.php' && $post_type == 'shop_order' && (isset($_REQUEST['gls-sucessfull']) || isset($_REQUEST['gls-unsucessfull']))) {
            $sucessfull = unserialize(str_replace('\\', '', urldecode($_REQUEST['gls-sucessfull'])));
            $unsucessfull = unserialize(str_replace('\\', '', urldecode($_REQUEST['gls-unsucessfull'])));
            if (count($sucessfull) != 0) {
                echo "<div class=\"updated\">";
                foreach ($sucessfull as $message) {
                    $messageContent = sprintf(__('Order no. %s Sucessfully generated', $this->launcher->getPluginSlug()), $message['orderId']);
                    echo "<p>{$messageContent}</p>";
                }
                echo "</div>";
            }
            if (count($unsucessfull) != 0) {
                echo "<div class=\"error\">";
                foreach ($unsucessfull as $message) {
                    $messageContent = sprintf(__('Order no. %s Was not generated. Error : %s', $this->launcher->getPluginSlug()), $message['orderId'], $message['message']);
                    echo "<p>{$messageContent}</p>";
                }
                echo "</div>";
            }
        }
    }

    function filter_order_id($order)
    {
        if (!defined('WC_VERSION')) {
            return;
        }
        if (version_compare(WC_VERSION, '3.0', '<')) {
            return $order->id;
        } else {
            return $order->get_id();
        }
    }

    function filter_name($order)
    {
        if (!defined('WC_VERSION')) {
            return;
        }
        if (version_compare(WC_VERSION, '3.0', '<')) {
            return $order->shipping_first_name . ' ' . $order->shipping_last_name;
        } else {
            return $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name();
        }
    }

    function filter_phone($order)
    {
        if (!defined('WC_VERSION')) {
            return;
        }
        if (version_compare(WC_VERSION, '3.0', '<')) {
            if ($order->shipping_phone != '') {
                return $order->shipping_phone;
            } else {
                return $order->billing_phone;
            }
        } else {
            return $order->get_billing_phone();
        }
    }

    function filter_email($order)
    {
        if (!defined('WC_VERSION')) {
            return;
        }
        if (version_compare(WC_VERSION, '3.0', '<')) {
            if ($order->shipping_email != '') {
                return $order->shipping_email;
            } else {
                return $order->billing_email;
            }
        } else {
            return $order->get_billing_email();
        }
    }

    function filter_shipping_address_1($order)
    {
        if (!defined('WC_VERSION')) {
            return;
        }
        if (version_compare(WC_VERSION, '3.0', '<')) {
            return $order->shipping_address_1;
        } else {
            return $order->get_shipping_address_1();
        }
    }

    function filter_shipping_city($order)
    {
        if (!defined('WC_VERSION')) {
            return;
        }
        if (version_compare(WC_VERSION, '3.0', '<')) {
            return $order->shipping_city;
        } else {
            return $order->get_shipping_city();
        }
    }

    function filter_shipping_postcode($order)
    {
        if (!defined('WC_VERSION')) {
            return;
        }
        if (version_compare(WC_VERSION, '3.0', '<')) {
            return $order->shipping_postcode;
        } else {
            return $order->get_shipping_postcode();
        }
    }

    function filter_shipping_country($order)
    {
        if (!defined('WC_VERSION')) {
            return;
        }
        if (version_compare(WC_VERSION, '3.0', '<')) {
            return $order->shipping_country;
        } else {
            return $order->get_shipping_country();
        }
    }

    function get_shipping_method($order)
    {
        $shippingMethods = $order->get_shipping_methods();
        foreach ($shippingMethods as $shippingMethod) {
            return $shippingMethod->get_method_id();
        }

        return false;
    }

    function get_psd_service($order)
    {
        if ( ! defined('WC_VERSION')) {
            return false;
        }

        if (version_compare(WC_VERSION, '3.0', '<')) {
            return get_post_meta($order->id, 'inv_gls_picked_shop_id', true) ? get_post_meta(
                $order->id,
                'inv_gls_picked_shop_id',
                true
            ) : '';
        } else {
            return get_post_meta($order->get_id(), 'inv_gls_picked_shop_id', true) ? get_post_meta(
                $order->get_id(),
                'inv_gls_picked_shop_id',
                true
            ) : '';
        }
    }

}