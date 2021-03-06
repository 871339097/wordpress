<?php
/**
 * SiteEditor Field: border-style.
 *
 * @package     SiteEditor
 * @subpackage  Options
 * @since       1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) )  {
    exit;
}

if ( ! class_exists( 'SiteEditorBorderStyleField' ) ) {

    
    /**
     * Field overrides.
     */
    class SiteEditorBorderStyleField extends SiteEditorField {

        /**
         * Related setting id for save in db
         *
         * @access protected
         * @var string
         */
        public $setting_id = '';

        /**
         * The border side
         *
         * @access public
         * @var string
         */
        public $prop_side = '';

        /**
         * The field type.
         *
         * @access protected
         * @var string
         */
        public $type = 'border-style';

        /**
         * Use 'refresh', 'postMessage'
         *
         * @access protected
         * @var string
         */
        public $transport = 'postMessage';

        /**
         * Sets the Default Value
         *
         * @access protected
         */
        protected function set_default() {

            // If a custom default has been defined,
            // then we don't need to proceed any further.
            if ( ! empty( $this->default ) ) {
                return;
            }

            $this->default = 'none';

        }

        /**
         * Sets the setting id
         *
         * @access protected
         */
        protected function set_setting_id() {

            if ( ! empty( $this->prop_side ) ) {
                $this->setting_id = "border_{$this->prop_side}_style";
            }

        }

    }
}

sed_options()->register_field_type( 'border-style' , 'SiteEditorBorderStyleField' );
