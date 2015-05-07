<?php

function ubermenu_searchbar( $placeholder = "Search..." ){
	?>
	<!-- UberMenu Search Bar -->
	<div class="ubermenu-search">
		<form role="search" method="get" class="ubermenu-searchform" action="<?php echo home_url( '/' ); ?>">
			<input type="text" placeholder="<?php echo $placeholder; ?>" value="" name="s" class="ubermenu-search-input" />
			<input type="submit" class="ubermenu-search-submit" value="&#xf002;" />
		</form>
	</div>
	<!-- end .ubermenu-search -->
	<?php
}

function ubermenu_searchbar_shortcode( $atts , $content ){

	extract( shortcode_atts( array(
		'placeholder' => 'Search...'
	), $atts ) );

	ob_start();
	ubermenu_searchbar( $placeholder );
	$s = ob_get_clean();

	return $s;
}
add_shortcode( 'ubermenu-search' , 'ubermenu_searchbar_shortcode' );
