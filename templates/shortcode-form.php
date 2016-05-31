<?php
/**
 * This template is used to display the form for submitting a ticket [kbs_form]
 */
global $kbs_form;
?>

<form id="kbs_ticket_form" class="kbs_form" action="" method="post">
	<?php do_action( 'kbs_ticket_form_top' ); ?>

	<fieldset>
		<legend><?php esc_attr_e( get_the_title( $kbs_form->ID ) ); ?></legend>

		<?php do_action( 'kbs_ticket_form_before' ); ?>

		<?php foreach( $kbs_form->fields as $field ) : ?>
        
        	<?php $settings = $kbs_form->get_field_settings( $field->ID ); ?>
            
            	<p>
                	<?php if ( empty( $settings['hide_label'] ) && 'recaptcha' != $settings['type'] ) : ?>
                        <label for="<?php echo $field->post_name; ?>"><?php esc_attr_e( get_the_title( $field->ID ) ); ?></label>
                    <?php endif; ?>

                    <?php $kbs_form->display_field( $field, $settings ); ?>
                </p>
        
        <?php endforeach; ?>

		<?php do_action( 'kbs_ticket_form_before_submit' ); ?>

		<p>
			<input type="hidden" name="kbs_honeypot" value="" />
			<input type="hidden" name="kbs_action" value="submit_ticket" />
			<input class="button" name="kbs_ticket_submit" type="submit" value="<?php printf( esc_attr__( 'Submit %s', 'kb-support' ), kbs_get_ticket_label_singular() ); ?>" />
		</p>

		<?php do_action( 'kbs_ticket_form_after' ); ?>
	</fieldset>

	<?php do_action( 'kbs_ticket_form_bottom' ); ?>
</form>