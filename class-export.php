<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
class WPCRM_System_Export_Campaigns extends WPCRM_System_Export{
	/**
	 * Our export type. Used for export-type specific filters/actions
	 * @var string
	 * @since 2.1
	 */
	public $export_type = 'wpcrm-campaign';

	/**
	 * Set the CSV columns
	 *
	 * @access public
	 * @since 2.1
	 * @return array $cols All the columns
	 */
	public function csv_cols() {

		$cols = array(
			'campaign_name' 	=> __( 'Campaign Name', 'wp-crm-system-import-campaigns' ),
			'assigned' 			=> __( 'Assigned to', 'wp-crm-system-import-campaigns' ),
			'active'			=> __( 'Active', 'wp-crm-system-import-campaigns' ),
			'status'			=> __( 'Status', 'wp-crm-system-import-campaigns' ),
			'start_date'		=> __( 'Start Date', 'wp-crm-system-import-campaigns' ),
			'end_date'			=> __( 'End Date', 'wp-crm-system-import-campaigns' ),
			'projected_reach'	=> __( 'Projected Reach', 'wp-crm-system-import-campaigns' ),
			'responses'			=> __( 'Responses', 'wp-crm-system-import-campaigns' ),
			'budget_cost'		=> __( 'Budgeted Cost', 'wp-crm-system-import-campaigns' ),
			'actual_cost'		=> __( 'Actual Cost', 'wp-crm-system-import-campaigns' ),
			'description'		=> __( 'Description', 'wp-crm-system-import-campaigns' )
		);

		if( defined( 'WPCRM_CUSTOM_FIELDS' ) ){
			$field_count = get_option( '_wpcrm_system_custom_field_count' );
			if( $field_count ){
				$custom_fields = array();
				for( $field = 1; $field <= $field_count; $field++ ){
					// Make sure we want this field to be imported.
					$field_scope = get_option( '_wpcrm_custom_field_scope_' . $field );
					$can_export = $field_scope == $this->export_type ? true : false;
					if( $can_export ){
						$custom_fields[] = get_option( '_wpcrm_custom_field_name_' . $field );
					}
				}
				$cols = array_merge( $cols, $custom_fields );
			}
		}

		$cols = apply_filters( 'wpcrm_system_export_cols_' . $this->export_type, $cols );

		return $cols;
	}

	/**
	 * Get the Export Data
	 *
	 * @access public
	 * @since 2.1
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {
		$get_ids = $this->get_cpt_post_ids();
		foreach ( $get_ids as $id ){
			$data[$id] = array(
				'campaign_name' 	=> get_the_title( $id ),
				'assigned' 			=> get_post_meta( $id, '_wpcrm_campaign-assigned', true ),
				'active'			=> get_post_meta( $id, '_wpcrm_campaign-active', true ),
				'status'			=> get_post_meta( $id, '_wpcrm_campaign-status', true ),
				'start_date'		=> date( get_option( 'wpcrm_system_php_date_format' ), get_post_meta( $id, '_wpcrm_campaign-startdate', true ) ),
				'end_date'			=> date( get_option( 'wpcrm_system_php_date_format' ), get_post_meta( $id, '_wpcrm_campaign-enddate', true ) ),
				'projected_reach'	=> get_post_meta( $id, '_wpcrm_campaign-projectedreach', true ),
				'responses'			=> get_post_meta( $id, '_wpcrm_campaign-responses', true ),
				'budget_cost'		=> get_post_meta( $id, '_wpcrm_campaign-budgetcost', true ),
				'actual_cost'		=> get_post_meta( $id, '_wpcrm_campaign-actualcost', true ),
				'description'		=> esc_html( get_post_meta( $id, '_wpcrm_campaign-description', true ) ),
			);
			if( defined( 'WPCRM_CUSTOM_FIELDS' ) ){
				$field_count 	= get_option( '_wpcrm_system_custom_field_count' );
				if( $field_count ){
					for( $field = 1; $field <= $field_count; $field++ ){
						// Make sure we want this field to be imported.
						$field_scope 	= get_option( '_wpcrm_custom_field_scope_' . $field );
						$field_type		= get_option( '_wpcrm_custom_field_type_' . $field );
						$can_export 	= $field_scope == $this->export_type ? true : false;
						if( $can_export ){
							$value 	= get_post_meta( $id, '_wpcrm_custom_field_id_' . $field, true );
							switch ( $field_type ) {
								case 'datepicker':
									$export = date( get_option( 'wpcrm_system_php_date_format' ), $value );
									break;
								case 'repeater-date':
									if ( is_array( $value ) ){
										foreach ( $value as $key => $v ){
											$values[$key] = date( get_option( 'wpcrm_system_php_date_format' ), $v );
										}
										$export = implode( ',', $values );
									} else {
										$export = '';
									}
									break;
								case 'repeater-file':
								case 'repeater-text':
								case 'repeater-textarea':
									if ( is_array( $value ) ){
										$export = implode( ',', $value );
									} else {
										$export = '';
									}
									break;
								default:
									$export = $value;
									break;
							}
							$data[$id][] = $export;
						}
					}
				}
			}
		}


		$data = apply_filters( 'wpcrm_system_export_get_data', $data );
		$data = apply_filters( 'wpcrm_system_export_get_data_' . $this->export_type, $data );

		return $data;
	}

}