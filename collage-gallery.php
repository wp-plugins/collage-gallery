<?php
/*
Plugin Name: Collage Gallery
Description: Plugin automatically create responsive collage gallery (like Google, Flickr, VK.com...) from images attached to the post.
Version: 0.1
Plugin URI: http://ukraya.ru/collage-gallery/
Author: Aleksej Solovjov
Author URI: http://ukraya.ru
Text Domain: collage-gallery
Domain Path: /languages/
License: GPL v2 or later
*/

/*  Copyright 2015 Aleksej Solovjov

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

*/  

/* Admin Page */

add_action ('admin_init', 'ug_init');
function ug_init () {       
  load_plugin_textdomain('collage-gallery', FALSE, dirname(plugin_basename(__FILE__)).'/languages/');
}

if (!class_exists('WP_Settings_API_Class'))
  include_once('inc/wp-settings-api-class.php'); 
  
add_action('wp_head', 'ug_wp_head', 99 );
function ug_wp_head () {
  global $post;
  $options = get_option('ug_options'); 
   
  echo '<style type="text/css">';
  echo '
  .collage_gallery:before, 
  .collage_gallery:after {
    content: "";
    display: table;
    clear:both;
  }

  .collage_gallery {
    display:block; 
    max-width: 100%; 
    margin-bottom:20px;
  }

  .collage_img_wrap {
    height: auto; 
    max-width: 100%;
    margin-bottom:20px;  
  }

  .collage_img_wrap img {
    border: 0 none;
    vertical-align: middle; 
    height: auto;
    max-width: 100%;
  }

  .collage_img_wrap span {
    font-size:11px;
  }
';   
  
  if (isset($options['css']) && !empty ($options['css'])) 
    echo $options['css'];
    
  echo '</style>';
  
  /*
  echo '<script type="text/javascript">';   
  echo '</script>';  
  */
}  
  
add_action ('wp_footer', 'ug_wp_footer');
function ug_wp_footer() {
  $options = get_option('ug_options');

  // http://miromannino.github.io/Justified-Gallery/options-and-events/
?>
<script type="text/javascript" >
  
  jQuery(document).ready(function($) {
    
    $(".ug").justifiedGallery({
      rowHeight: <?php echo $options['row_height']; ?>,
      maxRowHeight: <?php echo $options['max_row_height']; ?>, 
      <?php /*
      sizeRangeSuffixes: {
        'lt100':'',
        'lt240':'',
        'lt320':'',
        'lt500':'',
        'lt640':'',
        'lt1024':''
      },
      */ ?>
      lastRow: '<?php echo $options['last_row']; ?>', 
      fixedHeight: <?php echo $options['fixed_height'] == 1 ? 'true' : 'false'; ?>, 
      captions: <?php echo $options['caption'] == 1 ? 'true' : 'false'; ?>,
      margins: <?php echo $options['margins']; ?>,
      border: <?php echo $options['border']; ?>
      <?php /*
      randomize: false,
      extension: "/.[^.]+$/",
      refreshTime: 250,
      waitThumbnailsLoad: true,
      rel:null,
      target:null,
      justifyThreshold:'0.35', // If available space / row width < 0.35 the last row is justified, without considering the lastRow setting.
      cssAnimation: false,
      imagesAnimationDuration:'300',
      captionSettings: { 
        animationDuration: 500,
        visibleOpacity: 0.7,
        nonVisibleOpacity: 0.0 
      }
      */ ?>   
    });
    
  });
</script>
<?php
}

add_action('wp_enqueue_scripts', 'ug_enqueue_scripts');
function ug_enqueue_scripts () {

  wp_enqueue_script( 'justifiedGallery', plugins_url('js/jquery.justifiedGallery.min.js', __FILE__), array('jquery') );
  
  wp_register_style( 'justifiedGallery', plugins_url('css/justifiedGallery.min.css', __FILE__) );
  wp_enqueue_style( 'justifiedGallery' );    
}  


// photo = "1,3,4-12"
function ug_gallery_helper ($photo) {

  $photo_num_show = array();
  if (strpos($photo, ',') && strpos($photo, '-')) {
    $photos = explode(',', $photo);       
    foreach($photos as $ps) {
      if (strpos($ps, '-')) {
        $p = explode('-', $ps);
        for ($i = $p[0]; $i < ($p[1] + 1); $i++)
          $photo_num_show[] = $i;  
      }
      else
        $photo_num_show[] = $ps; 
    }
  }
  elseif (strpos($photo, ',' ) && !strpos($photo, '-' )) {
    $photos = explode(',', $photo);       
    foreach($photos as $ps)
      $photo_num_show[] = $ps;    
  }
  elseif(!strpos($photo, ',') && strpos($photo, '-')){
    $p = explode('-', $photo);
    for ($i = $p[0]; $i < ($p[1] + 1); $i++)
      $photo_num_show[] = $i;      
  }
  else{
    $photo_num_show[] = $photo;   
  }
  
  return $photo_num_show;
}

