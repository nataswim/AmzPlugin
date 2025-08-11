<?php

namespace Amazon\Affiliate\Fiu;

if( ! class_exists( 'FiuImageUrl' ) ):
	class FiuImageUrl {

		public $image_meta_url = '_amswoofiu_url';
		public $image_meta_alt = '_amswoofiu_alt';

		public function __construct() {
			add_filter( 'post_thumbnail_html', array( $this, 'amswoofiu_overwrite_thumbnail_with_url' ), 999, 5 );
			add_filter( 'woocommerce_structured_data_product', array( $this, 'amswoofiu_woo_structured_data_product_support' ), 99, 2 );
			
			add_filter( 'shopzio_product_image_from_id', array( $this, 'amswoofiu_shopzio_product_image_url' ), 10, 2 );

			if( !is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ){
				add_action( 'init', array( $this, 'amswoofiu_set_thumbnail_id_true' ) );
				add_filter( 'wp_get_attachment_image_src', array( $this, 'amswoofiu_replace_attachment_image_src' ), 10, 4 );
				add_filter( 'woocommerce_product_get_gallery_image_ids', array( $this, 'amswoofiu_set_customized_gallary_ids' ), 99, 2 );
				// Product Variation image Support
				add_filter( 'woocommerce_available_variation', array( $this, 'amswoofiu_woocommerce_available_variation' ), 99, 3 );
			}
			// Add WooCommerce Product listable Thumbnail Support for Woo 3.5 or greater
			add_action( 'admin_init', array( $this, 'amswoofiu_woo_thumb_support' ) );

			$options = get_option( AMSWOOFIU_OPTIONS );
			$resize_images = isset( $options['resize_images'] ) ? $options['resize_images']  : false;
			if( !$resize_images ){
				add_filter( 'amswoofiu_user_resized_images', '__return_false' );
			}

			add_filter('woocommerce_product_get_image_id', array( $this, 'amswoofiu_woocommerce_product_get_image_id_support'), 99, 2);
		}

		function amswoofiu_woocommerce_product_get_image_id_support( $value, $product){
			global $amswoofiu;
			$product_id = $product->get_id();
			if(!empty($product_id) && !empty($amswoofiu)){
				$post_type = get_post_type( $product_id );
				$image_data = $amswoofiu->woo_hook->amswoofiu_get_image_meta( $product_id );
				if ( isset( $image_data['img_url'] ) && $image_data['img_url'] != '' ){
					return $product_id;
				}
			}
			return $value;
		}

		function amswoofiu_set_thumbnail_id_true(){
			global $amswoofiu;
			foreach ( $amswoofiu->woo_hook->amswoofiu_get_posttypes() as $post_type ) {
				add_filter( "get_{$post_type}_metadata", array( $this, 'amswoofiu_set_thumbnail_true' ), 10, 4 );
			}
		}

		function amswoofiu_get_posttypes( $raw = false ) {

			$post_types = array_diff( get_post_types( array( 'public' => true ), 'names' ), array( 'nav_menu_item', 'attachment', 'revision' ) );
			if( !empty( $post_types ) ){
				foreach ( $post_types as $key => $post_type ) {
					if( !post_type_supports( $post_type, 'thumbnail' ) ){
						unset( $post_types[$key] );
					}
				}
			}
			if( $raw ){
				return $post_types;	
			}else{
				$options = get_option( AMSWOOFIU_OPTIONS );
				$disabled_posttypes = isset( $options['disabled_posttypes'] ) ? $options['disabled_posttypes']  : array();
				$post_types = array_diff( $post_types, $disabled_posttypes );
			}

			return $post_types;
		}

		function amswoofiu_set_thumbnail_true( $value, $object_id, $meta_key, $single ){

			global $amswoofiu;
			$post_type = get_post_type( $object_id );
			if( $this->amswoofiu_is_disallow_posttype( $post_type ) ){
				return $value;
			}

			if ( $meta_key == '_thumbnail_id' ){
				$image_data = $amswoofiu->woo_hook->amswoofiu_get_image_meta( $object_id );
				if ( isset( $image_data['img_url'] ) && $image_data['img_url'] != '' ){
					if( $post_type == 'product_variation' ){
						if( !is_admin() ){
							return $object_id;
						}else{
							return $value;
						}
					}
					return $object_id;
				}
			}
			return $value;
		}

		//function amswoofiu_overwrite_thumbnail_with_url( $html, $post_id, $post_image_id, $size, $attr ){
		function amswoofiu_overwrite_thumbnail_with_url( $html, $post_id, $post_image_id, $size, $attr = array() ){

			global $amswoofiu;
			if( $this->amswoofiu_is_disallow_posttype( get_post_type( $post_id ) ) ){
				return $html;
			}

			if( is_singular( 'product' ) && ( 'product' == get_post_type( $post_id ) || 'product_variation' == get_post_type( $post_id ) ) ){
				return $html;
			}
			
			$image_data = $amswoofiu->woo_hook->amswoofiu_get_image_meta( $post_id );
			
			if( !empty( $image_data['img_url'] ) ){
				$image_url 		= $image_data['img_url'];

				if( apply_filters( 'amswoofiu_user_resized_images', true ) ){
					$image_url = $this->amswoofiu_resize_image_on_the_fly( $image_url, $size );	
				}

				$image_alt	= ( $image_data['img_alt'] ) ? 'alt="'.$image_data['img_alt'].'"' : '';
				$classes 	= 'external-img wp-post-image ';
				$classes   .= ( isset($attr['class']) ) ? $attr['class'] : '';
				$style 		= ( isset($attr['style']) ) ? 'style="'.$attr['style'].'"' : '';

				$html = sprintf(
					'<img src="%s" %s class="%s" %s />', 
					$image_url, $image_alt, $classes, $style
				);
			}

			return $html;
		}

		public function amswoofiu_resize_image_on_the_fly( $image_url, $size = 'full' ){
			if( $size == 'full' || empty( $image_url )){
				return $image_url;
			}

			if( !class_exists( 'Jetpack_PostImages' ) || !defined( 'JETPACK__VERSION' ) ){
				return $image_url;
			}

			$parsed = parse_url( $image_url );
			if( isset( $parsed['query'] ) && $parsed['query'] != '' ){
				return $image_url;
			}

			$image_size = $this->amswoofiu_get_image_size( $size );
			
			if( !empty( $image_size ) && !empty( $image_size['width'] ) ){
				$width = (int) $image_size['width'];
				$height = (int) $image_size['height'];

				if ( $width < 1 || $height < 1 ) {
					return $image_url;
				}

				$img_host = parse_url( $image_url, PHP_URL_HOST );
				if ( '.files.wordpress.com' == substr( $img_host, -20 ) ) {
					return add_query_arg( array( 'w' => $width, 'h' => $height, 'crop' => 1 ), set_url_scheme( $image_url ) );
				}

				if( function_exists( 'jetpack_photon_url' ) ) {
					if( isset( $image_size['crop'] ) && $image_size['crop'] == 1 ){
						return jetpack_photon_url( $image_url, array( 'resize' => "$width,$height" ) );
					}else{
						return jetpack_photon_url( $image_url, array( 'fit' => "$width,$height" ) );
					}
				}
			}
			
			return $image_url;
		}

		function amswoofiu_get_image_sizes() {
			global $_wp_additional_image_sizes;
			$sizes = array();
			foreach ( get_intermediate_image_sizes() as $_size ) {
				if ( in_array( $_size, array('thumbnail', 'medium', 'medium_large', 'large') ) ) {
					$sizes[ $_size ]['width']  = get_option( "{$_size}_size_w" );
					$sizes[ $_size ]['height'] = get_option( "{$_size}_size_h" );
					$sizes[ $_size ]['crop']   = (bool) get_option( "{$_size}_crop" );
				} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
					$sizes[ $_size ] = array(
						'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
						'height' => $_wp_additional_image_sizes[ $_size ]['height'],
						'crop'   => $_wp_additional_image_sizes[ $_size ]['crop'],
					);
				}
			}
			return $sizes;
		}

		function amswoofiu_get_wcgallary_meta( $post_id ){
			
			$image_meta  = array();

			$gallary_images = get_post_meta( $post_id, AMSWOOFIU_WCGALLARY, true );
			
			if( !is_array( $gallary_images ) && $gallary_images != '' ){
				$gallary_images = explode( ',', $gallary_images );
				if( !empty( $gallary_images ) ){
					$gallarys = array();
					foreach ($gallary_images as $gallary_image ) {
						$gallary = array();
						$gallary['url'] = $gallary_image;
						$imagesizes = @getimagesize( $gallary_image );
						$gallary['width'] = isset( $imagesizes[0] ) ? $imagesizes[0] : '';
						$gallary['height'] = isset( $imagesizes[1] ) ? $imagesizes[1] : '';
						$gallarys[] = $gallary;
					}
					$gallary_images = $gallarys;
					update_post_meta( $post_id, AMSWOOFIU_WCGALLARY, $gallary_images );
					return $gallary_images;
				}
			}else{
				if( !empty( $gallary_images ) ){
					$need_update = false;
					foreach ($gallary_images as $key => $gallary_image ) {
						if( !isset( $gallary_image['width'] ) && isset( $gallary_image['url'] ) ){
							$imagesizes1 = @getimagesize( $gallary_image['url'] );
							$gallary_images[$key]['width'] = isset( $imagesizes1[0] ) ? $imagesizes1[0] : '';
							$gallary_images[$key]['height'] = isset( $imagesizes1[1] ) ? $imagesizes1[1] : '';
							$need_update = true;
						}
					}
					if( $need_update ){
						update_post_meta( $post_id, AMSWOOFIU_WCGALLARY, $gallary_images );
					}
					return $gallary_images;
				}	
			}
			return $gallary_images;
		}

		function amswoofiu_set_customized_gallary_ids( $value, $product ){

			if( $this->amswoofiu_is_disallow_posttype( 'product') ){
				return $value;
			}

			$product_id = $product->get_id();
			if( empty( $product_id ) ){
				return $value;
			}
			$gallery_images = $this->amswoofiu_get_wcgallary_meta( $product_id );
			if( !empty( $gallery_images ) ){
				$i = 0;
				foreach ( $gallery_images as $gallery_image ) {
					$gallery_ids[] = AMSWOOFIU_WCGALLARY . '__'.$i.'__'.$product_id;
					$i++;
				}
				return $gallery_ids;
			}
			return $value;
		}

		function amswoofiu_replace_attachment_image_src( $image, $attachment_id, $size, $icon ) {
			global $amswoofiu;
			if( false !== strpos( $attachment_id, AMSWOOFIU_WCGALLARY ) ){
				$attachment = explode( '__', $attachment_id );
				$image_num  = $attachment[1];
				$product_id = $attachment[2];
				if( $product_id > 0 ){
					
					$gallery_images = $amswoofiu->woo_hook->amswoofiu_get_wcgallary_meta( $product_id );
					if( !empty( $gallery_images ) ){
						if( !isset( $gallery_images[$image_num]['url'] ) ){
							return false;
						}
						$url = $gallery_images[$image_num]['url'];
						
						if( apply_filters( 'amswoofiu_user_resized_images', true ) ){
							$url = $amswoofiu->woo_hook->amswoofiu_resize_image_on_the_fly( $url, $size );	
						}
						$image_size = $amswoofiu->woo_hook->amswoofiu_get_image_size( $size );
						if ($url) {
							if( $image_size ){
								if( !isset( $image_size['crop'] ) ){
									$image_size['crop'] = '';
								}
								return array(
											$url,
											$image_size['width'],
											$image_size['height'],
											$image_size['crop'],
									);
							}else{
								if( $gallery_images[$image_num]['width'] != '' && $gallery_images[$image_num]['width'] > 0 ){
									return array( $url, $gallery_images[$image_num]['width'], $gallery_images[$image_num]['height'], false );
								}else{
									return array( $url, 800, 600, false );
								}
							}
						}
					}
				}
			}

			if( is_numeric($attachment_id ) && $attachment_id > 0 ){
				$image_data = $amswoofiu->woo_hook->amswoofiu_get_image_meta( $attachment_id, true );

				if ( isset( $image_data['img_url'] ) && $image_data['img_url'] != '' ){

					$image_url = $image_data['img_url'];
					$width = isset( $image_data['width'] ) ? $image_data['width'] : '';
					$height = isset( $image_data['height'] ) ? $image_data['height'] : '';

					if( apply_filters( 'amswoofiu_user_resized_images', true ) ){
						$image_url = $amswoofiu->woo_hook->amswoofiu_resize_image_on_the_fly( $image_url, $size );
					}

					$image_size = $amswoofiu->woo_hook->amswoofiu_get_image_size( $size );
					if ($image_url) {
						if( $image_size ){
							if( !isset( $image_size['crop'] ) ){
								$image_size['crop'] = '';
							}
							return array(
										$image_url,
										$image_size['width'],
										$image_size['height'],
										$image_size['crop'],
								);
						}else{
							if( $width != '' && $height != '' ){
								return array( $image_url, $width, $height, false );
							}
							return array( $image_url, 800, 600, false );
						}
					}
				}
			}

			return $image;
		}

		function amswoofiu_get_image_size( $size ) {
			$sizes = $this->amswoofiu_get_image_sizes();

			if( is_array( $size ) ){
				$woo_size = array();
				$woo_size['width'] = $size[0];
				$woo_size['height'] = $size[1];
				return $woo_size;
			}
			if ( isset( $sizes[ $size ] ) ) {
				return $sizes[ $size ];
			}

			return false;
		}

		function amswoofiu_is_disallow_posttype( $posttype ) {

			$options = get_option( AMSWOOFIU_OPTIONS );
			$disabled_posttypes = isset( $options['disabled_posttypes'] ) ? $options['disabled_posttypes']  : array();

			return in_array( $posttype, $disabled_posttypes );
		}

		public function amswoofiu_woo_thumb_support() {
			global $pagenow;
			if( 'edit.php' === $pagenow ){
				global $typenow;
				if( 'product' === $typenow && isset( $_GET['post_type'] ) && 'product' === sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) ) {
					add_filter( 'wp_get_attachment_image_src', array( $this, 'amswoofiu_replace_attachment_image_src' ), 10, 4 );
				}
			}
		}

		function amswoofiu_woo_structured_data_product_support( $markup, $product ) {
			if ( isset($markup['image']) && empty($markup['image']) ) {
				global $amswoofiu;
				$product_id = $product->get_id();
				if( !$this->amswoofiu_is_disallow_posttype( 'product' ) && $product_id > 0 ){
					$image_data = $amswoofiu->woo_hook->amswoofiu_get_image_meta( $product_id );
					if( !empty($image_data) && isset($image_data['img_url']) && !empty($image_data['img_url']) ) {
						$markup['image'] = $image_data['img_url'];
					}
				}
			}
			return $markup;
		}
		
		function amswoofiu_shopzio_product_image_url( $image, $attachment_id ) {
			if( empty( $attachment_id ) || !empty($image)){
				return $image;
			}

			$image_data = $this->amswoofiu_replace_attachment_image_src( $image, $attachment_id, 'full', false);
			if (!empty($image_data) && isset($image_data[0]) && !empty($image_data[0])) {
				$image = $image_data[0];
			}

			return $image;
		}

		function amswoofiu_get_image_meta( $post_id, $is_single_page = false ){
			
			$image_meta  = array();

			$img_url = get_post_meta( $post_id, $this->image_meta_url, true );
			$img_alt = get_post_meta( $post_id, $this->image_meta_alt, true );
			
			if( is_array( $img_url ) && isset( $img_url['img_url'] ) ){
				$image_meta['img_url'] 	 = $img_url['img_url'];	
			}else{
				$image_meta['img_url'] 	 = $img_url;
			}
			$image_meta['img_alt'] 	 = $img_alt;
			if( ( 'product_variation' == get_post_type( $post_id ) || 'product' == get_post_type( $post_id ) ) && $is_single_page ){
				if( isset( $img_url['width'] ) ){
					$image_meta['width'] 	 = $img_url['width'];
					$image_meta['height'] 	 = $img_url['height'];
				}else{

					if( isset( $image_meta['img_url'] ) && $image_meta['img_url'] != '' ){
						$imagesize = @getimagesize( $image_meta['img_url'] );
						$image_url = array(
							'img_url' => $image_meta['img_url'],
							'width'	  => isset( $imagesize[0] ) ? $imagesize[0] : '',
							'height'  => isset( $imagesize[1] ) ? $imagesize[1] : ''
						);
						update_post_meta( $post_id, $this->image_meta_url, $image_url );
						$image_meta = $image_url;	
					}				
				}
			}
			return $image_meta;
		}

		function amswoofiu_woocommerce_available_variation( $value, $variable_product, $variation ){
			$variation_id =  $variation->get_id();
			if( empty( $variation_id ) ){
				return $value;
			}

			global $amswoofiu;
			// Product Variation Image
			$variation_image = $amswoofiu->woo_hook->amswoofiu_get_image_meta( $variation_id, true );
			if( isset( $variation_image['img_url'] ) && !empty( $variation_image['img_url'] ) && isset($value['image'])){
				$image_url = $variation_image['img_url'];
				$width = (isset( $variation_image['width'] ) && !empty($variation_image['width'])) ? $variation_image['width'] : '';
				$height = (isset( $variation_image['height'] ) && !empty($variation_image['height'])) ? $variation_image['height'] : '';

				$value['image']['url'] = $image_url;
				// Large version.
				$value['image']['full_src'] = $image_url;
				$value['image']['full_src_w'] = $width;
				$value['image']['full_src_h'] = $height;

				// Gallery thumbnail.
				$value['image']['gallery_thumbnail_src'] = $image_url;
				$value['image']['gallery_thumbnail_src_w'] = $width;
				$value['image']['gallery_thumbnail_src_h'] = $height;

				// Thumbnail version.
				$value['image']['thumb_src'] = $image_url;
				$value['image']['thumb_src_w'] = $width;
				$value['image']['thumb_src_h'] = $height;

				// Image version.
				$value['image']['src'] = $image_url;
				$value['image']['src_w'] = $width;
				$value['image']['src_h'] = $height;
			}
			return $value;
		}
	}
endif;