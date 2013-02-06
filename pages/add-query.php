<?php
$blog_url = get_bloginfo ( 'url' );
$seramo_slug = get_option ( self::WP_OPTION_SERAMO_SLUG );
?>

<?

$wp_args = array (
		'error', 
		'm', 
		'p', 
		'post_parent', 
		'subpost', 
		'subpost_id', 
		'attachment', 
		'attachment_id', 
		'name', 
		'static', 
		'pagename', 
		'page_id', 
		'second', 
		'minute', 
		'hour', 
		'day', 
		'monthnum', 
		'year', 
		'w', 
		'category_name', 
		'tag', 'cat', 
		'tag_id', 
		'author_name', 
		'feed', 
		'tb', 
		'paged', 
		'comments_popup', 
		'meta_key', 
		'meta_value', 
		'preview', 
		's', 
		'sentence', 
		'fields', 
		'menu_order' 
	);
sort ( $wp_args );


$wp_major_args = array(
					'post_type' => array(
						'post_type' => array()
					),
					'post_status' => array(
						'post_status'  => array()
					),
					'meta_query' =>array(
							'key'  => array(),
							'value'  => array(),
							'type'  => array(),
							'compare'  => array()
					)
				);



$post_type_args						= get_post_types();


$meta_query_args					= array ('key', 'value', 'type', 'compare' );

$meta_query_type_args				= array ('NUMERIC', 'BINARY', 'CHAR', 'DATE', 'DATETIME', 'DECIMAL', 'SIGNED', 'TIME', 'UNSIGNED' );

$compare_args						= array ('=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN' );

$post_status_args					= array(
											'publish',                      // - a published post or page.
											'pending',                      // - post is pending review.
											'draft',                        // - a post in draft status.
											'auto-draft',                   // - a newly created post, with no content.
											'future',                       // - a post to publish in the future.
											'private',                      // - not visible to users who are not logged in.
											'inherit',                      // - a revision. see get_children.
											'trash'                         // - post is in trashbin (available with Version 2.9).
										);


$argument_values = array(
		
		'post_type' => $post_type_args,
		'compare' => $compare_args,
		'post_status' => $post_status_args
		
		);

add_thickbox();


?>
<div id="seramo-addquery">

	<div id="seramo-mainpanel" class="active-panel">
		<h2>Create new query target</h2>

		<form id="seramo_addquery_form" method="post">

			<p>
			<label for="seramo_target_title">Query/Target Title<input type="text" id="seramo_target_title" name="seramo_target_title" value=""></label>
			</p>

			<p>
			<label for="seramo_new_target"><span id="seramo-permalink"><?php echo $blog_url; ?>/<span class="seramo_slug"><?php echo $seramo_slug; ?></span>/</span><input type="text" id="seramo_new_target" name="seramo_new_target" value=""></label>
			</p>

			<p><a href="#" class="panel-link button-primary" id="seramo_add_save_btn">Add/Save</a></p>
			
			<p class="submit">
			<select id="wp_args_select">
			<option value=""> </option>
		    <?php foreach($wp_major_args as $arg => $arg_properties): ?>
		     <option value="<?php echo $arg; ?>"> <?php echo $arg; ?> </option>
		     <?php endforeach; ?>
		    </select>
			
			<a href="#" class="panel-link button-primary" id="seramo_add_argument_btn">Add Query Conditon</a>
			
			</p>
			

			<div id="seramo-added-arguments-wrapper">
			
			</div>
			

		</form>
	</div>

	
	<div id="saramo-insert-dumps"  style="display: none;">


		<div class="seramo_arg_type_post_type seramo_argument" argtype="post_type">
			<input type="text" value="post_type" disabled="disabled">
			<select  name="seramo_arg_post_type[]">
				<option value="any">any</option>
				<?php foreach($argument_values['post_type'] as $argument): ?>
				<option value="<?php echo $argument; ?>"> <?php echo $argument; ?> </option>
				<?php endforeach; ?>
			</select>
		</div>
				
	</div>
	
</div>