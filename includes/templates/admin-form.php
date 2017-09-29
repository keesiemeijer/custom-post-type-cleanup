<div class="wrap">
	<h1><?php _e( 'Custom Post Type Cleanup', 'custom-post-type-cleanup' ); ?></h1>
	<?php echo $notice; ?>
	<p>
	<?php _e( 'Delete posts from unused custom post types.', 'custom-post-type-cleanup' ); ?>
	(<?php echo $doc_link; ?>)
	</p>
	<p>
		<?php _e( "It's recommended you <strong style='font-weight:bold; color:red;'>make a database backup</strong> before deleting posts.", 'custom-post-type-cleanup' ); ?>
	</p>
	<hr>
	<h3>
		<?php _e( 'Delete Posts', 'custom-post-type-cleanup' ); ?>
	</h3>
	<h4>
		<?php
		/* translators: 1: Total post count, 2: Custom post type count, 3: 'custom post type' or 'custom post types' (single/plural) */
		printf( _n( '%1$d post from %2$d unused %3$s detected!', '%1$d posts from %2$d unused %3$s detected!', $total, 'custom-post-type-cleanup' ), $total, $type_count, $type_str );
		?>
	</h4>

	<form method="post" action="">
		<?php wp_nonce_field( 'custom_post_type_cleanup_nonce', 'security' ); ?>
		<table class='form-table'>
			<tr>
				<th scope='row'>
					<label for="cptc_post_type">
						<?php _ex( 'Post type', 'Form label text for post type', 'custom-post-type-cleanup' ); ?>
					</label>
				</th>
				<td>
					<select id="cptc_post_type" name="cptc_post_type">
						<?php echo $options; ?>
					</select>
					<p class="description">
						<?php _e( 'The post type you want to delete posts from.', 'custom-post-type-cleanup' ); ?>
					</p>
				</td>
			</tr>
		</table>
		<input id="custom_post_type_cleanup" class="button button-primary" name="custom_post_type_cleanup" value="Delete Posts!" type="submit">
		<p class="description" style="margin-top: 1em; margin-bottom: 1em;" >
		<?php
			/* translators: %d: Batch size */
			printf( __( 'Posts are deleted in batches of %d posts.', 'custom-post-type-cleanup' ), $this->batch_size );
		?>
		</p>
	</form>
	<hr>
	<h3>
		<?php _e( 'Re-register unused post types', 'custom-post-type-cleanup' ); ?>
	</h3>
	<p>
		<?php _e( 'Register unused custom post types to inspect or delete the posts in the wp-admin itself.', 'custom-post-type-cleanup' ); ?><br/><br/>
		<?php _e( 'You can register all unused custom post types for a limited time by clicking the following link', 'custom-post-type-cleanup' ); ?><br/>
		<a href="<?php echo $admin_url; ?>"><?php _e( 'Register all unused post types for the next 10 minutes', 'custom-post-type-cleanup' ); ?></a><br/>

	</p>
	<p>
		<?php _e( '<strong>Note</strong>: The custom post type posts can not be viewed in the front side of your website.', 'custom-post-type-cleanup' ); ?>
	</p>
	<hr>
	<p>
		<?php
			/* translators: %s: plugin link */
			printf( __( 'This page is generated by the %s plugin.', 'custom-post-type-cleanup' ), $plugin_link );
		?>
	</p>
</div>
