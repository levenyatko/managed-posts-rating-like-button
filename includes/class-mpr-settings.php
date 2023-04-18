<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists('MPR_Settings') ) :

class MPR_Settings
{
    public function __construct()
    {
        $this->define_hooks();
    }

    private function define_hooks()
    {
        // add menu item in wp-admin
        add_action( 'admin_menu', [$this, 'add_settings_page'] );

        // register plugin options
        add_action( 'admin_init', [$this, 'settings_admin_init'] );

    }

    /*
     * Add admin menus
     */
    public function add_settings_page()
    {
        add_submenu_page(
            'mpr-plugin-page',
            __( 'MPR Plugin Settings', 'mpr-likebtn' ),
            __( 'Settings', 'mpr-likebtn' ),
            'manage_mpr_log',
            'mpr-plugin-settings',
            [$this, 'display_settings_page']
        );

    }

    public function display_settings_page()
    {
        if ( ! current_user_can( 'manage_mpr_log' ) ) {
            wp_die( esc_html__( 'Access Denied', 'mpr-likebtn' ) );
        }

        include_once MPR_PLUGIN_DIR . 'partials/admin/settings-general.php';
    }

    /**
     * Setting sections.
     *
     * @return array
     */
    public function get_sections()
    {
        return [
            [
                'id'    => 'mpr_general_section',
                'title' => '',
            ]
        ];
    }

    /**
     * Setting fields.
     *
     * @return array
     */
    public function get_fields()
    {
        $support_post_types = get_post_types( array( 'public' => true ), 'objects' );

        $post_type_options = [];

        foreach($support_post_types as $post_type) {
            if( 'attachment' !== $post_type->name ) {
                $post_type_options[ $post_type->name ] = $post_type->label;
            }
        }

        return [
            'mpr_general_section' => [
                [
                    'name'    => 'post_types',
                    'label'   =>  __('Enable for:', 'mpr-likebtn'),
                    'desc'    => '',
                    'type'    => 'multicheck',
                    'options' => $post_type_options,
                    'default' => [
                        'post' => 'post',
                    ]
                ],
                [
                    'name'    => 'like_method',
                    'label'   =>  __('Allow to vote:', 'mpr-likebtn'),
                    'desc'    => '',
                    'type'    => 'select',
                    'options' => [
                        'all'        => __('All users by IP', 'mpr-likebtn'),
                        'logged'     => __('Logged only', 'mpr-likebtn'),
                    ],
                    'default' => 'all',
                ],
                [
                    'name'    => 'max_voting_count',
                    'label'   => __('Max. number of votes:', 'mpr-likebtn'),
                    'desc'    => __('Allowed voting count from non-logged or non-admin role user', 'mpr-likebtn'),
                    'type'    => 'number',
                    'default' => 1,
                ],
                [
                    'name'    => 'display_type',
                    'label'   => __('Display:', 'mpr-likebtn'),
                    'desc'    => __("To display the rating manually you can use [mpr-button] shortcode or mpr_button(['id' => 0, 'disabled' => false, 'return' => true ]) function", 'mpr-likebtn'),
                    'type'    => 'select',
                    'options' => [
                        'before'    => __('Before Content', 'mpr-likebtn'),
                        'after'     => __('After Content', 'mpr-likebtn'),
                        'manually'  => __('Manually', 'mpr-likebtn'),
                    ],
                    'default' => 'manually',
                ],
                [
                    'name'    => 'disabled_btn_display',
                    'label'   => __('Display if disabled:', 'mpr-likebtn'),
                    'desc'    => __("Do we need to show voting button for disabled post types (for manually added buttons only)?", 'mpr-likebtn'),
                    'type'    => 'select',
                    'options' => [
                        'hide'          => __('Hide', 'mpr-likebtn'),
                        'show_disabled' => __('Show disabled', 'mpr-likebtn'),
                        'show_enabled'  => __('Show', 'mpr-likebtn'),
                    ],
                    'default' => 'hide',
                ],
                [
                    'name'    => 'clear_all_settings',
                    'label'   => __( 'Clear all', 'mpr-likebtn' ),
                    'desc'    => __( 'Clear all settings and log when uninstalling plugin', 'mpr-likebtn' ),
                    'type'    => 'checkbox',
                    'default' => 'no',
                ]
            ]
        ];

    }

    function callback_multicheck( $args )
    {
        $field_id   = sprintf( 'mpr-%1$s-%2$s', $args['section'], $args['id'] );
        $field_name = sprintf( '%1$s[%2$s]', $args['section'], $args['id']);
        $value      = mpr_get_option( $args['id'], $args['section'], $args['default'] );
        ?>
            <fieldset>
                <?php
                    foreach ( $args['options'] as $key => $label ) {
                        $item_checked = isset( $value[$key] ) ? $value[$key] : '0';
                        $item_id = $field_id . '-' . $key;
                        $item_name = $field_name . '[' . $key . ']';
                        ?>
                            <label for="<?php echo esc_attr($item_id) ?>">
                                <input type="checkbox"
                                       class="checkbox"
                                       id="<?php echo esc_attr($item_id) ?>"
                                       name="<?php echo esc_attr($item_name) ?>"
                                       value="<?php echo esc_attr($key) ?>"
                                    <?php checked( $item_checked, $key ); ?>
                                />
                                <?php echo esc_html($label); ?>
                            </label>
                        <?php
                    }
                    $this->show_field_description($args);
                ?>
            </fieldset>
        <?php
    }

