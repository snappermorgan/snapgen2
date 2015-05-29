<?php 
/**
 * Post Navigation Template
 * @package themify
 * @since 1.0.0
 */
if(!themify_check('setting-post_nav_disable')):
	$in_same_cat = themify_check('setting-post_nav_same_cat')? true: false; ?>
	<!-- post-nav -->
	<div class="post-nav clearfix"> 
		<?php previous_post_link('<span class="prev">%link</span>', '<span class="arrow">&lt;</span> %title', $in_same_cat) ?>
		<?php next_post_link('<span class="next">%link</span>', '<span class="arrow">&gt;</span> %title', $in_same_cat) ?>
	</div>
	<!-- /post-nav -->
<?php endif; ?>