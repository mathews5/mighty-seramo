<?php 
	$blog_url = get_bloginfo('url');
	$seramo_slug = get_option ( self::WP_OPTION_SERAMO_SLUG );
	
	var_dump($_POST);
?>
<div id="icon-options-general" class="icon32"></div>
<h2>mightycore - json request stuff lad</h2>
<hr />
<div id="tabs">
	<ul>
		<li><a href="#tab-general">Settings</a></li>
		<li><a href="#tab-queries">Queries</a></li>
	</ul>
	
	<div id="tab-general">
	<p>JSON slug to target</p>
	<label for="seramo_slug">
	<span id="seramo-permalink"><?php echo $blog_url; ?>/</span>
	<input type="text" id="seramo_slug" name="seramo_slug" value="<?php echo $seramo_slug; ?>">
	</label>
	<p>This must be unique and not conflict with a first level page, if it does it will be amended.</p>
	
	<div style="width:100px">
	<p class="submit">
	<a href="#" id="save-slug" class="button-primary">Update</a><span class="spinner"></span>
	</p>
	</div>
	
	<p>Create a <a href="?#TB_inline&width=800&height=650&inlineId=seramo-thickbox" class="thickbox seramo-thickbox seramo-gotoqueries">custom query</a> to get data quckly and easily!</p>
	</div>
	
	<div id="tab-queries">
		<p>Create a <a href="#TB_inline?width=800&height=650&inlineId=seramo-thickbox" class="thickbox seramo-thickbox">custom target</a> to recieve data easily!</p>
		<hr/>
		<h2>Query Based Targets</h2>		
		
		<h2>Function Based Targets</h2>
		<?php 
		
			$registered_callbacks = self::$callbacks;
			
			foreach($registered_callbacks as $cb_slug => $callback):
			?><h3 class="title"><?php echo $callback['settings']['title']; ?> <span class="seramo_registerd_by">Registerd By: <span class="seramo_registerd_type"><?php echo $callback['reg_type']; ?></span></span></h3><?php
			?><p>Target: <span id="seramo-permalink"><?php echo $blog_url; ?>/<span class="seramo_slug"><?php echo $seramo_slug; ?></span>/<?php echo $cb_slug?></span></p><?php
			?><p class="description"><?php  echo $callback['settings']['description']; ?></p><?php
			?><p>Expected Paremters: <?php 
			if(!empty($callback['expected_parameters'])){
				echo implode(', ',$callback['expected_parameters']);
			}else{
				echo 'None';
			}?></p><?php
			
			endforeach;
		?>
		
	</div>
	
</div>
<?php add_thickbox(); ?>
<div id="seramo-thickbox"  style="display:none;">
<div id="seramo-thickbox-content"></div>
</div>
<?php 
	//include('add-query.php');
?>