<?php
	/*
	Plugin Name: Taxonomies dropdown filters
	Plugin URI: http://imgf.ru/
	Description: Enables dropdown filters by custom taxonomies on custom post types archive pages.
	Version: 0.2.0
	Author: Vladimir Sklyar
	Author URI: http://imgf.ru/
	*/



	$versusbassz_taxonomies_dropdown_filter = new Versusbassz_TaxonomiesDropdownFilter;

	add_action( 'init' , array( $versusbassz_taxonomies_dropdown_filter , 'init' ) );



	class Versusbassz_TaxonomiesDropdownFilter {


		public function init (  ) {
			$this->register_hooks();
		}


		public function register_hooks () {
			if ( is_admin() ) {
				add_action( 'restrict_manage_posts' , array( $this , 'print_form_elements' ) );
			}
		}


		public function print_form_elements () {

			global $typenow;

			$post_types = get_post_types( array(
				'public'   => true,
			));

			if ( in_array( $typenow , $post_types ) ) {
				$post_type_taxonomies = get_object_taxonomies( $typenow );

				foreach ( $post_type_taxonomies as $tax_slug ) {

					if ( in_array( $tax_slug , [ 'category' , 'post_tag' , 'post_format' ] ) ) {
						continue;
					}

					$taxonomy = get_taxonomy( $tax_slug );

					$selected_term = isset( $_GET[ $taxonomy->query_var ] ) ? $_GET[ $taxonomy->query_var ] : false ;

					$args = array(
						'show_option_all' => 'Показать все ' . $taxonomy->label,
						'taxonomy'        => $tax_slug,
						'name'            => $taxonomy->name,
						'orderby'         => 'name',
						'hierarchical'    => $taxonomy->hierarchical,
						'show_count'      => true,
						'hide_empty'      => false,

						'walker'          => new Versusbassz_Walker_TaxonomyDropdown(),
						'value'           => 'slug'
					);

					if ( $selected_term ) {
						$args['selected'] = $selected_term;
					}

					wp_dropdown_categories( $args );
				}
			}
		}

	}



	/*
	 * https://gist.github.com/stephenh1988/2902509
	 * http://wordpress.stackexchange.com/questions/75793/how-do-i-get-the-category-slug-from-wp-dropdown-categories
	 *
	 * http://www.wpcustoms.net/snippets/admin-filter-for-custom-taxonomies/
	 * http://wordpress.stackexchange.com/questions/578/adding-a-taxonomy-filter-to-admin-list-for-a-custom-post-type#12856
	 * https://wordpress.org/plugins/custom-taxonomy-filter/
	 *
	 *
	 *
	 * A walker class to use that extends wp_dropdown_categories and allows it to use the term's slug as a value rather than ID.
	 *
	 * See http://core.trac.wordpress.org/ticket/13258
	 *
	 * Usage, as normal:
	 * wp_dropdown_categories($args);
	 *
	 * But specify the custom walker class, and (optionally) a 'id' or 'slug' for the 'value' parameter:
	 * $args=array('walker'=> new SH_Walker_TaxonomyDropdown(), 'value'=>'slug', .... );
	 * wp_dropdown_categories($args);
	 *
	 * If the 'value' parameter is not set it will use term ID for categories, and the term's slug for other taxonomies in the value attribute of the term's <option>.
	*/

	class Versusbassz_Walker_TaxonomyDropdown extends Walker_CategoryDropdown {

		function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
			$pad = str_repeat('&nbsp;', $depth * 3);

			/** This filter is documented in wp-includes/category-template.php */
			$cat_name = apply_filters('list_cats', $category->name, $category);

			if( !isset($args['value']) ){
				$args['value'] = ( $category->taxonomy != 'category' ? 'slug' : 'id' );
			}

			$value = ($args['value']=='slug' ? $category->slug : $category->term_id );

			$output .= "\t<option class=\"level-$depth\" value=\"".$value."\"";
			if ( $value === (string) $args['selected'] ){
				$output .= ' selected="selected"';
			}
			$output .= '>';
			$output .= $pad.$cat_name;
			if ( $args['show_count'] )
				$output .= '&nbsp;&nbsp;('. number_format_i18n( $category->count ) .')';

			$output .= "</option>\n";
		}

	}