add_shortcode('collage_gallery', 'ug_shortcode');
function ug_shortcode ($atts = array(), $content = '') {
  global $post;
  $options = get_option('ug_options');
    
  if (isset($options['meta_name']) && !empty($options['meta_name']) && get_post_meta($post->ID, $options['meta_name'], true) == false )    
    return '';
  
  if (!empty($atts))
    extract ($atts);
    
  $images = get_children( array( 
    'post_parent' => $post->ID, 
    'post_type' => 'attachment', 
    'post_mime_type' => 'image', 
    'orderby' => 'menu_order id', 
    'order' => 'ASC', 
    'numberposts' => 999 
  ));
  $n = count($images);  
 
  $out = '';
  $i = 1;

  if ( $images ) {  
    $photo_num_show = (isset($photo))  ? ug_gallery_helper($photo) : null;

    foreach($images as $image) {
      $temp = '';
      
      // Images link
      $link = get_permalink($image->ID);
      $src = wp_get_attachment_image_src( $image->ID, 'large' );
      
      if (isset($photo_num_show) && !empty($photo_num_show)){
        if (!in_array($i, $photo_num_show)){
          $i++;
          continue;
        }
      }  
      
      $title = the_title_attribute(array(
        'echo' => false, 
        'post' => $image->ID
      )); 
      
      $options['link'] = isset($options['link']) ? $options['link'] : 'page';
      
      $img = '<img alt="'.$title.'" src="'.$src[0].'"/>';   
      $href = '';        
      if ($options['link'] == 'image' )
        $href =$image->guid;    
      
      if ($options['link'] == 'page' )
        $href = get_permalink($image->ID);     
      
      $a = '<a href="'.$href.'">'.$img.'</a>';
      
      if ($options['link'] == 'none' ) {
        $a = $img;
      }
                    
      $caption = '';               
      if (!empty($image->post_content))
        $caption = '<div class = "caption">' . $image->post_content . '</div>';
      
      $class = '';
      // one_image == cresponsive
      if ($n == 1 && $options['one_image'] == 'responsive') {
        
        if (!empty($image->post_content))
          $caption = '<p>' . $image->post_content . '</p>';
        
        $class = 'collage_img_wrap';
      }
            
      $temp = '<div '. $class .'>' .$a . $caption . '</div>'; 
      
      // one_image == none
      if ($n == 1 && $options['one_image'] == 'none') {
        $temp = '';
      }
      
      $out[] = apply_filters(
        'ug_shortcode_item', 
        $temp, 
        $image,
        array(
          'class' => $class,
          'src' => $src,
          'title' => $title,
          'href' => $href,
          'caption' => $caption,
          
          'img' => $img,
          'a' => $a
        ) 
      );
   
      $i++;
    }
  }
  if (!empty($out) && is_array($out))
    $out = implode('', $out);
  
  if (!empty($out) && ($n != 1 || ($n == 1 && $options['one_image'] == 'collage') ) ) {
    $out = '<div class = "ug">' . $out . '</div>'; 
  }
  
  return $out;
}

add_filter ('the_excerpt', 'ug_the_content_filter', 1);
add_filter ('the_content', 'ug_the_content_filter', 1);
function ug_the_content_filter ($content) {
  global $post;
  $options = get_option('ug_options');
  
  if (isset($options['insert']) && $options['insert'] == 'auto' && isset($options['insert_in']) ) {
    
    foreach($options['insert_in'] as $key => $value ) {
      
      if (call_user_func('is_'.$key)) {  
      
        $pattern = get_shortcode_regex();
        preg_match_all('/'.$pattern.'/s', $post->post_content, $matches);
        if ((!is_array($matches) || !in_array('collage_gallery', $matches[2]))) {
          $content .= do_shortcode('[collage_gallery]');
        }
        
        break;
      }
    }
  }
  
  return $content;
}


