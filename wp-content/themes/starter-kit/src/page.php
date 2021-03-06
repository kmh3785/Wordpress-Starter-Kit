<?php get_header();  ?>
<?php
$feat_image = wp_get_attachment_url( get_post_thumbnail_id($post->ID) );
// $pageIntro_mb->the_meta();
$parent = $page->page_parent;
$currentID = $post->ID;
$parentID = wp_get_post_parent_id( $post_ID );
$queryParent = query_posts(array('post_type' => 'page', 'post_parent' => 0));
$queryCurrent = query_posts(array('post_type' => 'page', 'p' => $currentID ));
?>
	<?php include(TEMPLATEPATH . '/component-subNav.php'); ?>
	
	<?php if ( $pageIntro_mb->get_the_value('hideHero') === 'no'  )  { ?>
		<?php include(TEMPLATEPATH . '/component-pageHero.php'); ?>
	<?php } ?>

	<?php if ( $pageIntro_mb->get_the_value('hideHero') === 'yes'  )  { ?>
		<Script>$('body').addClass('noHero');</script>
	<?php } ?>
	

	<div class="main">
		
	<!-- Start repeating text areas -->	
		<?php
			if( isset( $repeating_textareas ) ){
			$repeating_textareas->the_meta();
			while( $repeating_textareas->have_fields( 'repeating_textareas' ) ){ ?>

			<section class="repeatingTextArea <?php echo $repeating_textareas->get_the_value('columnSelect') ?>">
				<div class="contentWrap">
					<div class="content">
			
					<!-- If one column layout -->
						<?php if ( $repeating_textareas->get_the_value('columnSelect') === '1col'  )  { ?>
							<div class="oneCol">
								<?php if ( $repeating_textareas->get_the_value('titleAlignment') === 'center'  )  { ?>
									<header class="titleArea centered">
										<h3 class="sectionTitle"> <?php echo $repeating_textareas->get_the_value( 'sectionTitle' ); ?></h3>
									</header>
								<?php } ?>
								<p><?php echo $repeating_textareas->get_the_value( '1col_textarea' ); ?></p>
							</div>
						<?php } ?>

					<!-- If two column layout --> 
						<?php if ( $repeating_textareas->get_the_value('columnSelect') === '2col'  )   { ?>
							
							<!-- Title area -->
								<?php if ( $repeating_textareas->get_the_value('titleAlignment') === 'center'  )  { ?>
									<header class="titleArea centered">
										<h3 class="sectionTitle"> <?php echo $repeating_textareas->get_the_value( 'sectionTitle' ); ?></h3>
									</header>
								<?php } ?>	

							<div class="twoCol left">
								<?php if ( $repeating_textareas->get_the_value('titleAlignment') === 'left'  )  { ?>
									<header class="titleArea left">
										<h3 class="sectionTitle"> <?php echo $repeating_textareas->get_the_value( 'sectionTitle' ); ?></h3>
									</header>
								<?php } ?>
								<p><?php echo $repeating_textareas->get_the_value( '2col_left_textarea' ); ?></p>
							</div>
							<div class="twoCol right">
								<?php if ( $repeating_textareas->get_the_value('titleAlignment') === 'right'  )  { ?>
									<header class="titleArea right">
										<h3 class="sectionTitle"> <?php echo $repeating_textareas->get_the_value( 'sectionTitle' ); ?></h3>
									</header>
								<?php } ?>
								<p><?php echo $repeating_textareas->get_the_value( '2col_right_textarea' ); ?></p>
							</div>
						<?php } ?>
					
					<!-- If three column layout --> 
						<?php if ( $repeating_textareas->get_the_value('columnSelect') === '3col'  )   { ?>
							
							<?php if ( $repeating_textareas->get_the_value('titleAlignment') === 'center'  )  { ?>
								<header class="titleArea centered">
									<h3 class="sectionTitle"> <?php echo $repeating_textareas->get_the_value( 'sectionTitle' ); ?></h3>
								</header>
							<?php } else { ?>
								<header class="titleArea">
									<h3 class="sectionTitle"> <?php echo $repeating_textareas->get_the_value( 'sectionTitle' ); ?></h3>
								</header>
							<?php } ?>

							<div class="threeCol left">
								<p><?php echo $repeating_textareas->get_the_value( '3col_left_textarea' ); ?></p>
							</div>
							<div class="threeCol middle">
								<p><?php echo $repeating_textareas->get_the_value( '3col_middle_textarea' ); ?></p>
							</div>

							<div class="threeCol right">
								<p><?php echo $repeating_textareas->get_the_value( '3col_right_textarea' ); ?></p>
							</div>
						<?php } ?>

						<!-- If four column layout --> 
							<?php if ( $repeating_textareas->get_the_value('columnSelect') === '4col'  )   { ?>
								<header class="titleArea">
									<h3 class="sectionTitle"> <?php echo $repeating_textareas->get_the_value( 'sectionTitle' ); ?></h3>
								</header>

								<div class="fourCol left1">
									<p><?php echo $repeating_textareas->get_the_value( '4col_left1_textarea' ); ?></p>
								</div>
								<div class="fourCol left2">
									<p><?php echo $repeating_textareas->get_the_value( '4col_left2_textarea' ); ?></p>
								</div>

								<div class="fourCol right1">
									<p><?php echo $repeating_textareas->get_the_value( '4col_right1_textarea' ); ?></p>
								</div>

								<div class="fourCol right2">
									<p><?php echo $repeating_textareas->get_the_value( '4col_right2_textarea' ); ?></p>
								</div>
							<?php } ?>
					</div>
				</div>
			</section>
			<?php } 
		} ?>

		
	</div>

<?php include ( TEMPLATEPATH . '/component-bottomContactLink.php' ); ?>
<?php get_footer() ?>

<?php if ( $pageIntro_mb->get_the_value('hideHero') === 'yes'  )  { ?>
	<script>$('body').addClass('noHero');</script>
<?php } ?>