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
            wp_die( __( 'Access Denied', 'mpr-likebtn' ) );
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
                    'label'   =>  __('Display:', 'mpr-likebtn'),
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
        $value = mpr_get_option( $args['id'], $args['section'], $args['default'] );

        $html  = '<fieldset>';
        $html .= sprintf( '<input type="hidden" name="%1$s[%2$s]" value="" />', $args['section'], $args['id'] );

        $option_count = count( $args['options'] );

        foreach ( $args['options'] as $key => $label ) {
            $checked = isset( $value[$key] ) ? $value[$key] : '0';

            if ( $option_count < 5 ) {
                $html .= '<div>';
            } else {
                $html .= '<div style="display: inline-block; margin-right: 15px;">';
            }
            $html    .= sprintf( '<label for="mpr-%1$s-%2$s-%3$s">', $args['section'], $args['id'], $key );
            $html    .= sprintf( '<input type="checkbox" class="checkbox" id="mpr-%1$s-%2$s-%3$s" name="%1$s[%2$s][%3$s]" value="%3$s" %4$s />', $args['section'], $args['id'], $key, checked( $checked, $key, false ) );
            $html    .= sprintf( '%1$s</label>',  $label );
            $html .= '</div>';
        }

        if ( ! empty( $args['desc'] ) ) {
            $html .= sprintf( '<p class="description">%s</p>', $args['desc'] );
        }

        $html .= '</fieldset>';

        echo $html;
    }

    function callback_select( $args )
    {
        $value = mpr_get_option( $args['id'], $args['section'], $args['default'] );

        $html  = sprintf( '<select name="%1$s[%2$s]">', $args['section'], $args['id'] );

        foreach ( $args['options'] as $key => $label ) {
            $html    .= sprintf( '<option value="%1$s" %2$s />%3$s</option>', $key, selected( $value, $key, false ), $label );
        }

        $html .= '</select>';

        if ( ! empty( $args['desc'] ) ) {
            $html .= sprintf( '<p class="description">%s</p>', $args['desc'] );
        }

        echo $html;
    }

    function callback_checkbox( $args )
    {

        $value = esc_attr( mpr_get_option( $args['id'], $args['section'], $args['default'] ) );
        $html  = '<fieldset>';
        $html  .= sprintf( '<label for="mpr-%1$s-%2$s">', $args['section'], $args['id'] );
        $html  .= sprintf( '<input type="hidden" name="%1$s[%2$s]" value="no" />', $args['section'], $args['id'] );
        $html  .= sprintf( '<input type="checkbox" class="checkbox" id="mpr-%1$s-%2$s" name="%1$s[%2$s]" value="yes" %3$s />', $args['section'], $args['id'], checked( $value, 'yes', false ) );
        $html  .= sprintf( '%1$s</label>', $args['desc'] );
        $html  .= '</fieldset>';

        echo $html;
    }

    function callback_number( $args )
    {
        $value = esc_attr( mpr_get_option( $args['id'], $args['section'], $args['default'] ) );
        $html  = sprintf( '<label for="mpr-%1$s-%2$s">', $args['section'], $args['id'] );
        $html  .= sprintf( '<input type="number" name="%1$s[%2$s]" value="%3$s" />', $args['section'], $args['id'], $value);
        $html  .= '</label>';

        if ( ! empty( $args['desc'] ) ) {
            $html .= sprintf( '<p class="description">%s</p>', $args['desc'] );
        }

        echo $html;
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
                $section['desc'] = '<div class="inside">' . $section['desc'] . '</div>';
                $callback = function() use ( $section ) {
                    echo str_replace( '"', '\"', $section['desc'] );
                };
            } else if ( isset( $section['callback'] ) ) {
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
