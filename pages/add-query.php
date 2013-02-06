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

$meta_query_args = array ('key', 'value', 'type', 'compare' );

$meta_query_type_args = array ('NUMERIC', 'BINARY', 'CHAR', 'DATE', 'DATETIME', 'DECIMAL', 'SIGNED', 'TIME', 'UNSIGNED' );

$meta_query_compare_args = array ('=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN' );

add_thickbox();

?>
<div id="seramo-addquery">

	<div id="seramo-mainpanel" class="active-panel">
		<h2>Create new query target</h2>

		<form>

			<p>
			<label for="seramo_target_title">Query/Target Title<input type="text" id="seramo_target_title" name="seramo_target_title" value=""></label>
			</p>

			<p>
			<label for="seramo_new_target"><span id="seramo-permalink"><?php echo $blog_url; ?>/<span class="seramo_slug"><?php echo $seramo_slug; ?></span>/</span><input type="text" id="seramo_new_target" name="seramo_new_target" value=""></label>
			</p>

			<p class="submit"><a href="#my-content-id" class="panel-link button-primary">Add Query Conditon</a></p>
			



		</form>
	</div>

	<div id="my-content-id" style="display: none;">
		<p><a href="#seramo-mainpanel" class="panel-link button-primary">Back</a></p>
		
		
		
	</div>
	
	
	<div id="saramo-insert-dumps"  style="display: none;">
				<p>
				<select>
	     <?php foreach($wp_args as $arg): ?>
	     <option value="<?php echo $arg; ?>"><?php echo $arg; ?></option>
	     <?php endforeach; ?>
	     </select> <select>
	     <?php foreach($meta_query_compare_args as $arg): ?>
	     <option value="<?php echo $arg; ?>"><?php echo $arg; ?></option>
	     <?php endforeach; ?>
	     </select>
			</p>
	</div>
	
</div>

