<?php

    namespace MPRating;

    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }

if ( ! class_exists('Rest_Api') ) :

    class Rest_Api
    {
        public function __construct()
        {
            $this->define_hooks();
        }

        private function define_hooks()
        {
            add_action( 'rest_api_init', [$this, 'register_rest_routes'] );
        }

        public function register_rest_routes()
        {
            register_rest_route( 'mpr/v1', 'rate', [
                'methods' => 'POST',
                'callback' => [$this, 'maybe_rate_post'],
                'permission_callback' => '__return_true',
                'args' => [
                    'id' => [
                        'description'         => 'Item ID',
                        'required'            => true,
                        'type'                => 'integer',
                    ],
                    'parent_id' => [
                        'description'         => 'The id of the page from which the vote is added',
                        'required'            => true,
                        'type'                => 'integer',
                    ],
                ],
            ]);

            register_rest_route( 'mpr/v1', 'custom-rate', [
                'methods' => 'POST',
                'callback' => [$this, 'add_custom_rate_to_post'],
                'permission_callback' => function () {
                    return current_user_can( 'manage_mpr_log' );
                },
                'args' => [
                    'id' => [
                        'description'         => 'Item ID',
                        'required'            => true,
                        'type'                => 'integer',
                    ],
                    'rate' => [
                        'description'         => 'The added rating value',
                        'required'            => true,
                        'type'                => 'integer',
                    ],
                ],
            ]);

            register_rest_route( 'mpr/v1', 'get-statistic', [
                'methods' => 'GET',
                'callback' => [$this, 'statistic'],
                'permission_callback' => function () {
                    return current_user_can( 'manage_mpr_log' );
                },
                'args' => [
                    'range' => [
                        'description'         => 'Text range to select data',
                        'required'            => true,
                        'type'                => 'string',
                        'validate_callback' => function ($val) {
                            $ranges = \MPRating\Data\Statistic_Data::get_ranges();
                            if ( ! empty($ranges[ $val ]) ) {
                                return true;
                            }
                            return false;
                        },
                    ],
                    'period' => [
                        'required'            => true,
                        'type'                => 'string',
                    ],
                ],
            ]);
        }

        public function maybe_rate_post(\WP_REST_Request $request)
        {
            $result = [ 'success' => false ];

            $post_id = isset( $request['id'] ) ? (int) $request['id'] : 0;
            $parent_id = isset( $request['parent_id'] ) ? (int) $request['parent_id'] : 0;

            $result['post_id'] = $post_id;

            if ( $post_id > 0 && mpr_check_possibility_to_vote($post_id)) {

                $post = get_post($post_id);

                $display = mpr_is_btn_display_enabled( $post );

                // If Valid Post Then We Vote It
                if ($post && !wp_is_post_revision($post) && $display ) {

                    if ( current_user_can('mpr_freely_likes') ) {
                        $result['max_user_rating'] = -1; // unlimited
                    } else {
                        $result['max_user_rating'] = mpr_get_max_voting_count();
                    }

                    $rated = mpr_check_if_post_max_rated($post_id);

                    // if user didn't vote post yet OR
                    // he hasn't voting limits
                    if ( ! $rated || current_user_can('mpr_freely_likes') ) {

                        $new_rating = mpr_process_post_voting( $post_id, 1, $parent_id );
                        $result['new_rating'] = $new_rating;

                        $result['success'] = true;
                        $result['message'] = esc_html__('Thank you, vote accepted!', 'mpr-likebtn');

                    } else { // user can not vote many times - unvote post

                        $result['success'] = false;
                        $result['message'] = esc_html__('You have left the max. number of votes', 'mpr-likebtn');
                    }

                } else {
                    $result['message'] = sprintf(esc_html__('Invalid Post ID (#%s).', 'mpr-likebtn'), $post_id);
                }

            } else {
                $result['message'] = esc_html__("You can't vote for this entry", 'mpr-likebtn');
            }

            return $result;
        }

        public function add_custom_rate_to_post(\WP_REST_Request $request)
        {
            $result = [ 'success' => false ];

            $post_id = isset( $request['id'] ) ? (int) $request['id'] : 0;

            if ( current_user_can( 'manage_mpr_log' ) ) {

                $post = get_post($post_id);

                // If Valid Post Then We Vote It
                if ($post && !wp_is_post_revision($post) && mpr_is_post_type_supported($post->post_type)) {

                    $new_rating = mpr_process_post_voting( $post_id, (int) $request['rate'], -1 );
                    $result['new_rating'] = $new_rating;

                    $result['success'] = true;
                    $result['message'] = esc_html__('Thank you, vote accepted!', 'mpr-likebtn');

                } else {
                    $result['message'] = sprintf(esc_html__('Invalid Post ID (#%s).', 'mpr-likebtn'), $post_id);
                }

            } else {
                $result['message'] = esc_html__('You cannot vote for this entry', 'mpr-likebtn');
            }

            return rest_ensure_response($result);
        }

        public function statistic(\WP_REST_Request $request)
        {
            $result = [ 'success' => false ];

            if ( current_user_can( 'manage_mpr_log' ) ) {

                $params = $request->get_params();

                $statQueryObject = null;

                if ( 'year' == $params['range'] ) {
                    $statQueryObject = new \MPRating\Data\Statistic_Query_Year($params['period']);
                } elseif ( 'month' == $params['range'] ) {
                    $statQueryObject = new \MPRating\Data\Statistic_Query_Month($params['period']);
                }

                if ( ! is_null($statQueryObject) ) {

                    $result_data = [];
                    $result_data['totals'] = $statQueryObject->get_totals();

                    if ( ! empty($result_data['totals']) ) {

                        $screens = apply_filters('mpr_edit_meta_box_screens', []);

                        if ( ! empty($screens)) {
                            foreach ($screens as $screen) {
                                $post_type = get_post_type_object($screen);

                                $result_row = [
                                    'label' => $post_type->label,
                                    'data'  => $statQueryObject->get_totals($screen)
                                ];

                                $result_data['lines'][] = $result_row;
                            }
                        }

                        $result['data']    = $result_data;

                        $result['success'] = true;
                    }
                }

            } else {
                $result['message'] = esc_html__('You are not allowed to see the data.', 'mpr-likebtn');
            }

            return rest_ensure_response($result);
        }
    }

endif;
