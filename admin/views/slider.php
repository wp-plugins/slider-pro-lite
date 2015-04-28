<div class="wrap sliderpro-admin">
	<h2><?php echo isset( $_GET['action'] ) && $_GET['action'] === 'edit' ? __( 'Edit Slider', 'sliderpro-lite' ) : __( 'Add New Slider', 'sliderpro-lite' ); ?></h2>

	<form action="" method="post">
    	<div class="metabox-holder has-right-sidebar">
            <div class="editor-wrapper">
                <div class="editor-body">
                    <div id="titlediv">
                    	<input name="name" id="title" type="text" value="<?php echo esc_attr( $slider_name ); ?>" />
                    </div>
					
					<div class="slides-container">
                    	<?php
                    		if ( isset( $slides ) ) {
                    			if ( $slides !== false ) {
                    				foreach ( $slides as $slide ) {
                    					$this->create_slide( $slide );
                    				}
                    			}
                    		} else {
                    			$this->create_slide( false );
                    		}
	                    ?>
                    </div>

                    <a class="button add-slide" href="#"><?php _e( 'Add Slides', 'sliderpro-lite' ); ?></a>
                </div>
            </div>

            <div class="inner-sidebar meta-box-sortables ui-sortable">
				<div class="postbox action">
					<div class="inside">
						<input type="submit" name="submit" class="button-primary" value="<?php echo isset( $_GET['action'] ) && $_GET['action'] === 'edit' ? __( 'Update', 'sliderpro-lite' ) : __( 'Create', 'sliderpro-lite' ); ?>" />
                        <span class="spinner update-spinner"></span>
						<a class="button preview-slider" href="#"><?php _e( 'Preview', 'sliderpro-lite' ); ?></a>
                        <span class="spinner preview-spinner"></span>
					</div>
				</div>
                
                <div class="sidebar-settings">
                    <?php
                        $setting_groups = BQW_SliderPro_Lite_Settings::getSettingGroups();
                        $panels_state = BQW_SliderPro_Lite_Settings::getPanelsState();

                        foreach ( $setting_groups as $group_name => $group ) {
                            $panel_state_class = isset( $slider_panels_state ) && isset( $slider_panels_state[ $group_name ] ) ? $slider_panels_state[ $group_name ] : $panels_state[ $group_name ];
                            $panel_name_class = $group_name . '-panel';
                            ?>
                            <div class="postbox <?php echo $panel_name_class . ' ' . $panel_state_class; ?>" data-name="<?php echo $group_name; ?>">
                                <div class="handlediv"></div>
                                <h3 class="hndle"><?php echo $group['label']; ?></h3>
                                <div class="inside">
                                    <table>
                                        <tbody>
                                            <?php
                                                foreach ( $group['list'] as $setting_name ) {
                                                    $setting = BQW_SliderPro_Lite_Settings::getSettings( $setting_name );
                                            ?>
                                                    <tr>
                                                        <td>
                                                            <label data-info="<?php echo $setting['description']; ?>" for="<?php echo $setting_name; ?>"><?php echo $setting['label']; ?></label>
                                                        </td>
                                                        <td>
                                                            <?php
                                                                $value = isset( $slider_settings ) && isset( $slider_settings[ $setting_name ] ) ? $slider_settings[ $setting_name ] : $setting['default_value'];

                                                                if ( $setting['type'] === 'number' || $setting['type'] === 'text' || $setting['type'] === 'mixed' ) {
                                                                    echo '<input id="' . $setting_name . '" class="setting" type="text" name="' . $setting_name . '" value="' . esc_attr( $value ) . '" />';
                                                                } else if ( $setting['type'] === 'boolean' ) {
                                                                    echo '<input id="' . $setting_name . '" class="setting" type="checkbox" name="' . $setting_name . '"' . ( $value === true ? ' checked="checked"' : '' ) . ' />';
                                                                } else if ( $setting['type'] === 'select' ) {
                                                                    echo'<select id="' . $setting_name . '" class="setting" name="' . $setting_name . '">';
                                                                    
                                                                    foreach ( $setting['available_values'] as $value_name => $value_label ) {
                                                                        echo '<option value="' . $value_name . '"' . ( $value === $value_name ? ' selected="selected"' : '' ) . '>' . $value_label . '</option>';
                                                                    }
                                                                    
                                                                    echo '</select>';
                                                                }
                                                            ?>
                                                        </td>
                                                    </tr>
                                            <?php
                                                }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <?php
                        }
                    ?>
                </div>
            </div>
        </div>
	</form>
</div>