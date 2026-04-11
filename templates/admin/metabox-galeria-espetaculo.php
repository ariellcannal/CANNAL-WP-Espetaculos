<?php
/**
 * Template: Metabox Galeria do Espetáculo
 *
 * Variáveis disponíveis:
 * @var string      $galeria        post_meta '_espetaculo_galeria'
 * @var array[int]  $galeria_ids    ID's das imagens da galeria
 * @var WP_Post     $post           Post
 * 
 */
if (! defined('ABSPATH'))
    exit();
?>
<div class="espetaculo-galeria-container">
	<div class="espetaculo-galeria-images">
        <?php if (! empty($galeria_ids)) :?>
            <?php foreach ($galeria_ids as $image_id) :?>
            	<?php $image_url = wp_get_attachment_image_url($image_id, 'thumbnail'); ?>
                <?php if ($image_url) :?>
                    <div class="espetaculo-galeria-image" data-id="<?php esc_attr($image_id) ?>">
                        <img src="<?php esc_url($image_url) ?>" />
                        <button type="button" class="remove-image">×</button>
                    </div>
                <?php endif;?>
            <?php endforeach;?>
        <?php endif;?>
    </div>
	<input type="text" id="espetaculo_galeria" name="espetaculo_galeria" class="hidden" value="<?php echo esc_attr( $galeria ); ?>" readonly />
	<button type="button" class="button espetaculo-add-galeria"><?php esc_html_e( 'Adicionar Imagens', 'cannal-espetaculos' ); ?></button>
</div>