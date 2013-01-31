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
	<label for="max_order_depth">
	<span id="seramo-permalink"><?php echo get_bloginfo('url'); ?>/</span>
	<input type="text" id="seramo_slug" name="seramo_slug" value="<?php echo get_option ( self::WP_OPTION_SERAMO_SLUG ); ?>">
	</label>
	<p>This must be unique and not conflict with a first level page, if it does it will be amended.</p>
	
	<div style="width:100px">
	<p class="submit">
	<a href="#" id="save-slug" class="button-primary">Update</a><span class="spinner"></span>
	</p>
	</div>
	
	
	</div>
	
	<div id="tab-queries">
		<p>Create custom targets, to recieve data easily!</p>
	</div>
	
</div>
