<?php
/**
 * Mihdan: Swiper
 *
 * @package     mihdan-swiper
 * @author      Mikhail Kobzarev
 * @link http://wordpress.stackexchange.com/questions/165754/enqueue-scripts-styles-when-shortcode-is-present
 * @link hhttps://www.kobzarev.com/projects/mihdan-swiper/
 * @copyright   2016 mihdan
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: Mihdan: Swiper
 * Plugin URI: https://github.com/mihdan/mihdan-swiper
 * Description: Расширяет дефолтную галерею WordPress при помощи Swiper.JS
 * Version: 1.1
 * Author:      Mikhail Kobzarev
 * Author URI:  https://www.kobzarev.com/
 * Text Domain: mihdan-swiper
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * GitHub Plugin URI: https://github.com/mihdan/mihdan-swiper
 */

/**
 * Поправим вывод тегов
 *
 * @param $out
 * @param $pairs
 * @param $atts
 * @param $shortcode
 *
 * @return mixed
 */
function mihdan_swiper_shortcode_atts_gallery( $out ) {

	$out['icontag'] = 'div';
	$out['itemtag'] = 'div';

	return $out;
}
add_filter( 'shortcode_atts_gallery', 'mihdan_swiper_shortcode_atts_gallery' );

/**
 * Добавить класс для контейнера галереи
 *
 * @param $output
 *
 * @return mixed
 */
function mihdan_swiper_gallery_style( $output ) {

	$output = str_replace( 'gallery ', 'swiper-container mihdan-swiper-container ', $output );

	return $output;
}
add_filter( 'gallery_style', 'mihdan_swiper_gallery_style' );

/**
 * Отключить дефолтные стили для галереи
 */
add_filter( 'use_default_gallery_style', '__return_false' );

/**
 * Включить поддержку галерей
 */
function mihdan_swiper_setup_theme() {
	add_theme_support( 'html5', array( 'gallery' ) );
}
add_action( 'after_setup_theme', 'mihdan_swiper_setup_theme' );

/**
 * Добавить стили и скрипты от Swiper
 */
function mihdan_swiper_enqueue_scripts() {
	global $post;

	if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'gallery' ) ) {

		wp_enqueue_style( 'mihdan-swiper', plugins_url( 'assets/css/swiper.min.css', __FILE__ ) );
		wp_enqueue_style( 'mihdan-swiper-app', plugins_url( 'assets/css/mihdan-swiper-style.css', __FILE__ ) );
		wp_enqueue_script( 'mihdan-swiper', plugins_url( 'assets/js/swiper.min.js', __FILE__ ), array( 'jquery' ), null, true );

		// Аргументы для свайпера
		$args = array(
			'loop'           => true,
			'pagination'     => array(
				'el'        => '.mihdan-swiper-pagination',
				'clickable' => true,
			),
			'grabCursor'     => true,
			'navigation'     => array(
				'nextEl' => '.mihdan-swiper-button-next',
				'prevEl' => '.mihdan-swiper-button-prev',
			),
			//'effect' => 'fade',
			//'mousewheelControl' => true,
			'keyboard'       => array(
				'enabled' => true,
			),
			'hashNavigation' => array(
				'replaceState' => true,
			),
			//'autoHeight' => true
			'setWrapperSize' => true,
			'slideClass'     => 'mihdan-gallery-item',
			'height'         => 480,
			'slidesPerView'  => 1,
		);

		// Позволяем менять настройки свайпера другим
		$args = apply_filters( 'mihdan_swiper_args', $args );

		$js = <<<JS
			jQuery( function( $ ) {			    
			    $( '.mihdan-swiper-container' )
			    	.wrapInner( '<div class="swiper-wrapper"></div>' )
			    	.append( '<div class="swiper-pagination mihdan-swiper-pagination"></div>' )
			    	.append( '<div class="swiper-button-prev mihdan-swiper-button-prev"></div>' )
			    	.append( '<div class="swiper-button-next mihdan-swiper-button-next"></div>' )
			        .find( '.gallery-item' )
			        .removeClass( 'gallery-item' )
			        .addClass( 'mihdan-gallery-item' );
			    
			  var swiper = new Swiper ( '.mihdan-swiper-container', %s );  
			  console.log( swiper );
			} );
JS;

		// Передаём аргументы из PHP в JS
		$js = vsprintf(
			$js,
			array(
				json_encode( $args ),
			)
		);

		wp_add_inline_script( 'mihdan-swiper', $js );
	}
}
add_action( 'wp_enqueue_scripts', 'mihdan_swiper_enqueue_scripts' );

// eof;
