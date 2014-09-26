<?php

 /**
  * The Pagination CLASS.
  */

  	class Pagination {

  		public static $page_url;
  		public static $get_vars;

  		public static $default_args = array(
			'padding' 		=> 5,
			'next' 			=> true,
			'next_text' 	=> '&rsaquo;',
			'prev' 			=> true,
			'prev_text' 	=> '&lsaquo;',
			'first' 		=> true,
			'first_text' 	=> '&laquo;',
			'last' 			=> true,
			'last_text' 	=> '&raquo;',
			'numbered' 		=> true

		);

		public static $args = array();

	 /**
	  * Get the pagination.
	  *
	  * @return String
	  */

		public function __construct( $custom_query = null, $config_args = array() ) {

		}

	 /**
	  * Get the pagination.
	  *
	  * @return String
	  */

		public function get_pagination( $custom_query = null, $config_args = array() ) {

			self::$get_vars = self::return_get_vars_string();

			self::$args = array_merge( self::$default_args, $config_args );

			global $paged;

			//	If Query is not an object, stop pagination
			if ( !is_object($custom_query) ) return "Query not an object";

			//	
			$max_pages = $custom_query->max_num_pages;
			$posts_per_page = $custom_query->query_vars['posts_per_page'];

			// 	Stop pagination if max pages is not greater than 1
			if ( $max_pages <= 1 ) return false;

			$found_posts = $custom_query->found_posts;

			if( isset( $_GET['page'] ) )
				$current_page = $_GET['page'];
			else
				$current_page = ( get_query_var('page') ) ? get_query_var('page') : 1;

			if( $current_page == 0 ) $current_page = 1;

			//	extract all other existing GET vars
			$current_get_vars = self::return_get_vars_string();
			
			$pagination_links = array();

			if( self::$args['first'] )
				array_push( $pagination_links, array(
					'page' => 1,
					'text' => self::$args['first_text'],
					'classes' => 'to-first arrow-button'
				));

			if( self::$args['prev'] )
				array_push( $pagination_links, array(
					'page' => ( $current_page - 1 > 0) ? $current_page - 1 : $max_pages ,
					'text' => self::$args['prev_text'],
					'classes' => 'to-prev arrow-button'
				));

			if( self::$args['numbered'] ) {

				$start_pagi_number = $current_page - self::$args['padding'] < 1 ? 1 : $current_page - self::$args['padding'] ;
				$end_pagi_number = $current_page + self::$args['padding'] > $max_pages ? $max_pages : $current_page + self::$args['padding'] ;

				for ( $i = $start_pagi_number; $i <= $end_pagi_number ; $i++) { 

					array_push( $pagination_links, array(
						'page' => $i,
						'text' => $i,
						'classes' => ( $i === $current_page ) ? 'current-item' : 'pagination-item'
					));
				}				
			}

			if( self::$args['next'] )
				array_push( $pagination_links, array(
					'page' => ( $current_page + 1 <= $max_pages) ? $current_page + 1 : 1,
					'text' => self::$args['next_text'],
					'classes' => 'to-next arrow-button'
				));

			if( self::$args['last'] )
				array_push( $pagination_links, array(
					'page' => $max_pages,
					'text' => self::$args['last_text'],
					'classes' => 'to-last arrow-button'
				));

			//print_a($pagination_links);

			$content = "<div class=\"pagination\">";

				$content .= "<ul>";

				foreach ( $pagination_links as $key => $link_config ) {

					$content .= self::get_button( $link_config );

				}

				$content .= "</ul>";

			$content .= "</div>";

			$max_pages = $custom_query->max_num_pages;
			$posts_per_page = $custom_query->query_vars['posts_per_page'];
			$current_page = get_query_var('page');

			// 	Stop pagination if max pages is not greater than 1
			if ( $max_pages <= 1 ) return false;

			return $content;
		}


	 /**
	  * create()
	  * 
	  * @return Object
	  */

		public static function create( $custom_query = null, $config_args = array() ) {

			$pagination = new self( $custom_query, $config_args );

			return $pagination;

		}



	 /**
	  * get_button()
	  *
	  * @return String
	  */

		public static function get_button( $config = array() ) {

			if( $config['page'] == null )
				return false;

			$page_url = self::get_page_url();

			$get_var_args = array();

			/**
			 * TODO:	get_query_var('page') doesnt work on Categories or the front page
			 *			Falls back to using $_GET variables on these pages
			 *			Need to find out why it doesnt work...
			 */
			if( is_front_page() || is_category() ) {

				$get_var_args['remove'] = array( 'page' );
				$get_var_args['add'] = array( 'page' => $config['page'] );
				$get_vars = self::return_get_vars_string( $get_var_args );

			} else {

				$get_vars = self::return_get_vars_string( $get_var_args );
				$get_vars = "{$config['page']}/{$get_vars}";

			}

			$content = "<li class=\"{$config['classes']}\">";

				$content .=  "<a href=\"{$page_url}{$get_vars}\">{$config['text']}</a>";

			$content .=  "</li>";

			return $content;

		}



	 /**
	  * get_page_url()
	  * 
	  * Test what page we are on and return the url.
	  *
	  * @return String
	  */

		public static function get_page_url() {

			//	Check for front page or search page
			if( is_front_page() || is_search() ):

				return home_url();

			//	Check for blog page
			elseif ( is_home() ):

				return get_permalink( get_option('page_for_posts' ) );

			//	Category Page
			elseif(is_category()):

				$category = get_query_var('cat');
				$category = get_category($category);
				return get_category_link( $category->term_id );

			//	We are on a normal page
			else:

				return get_permalink();

			endif;

		}

	 /**
	  * return_get_vars_string()
	  * 
	  * Turns the current GET vars into a string. Handels prepending the correct concatenation symbols
	  * 
	  * @param  Array  $config 	Configurable args for the method
	  *                         - `remove` (array): An array of GET vars to exclude from the returned string
	  *                         - `add` (array): An assiative array of GET vars to add to the string. eg `array('foo'=>'bar')` will add `?foo=bar` OR `&foo=bar` to the string depending on if it is the first variable printed
	  * 
	  * 
	  * @return String
	  * Returns a string of GET vars
	  */
		public function return_get_vars_string( $config = array() ) {

			$get_vars = null;
			$divider = '?';

			if (!empty($_GET)) {

				foreach ( $_GET as $key => $value) {

					if( !isset($config['remove']) || !in_array( $key, $config['remove'] ) ) {

						$get_vars .= $divider.$key.'='.$value;
						if( $divider == '?' ) $divider = '&';
						
					}
				}
			}

			if( isset($config['add']) ) {

				foreach ( $config['add'] as $key => $value) {

					$get_vars .= $divider.$key.'='.$value;
					if( $divider == '?' )$divider = '&';
				}
			}

			return $get_vars;
		}
  	}