function ug_settings_admin_init() {
  global $ug_settings;

  $ug_settings = new WP_Settings_API_Class;
  
  $tabs = array(
    'ug_options' => array(
      'id' => 'ug_options',
      'name' => 'ug_options',
      'title' => __( 'Collage Gallery', 'collage-gallery' ),
      'desc' => __( '', 'collage-gallery' ),
      'sections' => array(
      
        'ug_section' => array(
          'id' => 'ug_section',
          'name' => 'ug_section',
          'title' => __( 'Global Settings', 'collage-gallery' ),
          'desc' => __( 'Collage Gallery plugin global settings.', 'collage-gallery' ),          
        ),

        'ug_jg' => array(
          'id' => 'ug_jg',
          'name' => 'ug_jg',
          'title' => __( 'Collage Settings', 'collage-gallery' ),
          'desc' => __( 'Collage based on jQuery plugin "Justified Gallery" by <i>miromannino</i> (<a href = "https://github.com/miromannino/Justified-Gallery" target = "_blank">git</a>). Explanation of the settings are taken from plugin official <a href = "http://miromannino.github.io/Justified-Gallery/" target = "_blank">site</a>.', 'collage-gallery' ),          
        ),
        
      )
    )    
  ); 
  $tabs = apply_filters('ug_tabs', $tabs, $tabs); 
  
  $fields = array(
    'ug_section' => array(
    
     array(
        'name' => 'insert',
        'label' => __( 'Add to post', 'collage-gallery' ),
        'desc' => __( 'Plugin can automatically convert all attached to the post images in a collage or you can use shortcode <i>[collage_gallery]</i>.', 'collage-gallery' ),
        'type' => 'radio',
        'default' => 'auto',
        'options' => array(
          'auto' => __( 'Auto', 'collage-gallery' ),
          'manual' => __( 'Manual / <i>use shortcode [collage_gallery]</i>', 'collage-gallery' )
        )
      ),        
   
      array(
        'name' => 'insert_in',
        'label' => __( 'Show in pages', 'collage-gallery' ),
        'desc' => __( 'You can select the pages types on which will be added collage.', 'collage-gallery' ),
        'type' => 'multicheck',
        'default' => 'single',
        'options' => array(
          'front_page' => __( 'Front page, <small>is_front_page()</small>.', 'collage-gallery' ),
          'single' => __( 'Single (posts) pages, <small>is_single()</small>.', 'collage-gallery' ),
          'tax' => __( 'Tax pages, <small>is_tax()</small>.', 'collage-gallery' )
        )
      ),
      
     array(
        'name' => 'one_image',
        'label' => __( 'If one image', 'collage-gallery' ),
        'desc' => __( 'Actions, if only one image in post.', 'collage-gallery' ),
        'type' => 'radio',
        'default' => 'none',
        'options' => array(
          'none' => __( 'Hide image', 'collage-gallery' ),
          'collage' => __( 'Show as Collage', 'collage-gallery' ),
          'responsive' => __( 'Show as responsive image', 'collage-gallery' )
        )
      ),
 
     array(
        'name' => 'link',
        'label' => __( 'Link', 'collage-gallery' ),
        'desc' => __( 'When click on image, opens...', 'collage-gallery' ),
        'type' => 'radio',
        'default' => 'page',
        'options' => array(
          'page' => __( 'Image page / attachment page', 'collage-gallery' ),
          'image' => __( 'Image', 'collage-gallery' ),
          'none' => __( 'Nothing happens', 'collage-gallery' )
        )
      ), 
              
      array(
        'name' => 'meta_name',
        'label' => __( 'post_meta name', 'collage-gallery' ),
        'desc' => __( 'Add collage to post only if it has <i>post_meta</i> with given name. Leave blank if not required.', 'collage-gallery' ),
        'type' => 'text',
      ), 
                   
    ),
   
    'ug_jg' => array(
      array(
        'name' => 'row_height',
        'label' => __( 'Row Height', 'collage-gallery' ),
        'desc' => __( 'The approximately height of rows in pixel (px).<br/>i.e.: <code>120</code>.', 'collage-gallery' ),
        'type' => 'text',
        'default' => '120'
      ), 
      array(
        'name' => 'max_row_height',
        'label' => __( 'Max&nbsp;row&nbsp;height', 'collage-gallery' ),
        'desc' => __( "The maximum row height in pixel. Negative value to haven't limits. Zero to have a limit of <i>1.5 * Row Height</i>.<br/>i.e.: <code>0</code>.", 'collage-gallery' ),
        'type' => 'text',
        'default' => '0'
      ),  
      array(
        'name' => 'last_row',
        'label' => __( 'Last Row', 'collage-gallery' ),
        'desc' => __( "Decide if you want to justify the last row or not, or to hide the row if it can't be justified.", 'collage-gallery' ),
        'type' => 'radio',
        'default' => 'nojustify',
        'options' => array(
          'nojustify' => __( 'No Justify', 'collage-gallery' ),
          'justify' => __( 'Justify', 'collage-gallery' ),
          'hide' => __( 'Hide', 'collage-gallery' )         
        )
      ), 
      array(
        'name' => 'fixed_height',
        'label' => __( 'Fixed Height', 'collage-gallery' ),
        'desc' => __( "Decide if you want to have a fixed height. This mean that all the rows will be exactly with the specified Row Height.", 'collage-gallery' ),
        'type' => 'radio',
        'default' => '0',
        'options' => array(
          '1' => __( 'Yes', 'collage-gallery' ),           
          '0' => __( 'No', 'collage-gallery' )      
        )
      ),   
      array(
        'name' => 'caption',
        'label' => __( 'Caption', 'collage-gallery' ),
        'desc' => __( "Decide if you want to show the caption or not, that appears when your mouse is over the image.", 'collage-gallery' ),
        'type' => 'radio',
        'default' => '1',
        'options' => array(
          '1' => __( 'Yes', 'collage-gallery' ),
          '0' => __( 'No', 'collage-gallery' )       
        )
      ), 
      array(
        'name' => 'margins',
        'label' => __( 'Margins', 'collage-gallery' ),
        'desc' => __( "Decide the margins (px) between the images.", 'collage-gallery' ),
        'type' => 'text',
        'default' => '1'
      ),
      array(
        'name' => 'border',
        'label' => __( 'Border', 'collage-gallery' ),
        'desc' => __( "Decide the border size (px) of the gallery. With a negative value the border will be the same as the margins.", 'collage-gallery' ),
        'type' => 'text',
        'default' => '-1'
      ),  
      array(
        'name' => 'css',
        'label' => __( 'CSS', 'collage-gallery' ),
        'desc' => __( "Add custom CSS.", 'collage-gallery' ),
        'type' => 'textarea'
      ),                                     
    )    
         
  );
  $fields = apply_filters('ug_fields', $fields, $fields);
  
 //set sections and fields
 $ug_settings->set_option_name( 'ug_options' );
 $ug_settings->set_sections( $tabs );
 $ug_settings->set_fields( $fields );

 //initialize them
 $ug_settings->admin_init();

}
add_action( 'admin_init', 'ug_settings_admin_init' );


