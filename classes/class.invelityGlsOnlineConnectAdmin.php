<?php

class InvelityGlsOnlineConnectAdmin
{
    private $launcher;
    private $acctivationMessage;

    /**
     * Adds menu items and page
     * Gets options from database
     */
    public function __construct(InvelityGlsOnlineConnect $launcher)
    {
        $this->launcher = $launcher;
        if (is_admin()) {
            add_action('admin_menu', [$this, 'add_plugin_page']);
            add_action('admin_init', [$this, 'page_init']);
        }
        $this->options = get_option('invelity_gls_export_options');

    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        add_submenu_page(
            'invelity-plugins',
            __('Gls online connect', $this->launcher->getPluginSlug()),
            __('Gls online connect', $this->launcher->getPluginSlug()),
            'manage_options',
            'invelity-gls-export',
            [$this, 'create_admin_page']
        );
    }

    private function getRemoteAd()
    {
        $invelityIkrosInvoicesad = get_transient('invelity-gls-export-ad');
        if (!$invelityIkrosInvoicesad) {
            $query = esc_url_raw(add_query_arg([], 'https://licenses.invelity.com/plugins/invelity-gls-export/invelityad.json'));
            $response = wp_remote_get($query, ['timeout' => 20, 'sslverify' => false]);
            $response = wp_remote_retrieve_body($response);
            set_transient('invelity-gls-export-ad', $response, 86400);/*5 min*/
            $invelityIkrosInvoicesad = $response;
        }
        return json_decode($invelityIkrosInvoicesad, true);
    }


    public
    function create_admin_page()
    {
        // Set class property
        $this->options = get_option('invelity_gls_export_options');
        ?>
        <div class="wrap invelity-plugins-namespace">
            <h2>
                <?= $this->launcher->getPluginName() ?>
            </h2>

            <form method="post" action="<?= admin_url() ?>options.php">
                <div>
                    <?php
                    settings_fields('invelity_gls_online_connect_options_group');
                    do_settings_sections('invelity-gls-online-connect-setting-admin');
                    submit_button();
                    ?>
                </div>
                <div>
                    <?php
                    $adData = $this->getRemoteAd();
                    if ($adData) {
                        ?>
                        <a href="<?= $adData['adDestination'] ?>" target="_blank">
                            <img src="<?= $adData['adImage'] ?>">
                        </a>
                        <?php
                    }
                    ?>
                </div>
            </form>

        </div>
        <?php
    }

