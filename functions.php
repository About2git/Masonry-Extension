<?php

// =========================================
// = Masonry (Pinterest) layout for Posts =
// =========================================

if ( is_admin() )
	return;

if ( ! class_exists( 'BuilderExtensionMasonryLayout' ) ) {
	class BuilderExtensionMasonryLayout {

		function BuilderExtensionMasonryLayout() {

            // Include the file for setting the image sizes
			require_once( dirname( __FILE__ ) . '/lib/image-size.php' );

			// Helpers
			it_classes_load( 'it-file-utility.php' );
			$this->_base_url = ITFileUtility::get_url_from_file( dirname( __FILE__ ) );

			// Calling only if not on a singular
			if ( ! is_singular() ) {
                // Print necessary scripts and styles.
                add_action( 'wp_enqueue_scripts', array( &$this, 'do_enqueues' ) );

				add_action( 'builder_layout_engine_render', array( &$this, 'change_render_content' ), 0 );
			}
		}

        function do_enqueues() {

            wp_enqueue_script( 'infinitescroll',  "$this->_base_url/js/jquery.infinitescroll.min.js", array('jquery'), '1.0', true );
            wp_enqueue_script( 'jquery-masonry' );

            add_action( 'print_footer_scripts', array(&$this, 'my_footer_script' ));
		}

        function my_footer_script() { ?>

			<script type="text/javascript">

			jQuery(document).ready(function($){

			    var $container = $('.builder-module-content .loop-content');

			    $container.imagesLoaded( function(){
			        $container.masonry({
			            itemSelector: '.hentry',
			            isAnimated: true,
			        });
			    });

			    $container.infinitescroll({
			        navSelector  : '.loop-utility',    // selector for the paged navigation
			        nextSelector : '.loop-utility .alignright a',  // selector for the NEXT link (to page 2)
			        itemSelector : '.hentry',     // selector for all items you'll retrieve
			        loading: {
			            finishedMsg: 'No more posts to load.',
			            img: '<?php echo $this->_base_url; ?>/images/loader.gif'
			        }
			    },

			        // trigger Masonry as a callback
			        function( newElements ) {
			            // hide new items while they are loading
			            var $newElems = $( newElements ).css({ opacity: 0 });
			            // ensure that images load before adding to masonry layout
			            $newElems.imagesLoaded(function(){
			                // show elems now they're ready
			                $newElems.animate({ opacity: 1 });
			                $container.masonry( 'appended', $newElems, true );
			            });
			        }
			    );

			    $(".hentry img").hover(
			        function () {
			            $(this).stop().fadeTo("fast", 0.7);
			        },
			        function () {
			            $(this).stop().fadeTo("fast", 1);
			        }
			    );



			});

			</script>

		<?php }

		function extension_render_content() {
			add_filter( 'excerpt_length', array( &$this, 'excerpt_length' ) );
			add_filter( 'excerpt_more', array( &$this, 'excerpt_more' ) );
		?>
			<?php if ( have_posts() ) : ?>
				<div class="loop">
					<div class="loop-content">
						<?php while ( have_posts() ) : // the loop ?>
							<?php the_post(); ?>

							<div <?php post_class('masonry-post-wrap'); ?>>
								<div class='masonry-post'>
									<div class="entry-header">
										<?php if ( has_post_thumbnail() ) : ?>
											<div class="entry-image">
												<a class="post-image" href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'it-masonry-thumb' ); ?></a>
											</div>
										<?php else : ?>
											<?php edit_post_link( '<img src="' . $this->_base_url . '/images/no-feature-image.jpg" class="it-masonry-thumb no-thumb" />', '<div class="post-image">', '</div>' ) ; ?>
										<?php endif; ?>

										<h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
										<div class="entry-meta">
											<?php printf( __( '%s', 'it-l10n-Builder' ), '<span class="the_date">' . get_the_date() . '</span>' ); ?>
											<?php do_action( 'builder_comments_popup_link', '<span class="comments"> | ', '</span>', __( '%s Comments', 'it-l10n-Builder' ), __( '0', 'it-l10n-Builder' ), __( '1', 'it-l10n-Builder' ), __( '%', 'it-l10n-Builder' ) ); ?>
										</div>
									</div>
									<div class="entry-content">
										<?php the_excerpt(); ?>
									</div>
								</div>
							</div>
						<?php endwhile; // end of one post ?>
					</div>
					<!-- Previous/Next page navigation -->
					<div class="loop-footer">
						<div class="loop-utility clearfix">
							<div class="alignleft"><?php previous_posts_link( __( '&laquo; Previous Page' , 'it-l10n-Builder' ) ); ?></div>
							<div class="alignright"><?php next_posts_link( __( 'Next Page &raquo;', 'it-l10n-Builder' ) ); ?></div>
						</div>
					</div>
				</div>
			<?php else : // do not delete ?>
				<?php do_action( 'builder_template_show_not_found' ); ?>
			<?php endif; // do not delete ?>
		<?php
			remove_filter( 'excerpt_length', array( &$this, 'excerpt_length' ) );
			remove_filter( 'excerpt_more', array( &$this, 'excerpt_more' ) );
		}

		function excerpt_length( $length ) {
			return 40;
		}

		function excerpt_more( $more ) {
			global $post;
			return '...<p><a href="'. get_permalink( $post->ID ) . '" class="more-link">Continue Reading &rarr;</a></p>';
		}

		function change_render_content() {
			remove_action( 'builder_layout_engine_render_content', 'render_content' );
			add_action( 'builder_layout_engine_render_content', array( &$this, 'extension_render_content' ) );
		}


	} // end class

	$BuilderExtensionMasonryLayout = new BuilderExtensionMasonryLayout();
}