<div class="wrap">
	<h1><?php _e( 'Custom Post Type Cleanup', 'custom-post-type-cleanup' ); ?></h1>
	<?php echo $notice; ?>
	<p>
	<?php _e( 'Delete posts from unused custom post types.', 'custom-post-type-cleanup' ); ?>
	(<?php echo $doc_link; ?>)<br/>
	<?php _e( 'The following unused custom post types are registered by this plugin for a limited time.', 'custom-post-type-cleanup' ); ?>
	</p>
	<p><strong><?php _e( 'Registered custom post types', 'custom-post-type-cleanup' ); ?>:</strong>
	<ul>
	<?php foreach ( $registered_post_types as $post_type ) : ?>
		<li><a href="<?php echo admin_url( 'edit.php?post_type=' . $post_type ); ?>"><?php echo $post_type; ?></a></li>
	<?php endforeach; ?>
	</ul>
	<?php if ( $mins ) : ?>
		<p>
		<?php if ( 1 === (int) $mins ) : ?>
			<?php printf( __( '<strong>%d minute</strong> to go before these post type are no longer registered.', 'custom-post-type-cleanup' ), $mins ); ?>
		<?php elseif ( $mins > 1 ) : ?>
		<?php
		/* translators: %d: total of minutes left */
		printf( __( '<strong>%d minutes</strong> to go before these post type are no longer registered.', 'custom-post-type-cleanup' ), $mins );
		?>
		<?php endif; ?>
			(<a href="<?php echo $admin_url; ?>"><?php _e( 'stop registering now', 'custom-post-type-cleanup' ); ?></a>)
		</p>
	<?php endif; ?>
	<hr>
	<p>
		<?php
		/* translators: %s: WordPress plugin repository link */
		printf( __( 'This page is generated by the %s plugin.', 'custom-post-type-cleanup' ), $plugin_link );
		?>
	</p>
</div>
