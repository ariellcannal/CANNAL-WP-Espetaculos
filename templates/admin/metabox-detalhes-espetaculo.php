<?php
/**
 * Template: Metabox Detalhes do Espetáculo
 *
 * Variáveis disponíveis:
 * @var string  $autor          post_meta '_espetaculo_autor'
 * @var string  $diretor        post_meta '_espetaculo_diretor'
 * @var string  $elenco         post_meta '_espetaculo_elenco'
 * @var string  $sinopse        post_meta '_espetaculo_sinopse'
 * @var string  $ano_estreia    post_meta '_espetaculo_ano_estreia'
 * @var string  $duracao        post_meta '_espetaculo_duracao'
 * @var string  $classificacao  post_meta '_espetaculo_classificacao'
 * @var int     $logotipo_id    post_meta '_espetaculo_logotipo'
 * @var int     $icone_id       post_meta '_espetaculo_icone'
 * @var string  $exibir_galeria post_meta '_espetaculo_exibir_galeria'
 * @var string  $icone_url      attachment_image_url - thumbnail
 * @var string  $logotipo_url   attachment_image_url - medium
 * @var WP_Post $post           Post
 * 
 */
if (! defined('ABSPATH'))
    exit();
?>
<table class="form-table">
	<tr>
		<th><label for="espetaculo_autor"><?php esc_html_e( 'Autor', 'cannal-espetaculos' ); ?></label></th>
		<td><input type="text" id="espetaculo_autor" name="espetaculo_autor" value="<?php echo esc_attr( $autor ); ?>" class="regular-text" /></td>
	</tr>
	<tr>
		<th><label for="espetaculo_diretor"><?php esc_html_e( 'Diretor', 'cannal-espetaculos' ); ?></label></th>
		<td><input type="text" id="espetaculo_diretor" name="espetaculo_diretor" value="<?php echo esc_attr( $diretor ); ?>" class="regular-text" /></td>
	</tr>
	<tr>
		<th><label for="espetaculo_elenco"><?php esc_html_e( 'Elenco', 'cannal-espetaculos' ); ?></label></th>
		<td><textarea id="espetaculo_elenco" name="espetaculo_elenco" rows="3" class="large-text"><?php echo esc_textarea( $elenco ); ?></textarea></td>
	</tr>
	<tr>
		<th><label for="espetaculo_sinopse"><?php esc_html_e( 'Sinopse', 'cannal-espetaculos' ); ?></label></th>
		<td><textarea id="espetaculo_sinopse" name="espetaculo_sinopse" rows="3" class="large-text"><?php echo esc_textarea( $sinopse ); ?></textarea></td>
	</tr>
	<tr>
		<th><label for="espetaculo_ano_estreia"><?php esc_html_e( 'Ano de Estreia', 'cannal-espetaculos' ); ?></label></th>
		<td><input type="number" id="espetaculo_ano_estreia" name="espetaculo_ano_estreia" value="<?php echo esc_attr( $ano_estreia ); ?>" class="small-text" min="1900" max="2100" /></td>
	</tr>
	<tr>
		<th><label for="espetaculo_duracao"><?php esc_html_e( 'Duração', 'cannal-espetaculos' ); ?></label></th>
		<td><input type="text" id="espetaculo_duracao" name="espetaculo_duracao" value="<?php echo esc_attr( $duracao ); ?>" class="regular-text" placeholder="Ex: 90 minutos" /></td>
	</tr>
	<tr>
		<th><label for="espetaculo_classificacao"><?php esc_html_e( 'Classificação Indicativa', 'cannal-espetaculos' ); ?></label></th>
		<td><select id="espetaculo_classificacao" name="espetaculo_classificacao">
				<option value="livre" <?php selected( $classificacao, 'livre' ); ?>><?php esc_html_e( 'Livre', 'cannal-espetaculos' ); ?></option>
				<option value="10" <?php selected( $classificacao, '10' ); ?>><?php esc_html_e( '10 anos', 'cannal-espetaculos' ); ?></option>
				<option value="12" <?php selected( $classificacao, '12' ); ?>><?php esc_html_e( '12 anos', 'cannal-espetaculos' ); ?></option>
				<option value="14" <?php selected( $classificacao, '14' ); ?>><?php esc_html_e( '14 anos', 'cannal-espetaculos' ); ?></option>
				<option value="16" <?php selected( $classificacao, '16' ); ?>><?php esc_html_e( '16 anos', 'cannal-espetaculos' ); ?></option>
				<option value="18" <?php selected( $classificacao, '18' ); ?>><?php esc_html_e( '18 anos', 'cannal-espetaculos' ); ?></option>
		</select></td>
	</tr>
	<tr>
		<th><label for="espetaculo_logotipo"><?php esc_html_e( 'Logotipo', 'cannal-espetaculos' ); ?></label></th>
		<td>
			<div id="espetaculo-logotipo-wrap">
                <?php if ( $logotipo_url ) : ?>
                <div id="espetaculo-logotipo-preview" style="margin-bottom: 10px;">
                    <img src="<?php echo esc_url( $logotipo_url ); ?>" alt="<?php esc_html_e( 'Logotipo do espetáculo', 'cannal-espetaculos' ); ?>" style="max-width: 200px; height: auto;" />
                </div>
                <?php else : ?>
                    <div id="espetaculo-logotipo-preview" class="hidden"></div>
                <?php endif; ?>
                <input type="hidden" id="espetaculo_logotipo" name="espetaculo_logotipo" value="<?php echo esc_attr( $logotipo_id ); ?>" />    			
    			<button type="button" class="button espetaculo-logotipo-upload"><?php echo $logotipo_url ? esc_html__( 'Alterar Logotipo', 'cannal-espetaculos' ) : esc_html__( 'Adicionar Logotipo', 'cannal-espetaculos' ); ?></button>
    			<button type="button" class="button espetaculo-logotipo-remove <?php echo $logotipo_id ? '' : 'hidden'; ?>"><?php esc_html_e( 'Remover Logotipo', 'cannal-espetaculos' ); ?></button>
			</div>
			<p class="description"><?php esc_html_e( 'Selecione o logotipo do espetáculo', 'cannal-espetaculos' ); ?></p>
		</td>
	</tr>
	<tr>
		<th><label for="espetaculo_icone_id"><?php esc_html_e( 'Ícone', 'cannal-espetaculos' ); ?></label></th>
		<td>
			<div id="espetaculo-icone-wrap">
                <?php if ( $icone_url ) : ?>
                    <div id="espetaculo-icone-preview">
    					<img src="<?php echo esc_url( $icone_url ); ?>" alt="<?php esc_attr_e( 'Ícone atual', 'cannal-espetaculos' ); ?>" />
    				</div>
                <?php else : ?>
                    <div id="espetaculo-icone-preview" class="hidden"></div>
                <?php endif; ?>
    
                <input type="hidden" id="espetaculo_icone_id" name="espetaculo_icone_id" value="<?php echo esc_attr( $icone_id ); ?>" />    			
    			<button type="button" id="btn-upload-icone" class="button"><?php echo $icone_url ? esc_html__( 'Alterar Ícone', 'cannal-espetaculos' ) : esc_html__( 'Adicionar Ícone', 'cannal-espetaculos' ); ?></button>
    			<button type="button" id="btn-remove-icone" class="button button-link-delete  <?php echo $icone_url ? '' : 'hidden'; ?>"><?php esc_html_e( 'Remover Ícone', 'cannal-espetaculos' ); ?></button>
        	</div>
			<p class="description">
            	<?php esc_html_e( 'Ícone da página do espetáculo (favicon). Obrigatoriamente quadrado, máximo 512×512px. Formatos aceitos: PNG, ICO, SVG.', 'cannal-espetaculos' ); ?>
        	</p>
			<p id="espetaculo-icone-error" class="cannal-field-error hidden"></p>
		</td>
	</tr>
	<tr>
		<th><label for="espetaculo_exibir_galeria"><?php esc_html_e( 'Exibir Galeria', 'cannal-espetaculos' ); ?></label></th>
		<td><label> <input type="checkbox" id="espetaculo_exibir_galeria" name="espetaculo_exibir_galeria" value="1" <?php checked( $exibir_galeria === '' || $exibir_galeria === '1', true ); ?> /> <?php esc_html_e( 'Exibir galeria de fotos ao final do conteúdo', 'cannal-espetaculos' ); ?></label></td>
	</tr>
</table>