<?php
/*
Plugin Name: Casepress Banner
Description: Banner AJAX rotator
Author: ConstPalkin
Version: 1.0.1
Author URI: http://casepress.org/
*/


function f_cp_banner($atts, $content=null) {
	global $post;
	$VERSION = '1.0.1';

	// вытаскиваем атрибуты шорткода
	extract(shortcode_atts(array(
		"banner_jquery" => false,		// загружать ли jQuery из плагина
		"banner_height" => '',			// принудительная высота контейнера баннера
		"banner_width" => '',			// принудительная ширина контейнера баннера
		"banner_name" => 'owl_carousel',	// уникальное имя баннера на странице (для нескольких баннеров на одной странице)
		"banner_timeout" => '10000',		// таймаут смены баннера (в мс)
		"banner_tag" => '',			// тег постов, которые надо собирать в цикл
		"banner_transition" => 'fadeUp',	// тип смены слайдера ('fade','backSlide','goDown','fadeUp')
		"banner_pagination" => 'false'		// нужны ли точки внизу
	), $atts));

	// если нужна отдельно-загружаемая jQuery (вдруг она по каким-то причинам не загружена на страницу)
	if ($banner_jquery) {
		wp_register_script( 'owl_carousel_jquery', plugins_url( '/js/jquery.js', __FILE__ ), array(), $VERSION, true );
		wp_enqueue_script( 'owl_carousel_jquery');
	}

	// берем посты с типом поста "cp_banner" и с определенным тегом (если указан)
	$myposts = $banner_tag ? get_posts(array('post_type'=>'cp_banner','nopaging'=>true,'tag'=>$banner_tag)) : get_posts(array('post_type'=>'cp_banner','nopaging'=>true)) ;

	$retour = '';
	$banner_wrapper_style = ' style="';
	$banner_wrapper_style .= ($banner_height) ? 'height:'.$banner_height.'px;' : '';
	$banner_wrapper_style .= ($banner_width)  ? 'width:' .$banner_width .'px;' : '';
	$banner_wrapper_style .= '"';

	$retour .= '<div class="banner_wrapper"'.$banner_wrapper_style.'>';
	$retour .= '<div id="'.$banner_name.'" class="owl-carousel">';

	// перебираем посты
	foreach($myposts as $post) {

		setup_postdata($post);

		//берем картинки из поста - это будет графический баннер
		$attachments =& get_children( 'post_parent='.$post->ID.'&post_mime_type=image' );
		//берем цитату - здесь будет ссылка, относящаяся к этому баннеру
		$bexcerpt = get_the_excerpt();

		//берем контент - это баннер, составленный из ХТМЛ-куска
		$bcontent = get_the_content();


		$btags = array();
		$gtags = get_the_tags($post->ID);

		if ($gtags) {
			foreach ($gtags AS $tag) { 
				$btags[] = $tag->name;
			} //foreach tags
		} //if tags

		if ( $banner_tag == '' || ($banner_tag != '' && in_array($banner_tag,$btags))) { //отбор по тегу
			if ($attachments) { // если это "картиночный" баннер, то формируем его из картинки и цитаты
				foreach($attachments as $attachment) {
					$image_attributes = wp_get_attachment_image_src( $attachment->ID, 'full' );
					$retour .= '<div class="item">';
					if ($bexcerpt) $retour .= '<a href="'.get_the_excerpt().'">';
					$retour .= '<img src="'.$image_attributes[0].'">';
					if ($bexcerpt) $retour .= '</a>';
					$retour .= '</div>';
				} // foreach attach
			} elseif ($bcontent) { // если не "картиночный", а "хтмл"-ный, то берем контент
				$retour .= '<div class="item">';
				$retour .= $bcontent;
				$retour .= '</div>';
			} // if attach
		} // if category
	} //foreach posts
	$retour .= '</div> ';
	$retour .= '</div> ';
	
	// JS-запуск каждой карусельки
	$retour .= '
		<script>
			jQuery(document).ready(function(){
				jQuery("#'.$banner_name.'").owlCarousel({
					items:1,
					singleItem:true,
					autoPlay:true,
					rewindSpeed:'.$banner_timeout.',
					stopOnHover:true,
					pagination :'.$banner_pagination.',
					transitionStyle :"'.$banner_transition.'"
				});
			});
		</script>
	';


	wp_reset_postdata();

	return $retour;

}; //function cp_banner()

wp_register_style( 'owl_carousel_core', plugins_url( '/owlcarousel/owl.carousel.css', __FILE__ ), array(), $VERSION, 'all' );
wp_enqueue_style( 'owl_carousel_core' );
wp_register_style( 'owl_carousel_theme', plugins_url( '/owlcarousel/owl.theme.css', __FILE__ ), array(), $VERSION, 'all' );
wp_enqueue_style( 'owl_carousel_theme' );
wp_register_style( 'owl_carousel_trans', plugins_url( '/owlcarousel/owl.transitions.css', __FILE__ ), array(), $VERSION, 'all' );
wp_enqueue_style( 'owl_carousel_trans' );
wp_register_script( 'owl_carousel_core', plugins_url( '/owlcarousel/owl.carousel.min.js', __FILE__ ), array(), $VERSION, true );
wp_enqueue_script( 'owl_carousel_core' );



//creating shortcode
add_shortcode("cp_banner", "f_cp_banner");
?>
