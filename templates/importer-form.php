<?php
  $post_id = ( isset( $_GET[ 'post_id' ] ) ? intval( $_GET[ 'post_id' ] ) : null );
  $render_data_list = YoutubeImporterImportyoutube\Helper\FeedForm::get_for_render( $post_id );
  $has_any_advanced = false;// Will be changed during first loop.
?>

<div class="main-container-importyoutube">
  <form method="POST" class="youtube_importer_form">
    <?php if( $post_id !== null ) : ?>
      <input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ) ?>">
    <?php endif; ?>
    <?php foreach( $render_data_list as $render_data ) : ?>
      
      <?php youtube_importer_importyoutube_load_template( '_form-field.php', [ 'data' => $render_data ] ); ?>
    <?php endforeach; ?>
	
	
   <button type="button" id="previewimport" class="button button-primary">Preview</button>

    <?php if( $post_id !== null ) : ?>
      <button class="button button-primary youtube_importer_form_submit"><?php echo esc_html__( "Update", 'import-youtube-videos' ); ?></button>
    <?php else : ?>
      <button class="button button-primary youtube_importer_form_submit"><?php echo esc_html__( "Import", 'import-youtube-videos' ); ?></button>
    <?php endif; ?>
  </form>
  <div id="mainloaderdatecheck">
  
  <div class="impt-video">
  <div class="import-header"><h4>Select Videos that you want to import</h4> <span class="close-imp">
  <img src="<?php echo plugin_dir_path( __FILE__ ); ?>assets/close.png"/></span></div>
  <img id="loadingimage" src="<?php echo plugin_dir_path( __FILE__ ); ?>assets/load.gif" style="display:none;"/>
  <div id="importedvideoscheck">
	
	</div>
	</div>
	</div>
</div>  