    /**
     * Register individual setting options and option sections
     */
    public
    function page_init()
    {
        register_setting(
            'invelity_gls_online_connect_options_group', // Option group
            'invelity_gls_export_options', // Option name
            [$this, 'sanitize'] // Sanitize
        );

        add_settings_section(
            'setting_section_1', // ID
            __('Connection settings', $this->launcher->getPluginSlug()), // Title
            [$this, 'print_section_info'], // Callback
            'invelity-gls-online-connect-setting-admin' // Page
        );

        add_settings_section(
            'setting_section_2', // ID
            __('Sender settings', $this->launcher->getPluginSlug()), // Title
            null,
            'invelity-gls-online-connect-setting-admin' // Page
        );
        add_settings_section(
            'setting_section_3', // ID
            __('Custom settings', $this->launcher->getPluginSlug()), // Title
            null,
            'invelity-gls-online-connect-setting-admin' // Page
        );

        add_settings_section(
            'setting_section_4', // ID
            __('Services settings', $this->launcher->getPluginSlug()), // Title
            null,
            'invelity-gls-online-connect-setting-admin' // Page
        );

        add_settings_field(
            'country_version',
            __('Country', $this->launcher->getPluginSlug()),
            [$this, 'country_version_callback'],
            'invelity-gls-online-connect-setting-admin',
            'setting_section_1'
        );

        add_settings_field(
            'username',
            __('Username', $this->launcher->getPluginSlug()),
            [$this, 'username_callback'],
            'invelity-gls-online-connect-setting-admin',
            'setting_section_1'
        );
        add_settings_field(
            'password',
            __('Password', $this->launcher->getPluginSlug()),
            [$this, 'password_callback'],
            'invelity-gls-online-connect-setting-admin',
            'setting_section_1'
        );
        add_settings_field(
            'senderid',
            __('Sender ID', $this->launcher->getPluginSlug()),
            [$this, 'senderid_callback'],
            'invelity-gls-online-connect-setting-admin',
            'setting_section_1'
        );
        add_settings_field(
            'sender_name',
            __('Sender name', $this->launcher->getPluginSlug()),
            [$this, 'sender_name_callback'],
            'invelity-gls-online-connect-setting-admin',
            'setting_section_2'
        );
        add_settings_field(
            'sender_address',
            __('Sender address', $this->launcher->getPluginSlug()),
            [$this, 'sender_address_callback'],
            'invelity-gls-online-connect-setting-admin',
            'setting_section_2'
        );
        add_settings_field(
            'sender_city',
            __('Sender City', $this->launcher->getPluginSlug()),
            [$this, 'sender_city_callback'],
            'invelity-gls-online-connect-setting-admin',
            'setting_section_2'
        );
        add_settings_field(
            'sender_zip',
            __('Sender postcode', $this->launcher->getPluginSlug()),
            [$this, 'sender_zip_callback'],
            'invelity-gls-online-connect-setting-admin',
            'setting_section_2'
        );
//        add_settings_field(
//            'sender_country',
//            __('Sender country', $this->launcher->getPluginSlug()),
//            [$this, 'sender_country_callback'],
//            'invelity-gls-online-connect-setting-admin',
//            'setting_section_2'
//        );
        add_settings_field(
            'sender_contact',
            __('Sender contact name', $this->launcher->getPluginSlug()),
            [$this, 'sender_contact_callback'],
            'invelity-gls-online-connect-setting-admin',
            'setting_section_2'
        );
        add_settings_field(
            'sender_contact_name',
            __('Sender contact name', $this->launcher->getPluginSlug()),
            [$this, 'sender_contact_name_callback'],
            'invelity-gls-online-connect-setting-admin',
            'setting_section_2'
        );
        add_settings_field(
            'sender_phone',
            __('Sender phone', $this->launcher->getPluginSlug()),
            [$this, 'sender_phone_callback'],
            'invelity-gls-online-connect-setting-admin',
            'setting_section_2'
        );
        add_settings_field(
            'sender_email',
            __('Sender email', $this->launcher->getPluginSlug()),
            [$this, 'sender_email_callback'],
            'invelity-gls-online-connect-setting-admin',
            'setting_section_2'
        );
        add_settings_field(
            'pcount',
            __('Number of stamps per order', $this->launcher->getPluginSlug()),
            [$this, 'pcount_callback'],
            'invelity-gls-online-connect-setting-admin',
            'setting_section_2'
        );
        add_settings_field(
            'clientref',
            __('Client ref.', $this->launcher->getPluginSlug()),
            [$this, 'clientref_callback'],
            'invelity-gls-online-connect-setting-admin',
            'setting_section_3'
        );

        add_settings_field(
            'fds',
            __('FDS', $this->launcher->getPluginSlug()),
            [$this, 'fds_callback'],
            'invelity-gls-online-connect-setting-admin',
            'setting_section_4'
        );
        add_settings_field(
            'fds',
            __('Flex delivery service (FDS)', $this->launcher->getPluginSlug()),
            [$this, 'fds_callback'],
            'invelity-gls-online-connect-setting-admin',
            'setting_section_4'
        );
        add_settings_field(
            'fss',
            __('Flex delivery SMS service (FSS)', $this->launcher->getPluginSlug()),
            [$this, 'fss_callback'],
            'invelity-gls-online-connect-setting-admin',
            'setting_section_4'
        );
        add_settings_field(
            'sm2',
            __('PreAdvice Service (SM2)', $this->launcher->getPluginSlug()),
            [$this, 'sm2_callback'],
            'invelity-gls-online-connect-setting-admin',
            'setting_section_4'
        );
    }


    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public
    function sanitize(
        $input
    ) {
        $new_input = [];

        if (isset($_POST['invelity_gls_export_options']['country_version'])) {
            $new_input['country_version'] = sanitize_text_field($_POST['invelity_gls_export_options']['country_version']);
        }
        if (isset($input['username'])) {
            $new_input['username'] = sanitize_text_field($input['username']);
        }
        if (isset($input['password'])) {
            $new_input['password'] = sanitize_text_field($input['password']);
        }
        if (isset($input['senderid'])) {
            $new_input['senderid'] = sanitize_text_field($input['senderid']);
        }
        if (isset($input['sender_name'])) {
            $new_input['sender_name'] = sanitize_text_field($input['sender_name']);
        }
        if (isset($input['sender_address'])) {
            $new_input['sender_address'] = sanitize_text_field($input['sender_address']);
        }
        if (isset($input['sender_city'])) {
            $new_input['sender_city'] = sanitize_text_field($input['sender_city']);
        }
        if (isset($input['sender_zip'])) {
            $new_input['sender_zip'] = sanitize_text_field($input['sender_zip']);
        }
//        if (isset($input['sender_country'])) {
//            $new_input['sender_country'] = sanitize_text_field($input['sender_country']);
//        }
        if (isset($input['sender_contact'])) {
            $new_input['sender_contact'] = sanitize_text_field($input['sender_contact']);
        }
        if (isset($input['sender_phone'])) {
            $new_input['sender_phone'] = sanitize_text_field($input['sender_phone']);
        }
        if (isset($input['sender_contact_name'])) {
            $new_input['sender_contact_name'] = sanitize_text_field($input['sender_contact_name']);
        }
        if (isset($input['sender_phone'])) {
            $new_input['sender_phone'] = sanitize_text_field($input['sender_phone']);
        }
        if (isset($input['sender_email'])) {
            $new_input['sender_email'] = sanitize_text_field($input['sender_email']);
        }
        if (isset($input['pcount'])) {
            $new_input['pcount'] = sanitize_text_field($input['pcount']);
        }
        if (isset($input['clientref'])) {
            $new_input['clientref'] = sanitize_text_field($input['clientref']);
        }

        if (isset($input['fds'])) {
            $new_input['fds'] = sanitize_text_field($input['fds']);
        }
        if (isset($input['fss'])) {
            $new_input['fss'] = sanitize_text_field($input['fss']);
        }
        if (isset($input['sm2'])) {
            $new_input['sm2'] = sanitize_text_field($input['sm2']);
        }

        return $new_input;
    }