// Register the plugin page
function ug_admin_menu() {
  global $ug_settings_page; 

  add_menu_page( __('Collage Gallery', 'collage-gallery'), __('Collage Gallery', 'collage-gallery'), 'activate_plugins', 'collage-gallery', 'ug_settings_page', null, '99' ); 
     
  $ug_settings_page = add_submenu_page( 'collage-gallery', __('Collage Gallery', 'collage-gallery'), __('Collage Gallery', 'collage-gallery'), 'activate_plugins', 'collage-gallery', 'ug_settings_page' );
 
}
add_action( 'admin_menu', 'ug_admin_menu', 20 );




// Display the plugin settings options page
function ug_settings_page() {
  global $ug_settings; 

  echo '<div class="wrap">';
    echo '<div id="icon-options-general" class="icon32"><br /></div>';
    echo '<h2>'.__('Collage Gallery', 'collage-gallery').'</h2>';
 
    echo '<div id = "col-container">';  
      echo '<div id = "col-right" class = "evc">';
        echo '<div class = "evc-box">';
        ug_ad();
        echo '</div>';
      echo '</div>';
      echo '<div id = "col-left" class = "evc">';
        settings_errors();
        $ug_settings->show_navigation();
        $ug_settings->show_forms();
      echo '</div>';
    echo '</div>';  
        
  echo '</div>';
}


add_action('admin_head', 'ug_admin_head', 99 );
function ug_admin_head () {
  
  if ( isset($_GET['page']) && $_GET['page'] == 'collage-gallery' ) {

?>
  <style type="text/css">
    #col-right.evc {
      width: 35%;
    }
    #col-left.evc {
      width: 64%;
    }    
    .evc-box{
      padding:0 20px 0 40px;
    }
    .evc-boxx {
      background: none repeat scroll 0 0 #FFFFFF;
      border-left: 4px solid #2EA2CC;
      box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.1);
      margin: 5px 0 15px;
      padding: 1px 12px;
    }
    .evc-boxx h3 {
      line-height: 1.5;
    }
    .evc-boxx p {
      margin: 0.5em 0;
      padding: 2px;
    }
  </style> 
  <script type="text/javascript" >  
  jQuery(document).ready(function($) {
    
    if ($(".evc-box").length) {
    
      $("#col-right").stick_in_parent({
        parent: '#col-container',
        offset_top: $('#wpadminbar').height() + 10,
      });
    }
  });
  </script>
<?php
  }
}    

function ug_ad () {

  echo '
    <div class = "evc-boxx">
      <p>'.__('Collage Gallery plugin <a href = "http://ukraya.ru/collage-gallery/support" target = "_blank">Support</a>', 'collage-gallery') . '</p>
    </div>';
}    

add_action('admin_init', 'ug_admin_init'); 
function ug_admin_init () { 
  if ( isset($_GET['page']) && $_GET['page'] == 'collage-gallery' ) {
    wp_enqueue_script('sticky-kit', plugins_url('js/jquery.sticky-kit.min.js' , __FILE__), array('jquery'), null, false); 
  }
}