    function callback_select( $args )
    {
        $field_id   = sprintf( 'mpr-%1$s-%2$s', $args['section'], $args['id'] );
        $field_name = sprintf( '%1$s[%2$s]', $args['section'], $args['id']);
        $value      = mpr_get_option( $args['id'], $args['section'], $args['default'] );

        ?>
            <select name="<?php echo esc_attr($field_name) ?>" id="<?php echo esc_attr($field_id) ?>" autocomplete="off">
                <?php
                    foreach ( $args['options'] as $key => $label ) {
                        ?>
                        <option value="<?php echo esc_attr($key) ?>" <?php selected( $value, $key ); ?> />
                            <?php echo esc_html($label); ?>
                        </option>
                        <?php
                    }
                ?>
            </select>
        <?php

        $this->show_field_description($args);

    }

    function callback_checkbox( $args )
    {
        $field_id   = sprintf( 'mpr-%1$s-%2$s', $args['section'], $args['id'] );
        $field_name = sprintf( '%1$s[%2$s]', $args['section'], $args['id']);
        $value      = mpr_get_option( $args['id'], $args['section'], $args['default'] );
        ?>
        <fieldset>
            <label for="<?php echo esc_attr($field_id) ?>">
                <input type="checkbox"
                       class="checkbox"
                       id="<?php echo esc_attr($field_id) ?>"
                       name="<?php echo esc_attr($field_name) ?>"
                       value="yes"
                    <?php checked( $value, 'yes' ); ?>
                />
                <?php echo esc_html($args['desc']); ?>
            </label>
            <?php $this->show_field_description($args); ?>
        </fieldset>
        <?php
    }

    function callback_number( $args )
    {
        $field_id   = sprintf( 'mpr-%1$s-%2$s', $args['section'], $args['id'] );
        $field_name = sprintf( '%1$s[%2$s]', $args['section'], $args['id']);
        $value      = mpr_get_option( $args['id'], $args['section'], $args['default'] );

        ?>
        <label for="<?php echo esc_attr($field_id) ?>">
           <input type="number"
                  id="<?php echo esc_attr($field_id) ?>"
                  name="<?php echo esc_attr($field_name) ?>"
                  value="<?php echo esc_attr($value) ?>"/>
        </label>
        <?php

        $this->show_field_description($args);

    }

    private function show_field_description($field_args)
    {
        if ( ! empty( $field_args['desc'] ) ) {
            ?>
            <p class="description"><?php echo esc_html($field_args['desc']); ?></p>
            <?php
        }
    }

    function settings_admin_init()
    {

        $sections = $this->get_sections();

        //register settings sections
        foreach ( $sections as $section ) {
            if ( false == get_option( $section['id'] ) ) {
                add_option( $section['id'] );
            }

            if ( isset($section['desc']) && !empty($section['desc']) ) {
                $section['desc'] = '<p>' .$section['desc'] . '</p>';
                $callback = function() use ( $section ) {
                    $desc = str_replace( '"', '\"', $section['desc'] );
                    echo wp_kses_post( $desc );
                };
            } elseif ( isset( $section['callback'] ) ) {
                $callback = $section['callback'];
            } else {
                $callback = null;
            }

            add_settings_section( $section['id'] . '_0', $section['title'], $callback, $section['id'] );
        }

        $settings = $this->get_fields();

        //register settings fields
        foreach ( $settings as $section => $field ) {
            $i = 0;
            $next_section_group = $section . '_' . $i;

            foreach ( $field as $option ) {
                $name = isset( $option['name'] ) ? $option['name'] : 'No name';
                $type = isset( $option['type'] ) ? $option['type'] : 'text';
                $label = isset( $option['label'] ) ? $option['label'] : '';
                $callback = isset( $option['callback'] ) ? $option['callback'] : array( $this, 'callback_' . $type );

                $args = array(
                    'id'                => $name,
                    'class'             => isset( $option['class'] ) ? $option['class'] : $name,
                    'label_for'         => "{$section}[{$name}]",
                    'desc'              => isset( $option['desc'] ) ? $option['desc'] : '',
                    'name'              => $label,
                    'section'           => $section,
                    'size'              => isset( $option['size'] ) ? $option['size'] : null,
                    'options'           => isset( $option['options'] ) ? $option['options'] : '',
                    'default'           => isset( $option['default'] ) ? $option['default'] : '',
                    'sanitize_callback' => isset( $option['sanitize_callback'] ) ? $option['sanitize_callback'] : '',
                    'type'              => $type,
                    'placeholder'       => isset( $option['placeholder'] ) ? $option['placeholder'] : '',
                );
                add_settings_field( "{$section}[{$name}]", $label, $callback, $section, $next_section_group, $args );
            }
        }

        // creates our settings in the options table
        foreach ( $sections as $section ) {
            register_setting( $section['id'], $section['id']);
        }

    }

}

endif;
