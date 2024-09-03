<?php

// Check if the class exists
if (!class_exists('pie_lookup')) {
    // Create Pie Lookup class
    class pie_lookup
    {

        // Create the shortcode
        public function __construct()
        {
            add_shortcode('pies', array($this, 'create_pie_lookup'));
        }

        public function create_pie_lookup($atts)
        {
            ob_start();

            // Parse shortcode attributes
            $atts = shortcode_atts(
                array(
                    'lookup' => '',        // Default value for lookup
                    'ingredients' => '',   // Default value for ingredients
                    'posts' => '5',        // Default number of posts to display
                ),
                $atts,
                'pies'
            );

            // Extract attributes from shortcode
            $shortcode_lookup = sanitize_text_field($atts['lookup']);
            $shortcode_ingredients = sanitize_text_field($atts['ingredients']);

            // Get values from URL parameters
            $url_lookup = isset($_GET['lookup']) ? sanitize_text_field($_GET['lookup']) : '';
            $url_ingredients = isset($_GET['ingredients']) ? sanitize_text_field($_GET['ingredients']) : '';

            // Use URL parameters if present, otherwise use shortcode attributes
            $lookup = !empty($url_lookup) ? $url_lookup : $shortcode_lookup;
            $ingredients = !empty($url_ingredients) ? $url_ingredients : $shortcode_ingredients;
            $paged = get_query_var('paged') ? get_query_var('paged') : 1;
            $posts_per_page = intval($atts['posts']);

            // Display the form
?>
            <form method="get" action="" class="pies-form-container">
                <label for="lookup">Search by Pie Type:</label>
                <input type="text" name="lookup" id="lookup" value="<?php echo esc_attr($lookup); ?>" />

                <label for="ingredients">Search by Ingredients:</label>
                <input type="text" name="ingredients" id="ingredients" value="<?php echo esc_attr($ingredients); ?>" />

                <button type="submit">Search</button>
            </form>
<?php

            // Arguments for WP Query
            $args = array(
                'post_type' => 'pie',
                'posts_per_page' => $posts_per_page,
                'paged' => $paged,
                'meta_query' => array(),
            );

            // If lookup provided, add additional search queries
            if (!empty($lookup)) {
                $args['meta_query'][] = array(
                    'key' => 'pie_type',
                    'value' => $lookup,
                    'compare' => 'LIKE',
                );
            }

            // If ingredients provided, add additional search queries
            if (!empty($ingredients)) {
                $ingredients_array = array_map('trim', explode(',', $ingredients));
                $meta_queries = array('relation' => 'AND');
                foreach ($ingredients_array as $ingredient) {
                    $meta_queries[] = array(
                        'key' => 'pie_ingredients',
                        'value' => $ingredient,
                        'compare' => 'LIKE',
                    );
                }
                $args['meta_query'][] = $meta_queries;
            }

            // Query the pies
            $pies_query = new WP_Query($args);

            // Check if pagination adjustment is needed
            $total_pages = $pies_query->max_num_pages;

            // Redirect to first page if page number is greater than total pages
            if ($paged > $total_pages) {
                $new_query_vars = array(
                    'paged' => 1,
                    'lookup' => $lookup,
                    'ingredients' => $ingredients,
                );

                $new_url = add_query_arg($new_query_vars, home_url($_SERVER['REQUEST_URI']));

                wp_redirect($new_url);
                exit();
            }

            // Display the query results
            if ($pies_query->have_posts()) {
                echo '<ul class="pies-list">';
                while ($pies_query->have_posts()) {
                    $pies_query->the_post();
                    $post_url = get_permalink();
                    echo '<li><a href="' . esc_url($post_url) . '">' . get_the_title() . '</a></li>';
                }
                echo '</ul>';

                // Pagination
                $big = 9999;
                echo paginate_links(array(
                    'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
                    'format' => '?paged=%#%',
                    'current' => max(1, $paged),
                    'total' => $pies_query->max_num_pages,
                ));
            } else {
                echo 'No pies found.';
            }

            // Reset post data
            wp_reset_postdata();

            return ob_get_clean();
        }
    }

    new pie_lookup();
}
