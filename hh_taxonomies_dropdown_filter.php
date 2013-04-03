<?php
	/*
	Plugin Name: HH taxonomies dropdown filter
	Plugin URI: http://studio.hamburg-hram.de
	Description: Enables dropdown filters by custom taxonomies on custom post types archive pages.
	Version: 0.1.0
	Author: Vladimir Sklyar
	Author URI: http://imgf.ru
	*/

	add_action( 'restrict_manage_posts', 'todo_restrict_manage_posts' );
	add_filter( 'parse_query', 'todo_convert_restrict' );
	add_filter( 'parse_query', 'override_is_tax_on_post_search' );

	function todo_restrict_manage_posts () {
		global $typenow;

		$args = array( 'public' => true , '_builtin' => false );
		$post_types = get_post_types( $args );

		if ( in_array( $typenow , $post_types ) ) {
			$filter = get_object_taxonomies( $typenow );

			foreach ($filter as $tax_slug) {
				//foreach($tax_slug as $tax_slug){
				$tax_obj = get_taxonomy( $tax_slug );
				wp_dropdown_categories( array(
					'show_option_all' => __( 'Show All ' . $tax_obj->label ),
					'taxonomy'        => $tax_slug,
					'name'            => $tax_obj->name,
					'orderby'         => 'name',
					'selected'        => $_GET[ $tax_obj->query_var ],
					'hierarchical'    => $tax_obj->hierarchical,
					'show_count'      => true,
					'hide_empty'      => false
				) );
				//}
			}
		}
	}

	function todo_convert_restrict ( $query ) {

		global $pagenow;
		global $typenow;

		if ( $pagenow == 'edit.php' ) {
			$filters = get_object_taxonomies( $typenow );
			foreach ( $filters as $tax_slug ) {
				$var = &$query->query_vars[ $tax_slug ];
				if ( isset( $var ) ) {
					$term = get_term_by( 'id' , $var , $tax_slug );
					$var = $term->slug;
				}
			}
		}

	}

	function override_is_tax_on_post_search ( $query ) {

		global $pagenow;
		$qv = &$query->query_vars;
		if ( $pagenow == 'edit.php' && isset( $qv['taxonomy'] ) && isset( $qv['s'] ) ) {
			$query->is_tax = true;
		}

	}



