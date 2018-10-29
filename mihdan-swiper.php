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
 * Version: 1.0.3
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

	$output = str_replace( 'gallery ', 'gallery mihdan-swiper-container ', $output );

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

		wp_enqueue_style( 'swiper', plugins_url( 'assets/css/swiper.min.css', __FILE__ ) );
		wp_enqueue_style( 'mihdan-swiper', plugins_url( 'assets/css/mihdan-swiper-style.css', __FILE__ ) );
		wp_enqueue_script( 'swiper', plugins_url( 'assets/js/swiper.jquery.min.js', __FILE__ ), array( 'jquery' ), null, true );

		// Аргументы для свайпера
		$args = array(
			'loop'                => true,
			'pagination'          => '.mihdan-swiper-pagination',
			'paginationClickable' => true,
			'grabCursor'          => true,
			'nextButton'          => '.mihdan-swiper-button-next',
			'prevButton'          => '.mihdan-swiper-button-prev',
			//'effect' => 'fade',
			//'mousewheelControl' => true,
			'keyboardControl'     => true,
			'hashnav'             => true,
			//'autoHeight' => true
			'setWrapperSize'      => true,
			'slideClass'          => 'gallery-item',
			'height'              => 480,
		);

		// Позволяем менять настройки свайпера другим
		$args = apply_filters( 'mihdan_swiper_args', $args );

		$js = <<<JS
			jQuery( function( $ ) {			    
			    $( '.mihdan-swiper-container' )
			    	.wrapInner( '<div class="swiper-wrapper"></div>' )
			    	.append( '<div class="mihdan-swiper-pagination"></div>' )
			    	.append( '<div class="mihdan-swiper-button-prev"></div>' )
			    	.append( '<div class="mihdan-swiper-button-next"></div>' );
			    	
			  var swiper = new Swiper ( '.swiper-container', %s );  
			} );
JS;

		// Передаём аргументы из PHP в JS
		$js = vsprintf(
			$js,
			array(
				json_encode( $args ),
			)
		);

		wp_add_inline_script( 'swiper', $js );
	}
}
add_action( 'wp_enqueue_scripts', 'mihdan_swiper_enqueue_scripts' );

// eof;