    /**
     * Print the Section text
     */
    public
    function print_section_info()
    {
        print __('Enter your settings below:', $this->launcher->getPluginSlug());
    }

    public
    function country_version_callback()
    {
        ?>
        <input type="radio" name="invelity_gls_export_options[country_version]"
               value="sk" <?= isset($this->options['country_version']) && esc_attr($this->options['country_version']) == 'sk' ? 'checked' : '' ?>>GLS Slovakia
        &nbsp;&nbsp;&nbsp;
        <input type="radio" name="invelity_gls_export_options[country_version]"
               value="cz" <?= isset($this->options['country_version']) && esc_attr($this->options['country_version']) == 'cz' ? 'checked' : '' ?>>GLS Czech Republic
        <?php
    }

    public
    function username_callback()
    {
        printf(
            '<input type="text" id="username" name="invelity_gls_export_options[username]" value="%s" />',
            isset($this->options['username']) ? esc_attr($this->options['username']) : ''
        );
    }

    public
    function password_callback()
    {
        printf(
            '<input type="text" id="password" name="invelity_gls_export_options[password]" value="%s" />',
            isset($this->options['password']) ? esc_attr($this->options['password']) : ''
        );
    }

    public
    function senderid_callback()
    {
        printf(
            '<input type="text" id="senderid" name="invelity_gls_export_options[senderid]" value="%s" />',
            isset($this->options['senderid']) ? esc_attr($this->options['senderid']) : ''
        );
    }

