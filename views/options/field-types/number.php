<?php
/**
 * Number input for settings.
 *
 * @package Tutor LMS
 * @since 2.0
 */

$value = $this->get( $field['key'] );
if ( isset( $field['default'] ) && trim( $value ) === '' ) {
	$value = $field['default'];
}

$field_key	= isset( $field['key'] ) ? esc_attr( $field['key'] ) : null;
$field_id	= esc_attr( 'field_' . $field_key );
$min		= isset( $field['min'] ) ? esc_attr( $field['min'] ) : 0;

?>
<div class="tutor-option-field-row" id="<?php echo esc_attr( $field_id ); ?>">
<?php require tutor()->path . 'views/options/template/common/field_heading.php'; ?>

	<div class="tutor-option-field-input">
		<input class="tutor-form-control" type="number" name="tutor_option[<?php echo esc_attr( $field_key ); ?>]" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo $min; ?>" min="<?php echo $min; ?>">
	</div>
</div>