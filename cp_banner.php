<?php
/*
Plugin Name: Casepress Banner
Description: Banner AJAX rotator
Author: ConstPalkin
Version: 0.0.3
Author URI: http://casepress.org/
*/


function cp_banner($atts, $content=null) {
	global $post;
	$VERSION = '0.1.0';

	// вытаскиваем атрибуты шорткода
	extract(shortcode_atts(array(
		"banner_jquery" => false,
		"banner_name" => 'owl_carousel',
		"banner_timeout" => '3000',
		"banner_items" => '1',
		"banner_tag" => '',
	), $atts));

	// если нужна отдельно-загружаемая jQuery (вдруг она по каким-то причинам не загружена на страницу)
	if ($banner_jquery) {
		wp_register_script( 'owl_carousel_jquery', plugins_url( '/js/jquery.js', __FILE__ ), array(), $VERSION, true );
		wp_enqueue_script( 'owl_carousel_jquery');
	}

	// берем посты с типом поста "cp_banner" и с определенным тегом (если указан)
	$myposts = $banner_tag ? get_posts(array('post_type'=>'cp_banner','nopaging'=>true,'tag'=>$banner_tag)) : get_posts(array('post_type'=>'cp_banner','nopaging'=>true)) ;

	$retour = '<div class="'.$banner_name.'">';

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
					//$image_attributes = wp_get_attachment_image_src( $attachment->ID, 'thumbnail' )  ? wp_get_attachment_image_src( $attachment->ID, 'thumbnail' ) : wp_get_attachment_image_src( $attachment->ID, 'full' );
					$image_attributes = wp_get_attachment_image_src( $attachment->ID, 'full' );
					$retour .= '<div>';
					if ($bexcerpt) $retour .= '<a href="'.get_the_excerpt().'">';
					$retour .= '<img src="'.$image_attributes[0].'">';
					if ($bexcerpt) $retour .= '</a>';
					$retour .= '</div>';
				} // foreach attach
			} elseif ($bcontent) { // если не "картиночный", а "хтмл"-ный, то берем контент
				$retour .= '<div>';
				$retour .= $bcontent;
				$retour .= '</div>';
			} // if attach
		} // if category
	} //foreach posts
	$retour .= '</div> ';
	
	// JS-запуск каждой карусельки
	$retour .= '
		<script>
		jQuery(document).ready(function(){
			  jQuery(".'.$banner_name.'").owlCarousel({
					items:'.$banner_items.',
			    loop:true,
		  	  margin:10,
		    	autoplay:true,
			    autoplayTimeout:'.$banner_timeout.',
		  	  autoplayHoverPause:true
				});
		});
		</script>
	';

	wp_reset_postdata();

	return $retour;
}; //function cp_banner()

//adding js and css
wp_register_style( 'owl_carousel_core', plugins_url( '/owlcarousel/assets/owl.carousel.min.css', __FILE__ ), array(), $VERSION, 'all' );
wp_enqueue_style( 'owl_carousel_core' );
wp_register_style( 'owl_carousel_theme', plugins_url( '/owlcarousel/assets/owl.theme.default.min.css', __FILE__ ), array(), $VERSION, 'all' );
wp_enqueue_style( 'owl_carousel_theme' );
wp_register_script( 'owl_carousel_core', plugins_url( '/owlcarousel/owl.carousel.min.js', __FILE__ ), array(), $VERSION, true );
wp_enqueue_script( 'owl_carousel_core' );

//creating shortcode
add_shortcode("cp_banner", "cp_banner");
?>