    public
    function sender_name_callback()
    {
        printf(
            '<input type="text" id="sender_name" name="invelity_gls_export_options[sender_name]" value="%s" />',
            isset($this->options['sender_name']) ? esc_attr($this->options['sender_name']) : ''
        );
    }

    public
    function sender_address_callback()
    {
        printf(
            '<input type="text" id="sender_address" name="invelity_gls_export_options[sender_address]" value="%s" />',
            isset($this->options['sender_address']) ? esc_attr($this->options['sender_address']) : ''
        );
    }

    public
    function sender_city_callback()
    {
        printf(
            '<input type="text" id="sender_city" name="invelity_gls_export_options[sender_city]" value="%s" />',
            isset($this->options['sender_city']) ? esc_attr($this->options['sender_city']) : ''
        );
    }

    public
    function sender_zip_callback()
    {
        printf(
            '<input type="text" id="sender_zip" name="invelity_gls_export_options[sender_zip]" value="%s" />',
            isset($this->options['sender_zip']) ? esc_attr($this->options['sender_zip']) : ''
        );
    }

//    public
//    function sender_country_callback()
//    {
//        printf(
//            '<input type="text" id="sender_country" name="invelity_gls_export_options[sender_country]" value="%s" />',
//            isset($this->options['sender_country']) ? esc_attr($this->options['sender_country']) : ''
//        );
//    }

    public
    function sender_contact_callback()
    {
        printf(
            '<input type="text" id="sender_contact" name="invelity_gls_export_options[sender_contact]" value="%s" />',
            isset($this->options['sender_contact']) ? esc_attr($this->options['sender_contact']) : ''
        );
    }

    public
    function sender_phone_callback()
    {
        printf(
            '<input type="text" id="sender_phone" name="invelity_gls_export_options[sender_phone]" value="%s" />',
            isset($this->options['sender_phone']) ? esc_attr($this->options['sender_phone']) : ''
        );
    }


    public
    function sender_contact_name_callback()
    {
        printf(
            '<input type="text" id="sender_contact_name" name="invelity_gls_export_options[sender_contact_name]" value="%s" />',
            isset($this->options['sender_contact_name']) ? esc_attr($this->options['sender_contact_name']) : ''
        );
    }

    public
    function sender_email_callback()
    {
        printf(
            '<input type="text" id="sender_email" name="invelity_gls_export_options[sender_email]" value="%s" />',
            isset($this->options['sender_email']) ? esc_attr($this->options['sender_email']) : ''
        );
    }

    public
    function pcount_callback()
    {
        printf(
            '<input type="number" id="pcount" name="invelity_gls_export_options[pcount]" value="%s" />',
            isset($this->options['pcount']) && $this->options['pcount'] ? esc_attr($this->options['pcount']) : 1
        );
    }

    public
    function clientref_callback()
    {
        printf(
            '<input type="number" id="clientref" name="invelity_gls_export_options[clientref]" value="%s" />',
            isset($this->options['clientref']) ? esc_attr($this->options['clientref']) : ''
        );
    }
    public
    function fds_callback()
    {
        printf(
            '<input type="checkbox" id="fds" name="invelity_gls_export_options[fds]" %s/>',
            isset($this->options['fds']) && ($this->options['fds'] == 'on') ? 'checked' : ''
        );
    }
    public
    function fss_callback()
    {
        printf(
            '<input type="checkbox" id="fss" name="invelity_gls_export_options[fss]" %s /><p>Please make sure order phone numbers include country codes (+421 / +420)</p><p>If you want to use FSS service, FDS service must be turned on too.</p>',
            isset($this->options['fss']) && ($this->options['fss'] == 'on') ? 'checked' : ''
        );
    }
    public
    function sm2_callback()
    {
        printf(
            '<input type="checkbox" id="sm2" name="invelity_gls_export_options[sm2]" %s /><p>Please make sure order phone numbers include country codes (+421 / +420)</p>',
            isset($this->options['sm2']) && ($this->options['sm2'] == 'on') ? 'checked' : ''
        );
    }


}