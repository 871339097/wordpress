<?php
/**
 * SiteEditor Control: multi-check.
 *
 * @package     SiteEditor
 * @subpackage  Options
 * @since       1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SiteEditorMulticheckControl' ) ) {

	/**
	 * Multicheck control
	 */
	class SiteEditorMulticheckControl extends SiteEditorOptionsControl {

		/**
		 * The control type.
		 *
		 * @access public
		 * @var string
		 */
		public $type = 'multi-check';

		/**  
		 * Enqueue control related scripts/styles.
		 *
		 * @access public
		 */
		public function enqueue() {

		}

		/**
		 * Renders the control wrapper and calls $this->render_content() for the internals.
		 *
		 * @since 3.4.0
		 */
		protected function render_content() {

			$atts           = $this->input_attrs();

			$atts_string    = $atts["atts"];

			$classes        = "sed-module-element-control sed-element-control sed-bp-input sed-bp-checkbox-input sed-control-{$this->type} {$atts['class']}";

			$pkey			= $this->id;

			$sed_field_id   = 'sed_pb_' . $pkey;

            $value          = $this->value();

			if( ! class_exists( 'SiteEditorMultiCheckField' ) ){
				require_once dirname( dirname( __FILE__ ) ) . DS . 'fields' . DS . 'site-editor-multi-check-field.class.php';
			}

			$value = SiteEditorMultiCheckField::sanitize( $value );

			?>

            <label class=""><?php echo esc_html( $this->label );?></label>

			<?php if(!empty($this->description)){ ?> 
			    <span class="field_desc flt-help sedico sedico-question sedico-lg " title="<?php echo esc_attr( $this->description );?>"></span> 
			<?php } ?>

			<div class="sed-bp-form-checkboxes sed-checkboxes">

				<?php
					$i = 1;
					foreach( $this->choices as $key_val => $choice ){
					$checked = ( in_array( esc_attr( $key_val ) , $value) ) ? 'checked="checked"' : '';
				?>

					<div>

						<label for="<?php echo esc_attr( $sed_field_id ) . $i ;?>" class="sed-bp-form-checkbox">
							<input type="checkbox" class="<?php echo esc_attr( $classes ); ?>" value="<?php echo esc_attr( $key_val );?>" name="<?php echo esc_attr( $sed_field_id );?>[]" id="<?php echo esc_attr( $sed_field_id ) . $i ;?>" <?php echo $checked;?> <?php echo $atts_string;?> />
							<?php echo esc_html( $choice );?>
						</label>

					</div>

				<?php
						$i++;
					}
				?>
            </div>

			<?php
		}

		/**
		 * An Underscore (JS) template for this control's content (but not its container).
		 *
		 * Class variables for this control class are available in the `data` JS object;
		 *
		 * @see SiteEditorOptionsControl::print_template()
		 *
		 * @access protected
		 */
		protected function content_template() {

		}
	}
}

$this->register_control_type( 'multi-check' , 'SiteEditorMulticheckControl' );
