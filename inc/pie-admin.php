<?php

if (!defined('ABSPATH')) {
    echo 'ACCESS DENIED';
    exit;
} else {

    //Checking if the class exists
    if (!class_exists('pie_admin')) {
        //Creating Pie admin class
        class pie_admin
        {
            public function __construct()
            {
                add_action('init', array($this, 'pie_create_post_type')); //Registering Pie custom post type
                add_action('add_meta_boxes', array($this, 'pie_add_meta_boxes')); //Adding meta boxes
                add_action('save_post', array($this, 'save_pie_meta_boxes')); //Saving meta boxes
            }

            //Creating Pie custom post type
            public function pie_create_post_type()
            {
                $args = array(
                    'labels' => array(
                        'name' => __('Pie'),
                        'singular_name' => __('Pie'),
                    ),
                    'public' => true,
                    'has_archive' => true,
                    'supports' => array(
                        'title',
                        'editor',
                        'revisions',
                        'author',
                        'thumbnail',
                        'custom-fields',
                        'post-formats'
                    ),
                    'show_in_rest' => true,
                    'rewrite' => array('slug' => 'pie'),
                );
                register_post_type('pie', $args);
            }

            //Creating Meta Boxes
            public function pie_add_meta_boxes()
            {
                add_meta_box('pie_type', 'Pie Type', array($this, 'pie_type_field'), 'pie', 'normal', 'default');
                add_meta_box('pie_ingredients', 'Ingredients', array($this, 'pie_ingredients_field'), 'pie', 'normal', 'default');
            }

            //Creating the Pie Type Field
            public function pie_type_field($post)
            {
                $value = get_post_meta($post->ID, 'pie_type', true);
                echo '<label for="Pie Type">' . __('Pie Type') . '</label>';
                echo '<input type="text" name="pie_type" value="' . esc_attr($value) . '" />';
            }

            //Creating the Ingredients Field
            public function pie_ingredients_field($post)
            {
                $value_json = get_post_meta($post->ID, 'pie_ingredients', true);
                $value = $value_json ? json_decode($value_json, true) : array();
                $value_string = implode(', ', $value);
                echo '<label for="pie_ingredients">' . __('Ingredients (comma-separated)', 'textdomain') . '</label>';
                echo '<textarea id="pie_ingredients" name="pie_ingredients" rows="5" cols="50" style="width:100%;">';
                echo $value_string;
                echo '</textarea>';
            }

            //Saving the meta box
            public function save_pie_meta_boxes($post_id)
            {
                // Sanitize and save the Pie Type
                if (isset($_POST['pie_type'])) {
                    update_post_meta($post_id, 'pie_type', sanitize_text_field($_POST['pie_type']));
                }

                // Sanitize and save the Ingredients
                if (isset($_POST['pie_ingredients'])) {
                    $ingredients = array_map('sanitize_text_field', explode(',', trim($_POST['pie_ingredients'])));
                    update_post_meta($post_id, 'pie_ingredients', json_encode($ingredients));
                }
            }
        }
        new pie_admin();
    }
}
