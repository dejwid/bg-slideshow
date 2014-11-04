<?php
/*
Plugin Name: dBgSlideshow
Plugin URI: http://github.com/dejwid/bg-slideshow
Description: Display images slideshow as website background
Version: 1.0
Author: Dawid Paszko
Author URI: http://github.com/dejwid
License: GPL2
*/

add_action( 'admin_menu', 'register_my_custom_menu_page' );
add_action( 'wp_footer', 'dbgslideshow_init' );

function dbgslideshow_init(){
	$data = json_decode(get_option('dbgslideshow'));
	$imgs = count($data->selected);

    $frontpageId = get_option('page_on_front');
    $homePage = get_page($frontpageId);
    $slideshowImgs = $data->selected;
    $x = '';
    if((get_the_ID() == $frontpageId) || $data->place == 'everywhere'){
    $x = '<div id="slideshow">';
        foreach($slideshowImgs as $k=>$img){
        	$i=$k+1;
            $x.='<div class="slideshow slideshow-slide" id="slide'.$i.'" style="background-image: url(\''.$img.'\');background-position: center center;filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\''.$img.'\',sizingMethod=\'scale\');">';
                //$x.='<img src="'.$img.'" />';
            $x.='</div>';
        }
    $x.='</div></div>';
    echo $x;
    ?>
    <script type="text/javascript" src="<?php echo plugins_url('dbgslideshow/jquery.dbgslideshow.js'); ?>"></script>
    <script type="text/javascript">
    $('body').prepend($('#slideshow'));
    $('#slideshow').dslideshow({
        speed: <?php echo $data->animation; ?>,
        delay: <?php echo $data->slide ?>,
        slides: '.slideshow-slide'
    });
    </script>
    <style>
        #dbgslideshow,.slideshow{top:0;bottom:0;left:0;right:0;position:fixed;}
        .slideshow-slide{background-size:cover;}
    </style>
    <?php
    }
}

function register_my_custom_menu_page(){
    add_menu_page( 'dBgSlideshow', 'dBgSlideshow', 'manage_options', 'dbgslideshow', 'my_custom_menu_page', plugins_url( 'dbgslideshow/small.png' ), 6 ); 
}

function my_custom_menu_page(){
	//SYSTEM
	$query_images = new WP_Query(array(
	    'post_type' => 'attachment',
	    'post_mime_type' =>'image',
	    'post_status' => 'inherit',
	    'posts_per_page' => -1,
	));
	$images = array();
	foreach ( $query_images->posts as $image) {
	    $pimages[] = wp_get_attachment_url( $image->ID );
	}

	$data = get_option('dbgslideshow');
	$defaults = array(
		'animation'=>1000,
		'slide'=>1000,
		'selected'=>array(),
		'order'=>$pimages,
		'place'=>'frontpage'
	);
	//update_option('dbgslideshow',json_encode($defaults));
	if($data===false){
		add_option('dbgslideshow',json_encode($defaults));
		$data = $defaults;
	}else{
		$data = json_decode($data);
	}
	if($_POST['slide']){
		$data->slide = $_POST['slide'];
		$data->animation = $_POST['animation'];
		$data->place = $_POST['place'];
		update_option('dbgslideshow',json_encode($data));
	}
	if($_POST['selected']){
		$data->selected = explode('|',$_POST['selected']);
		$data->order = explode('|',$_POST['order']);
		update_option('dbgslideshow',json_encode($data));
	}

	//echo '<pre>';
	//var_dump($data);
	//echo '</pre>';

	// HTML
	?>
	<script type="text/javascript" src="<?php echo plugins_url('dbgslideshow/jquery.masonry.js'); ?>"></script>
	<script type="text/javascript">
	$ = jQuery;
	jQuery().ready(function(){
		  /*jQuery('.dbgsp').masonry({
		    itemSelector : '.dbgsi',
		    columnWidth : 240
		  });*/
	});
	function saveImgs(){
		//order
		$checkboxes = $('#dbgs input');
		$j = $checkboxes.size();
		$order = '';
		for($i=0;$i<$j;$i++){
			$order += '|' + $checkboxes.eq($i).val();
		}
		$order = $order.substring(1);
		//selected
		$checkboxes = $('#dbgs input').filter(':checked');
		$j = $checkboxes.size();
		$imgs = '';
		for($i=0;$i<$j;$i++){
			$imgs += '|' + $checkboxes.eq($i).val();
		}
		$imgs = $imgs.substring(1);
		//save
		//alert($imgs);
		//alert($order);
		//return false;
		$.post('',{selected:$imgs, order: $order},function(){
			
		});
	}
	function saveData(){
		$.post('',{animation:$('#animation').val(), slide:$('#slide').val(), place:$('#place').val()},function(data){
			alert('zapisano');
		});
	}
	function moveDown(key){
		jQuery('#ir'+key).before(jQuery('#ir'+key).next());
		saveImgs();
	}
	function moveUp(key){
		jQuery('#ir'+key).after(jQuery('#ir'+key).prev());
		saveImgs();
	}
	</script>
	<style type="text/css">
        /* dbgslideshow css */
	.imgRecords{
		clear: both;
	}
	div.dbgs img{
		max-height: 200px;
		width: auto;
	}
	div.dbgs button{
		font-weight: bold;
		font-size: 2em;
		border: 1px solid #ccc;
		background: #eee;
		cursor: pointer;
		margin: 5px 0;
	}
	div.dbgs button:hover{
		background-color: #ddd;
	}
	div.dbgs div:first-child{
		float:left;
		padding-top: 20px;
		padding-right: 5px;
		text-align: center;
	}
	</style>
	<h2>dBgSlideshow</h2>
	<table>
		<tr>
			<td>Miejsce wyświetlania:&nbsp;</td>
			<td>
				<select id="place">
					<option value="frontpage" <?php if($data->place=='frontpage') echo 'selected="selected"'; ?>>Strona główna</option>
					<option value="everywhere" <?php if($data->place=='everywhere') echo 'selected="selected"'; ?>>Wszędzie</option>
				</select>
			</td> 
		</tr>
		<tr>
			<td>Czas animacji:&nbsp;</td>
			<td><input id="animation" type="text" value="<?php echo $data->animation; ?>" />ms</td> 
		</tr>
		<tr>
			<td>Czas slajdu:&nbsp;</td>
			<td><input id="slide" type="text" value="<?php echo $data->slide; ?>" />ms</td> 
		</tr>
		<tr>
			<td></td><td><button onclick="saveData();">Zapisz</button></td>
		</tr>
	</table>
	<?php
	echo '<div class="dbgs" id="dbgs">';
	$key =0;
	foreach($data->order as $img){
		$key++;
		$x = in_array($img, $data->selected)?'checked="checked"':'';
		echo '<div class="imgRecords" id="ir'.$key.'"><div><button onclick="moveUp('.$key.');">&uarr;</button><br /><input onclick="saveImgs();" type="checkbox" '.$x.' value="'.$img.'" /><br /><button onclick="moveDown('.$key.');">&darr;</button></div><img src="'.$img.'" /></div>';
	}
	echo '</div>';
}
?>
