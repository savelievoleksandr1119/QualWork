<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if(!class_exists('Vibe_Zoom_Meetings_tab_Mods'))
{
    if ( bp_is_active( 'groups' ) ) :
        class Vibe_Zoom_Meetings_tab_Mods extends BP_Group_Extension {
            /**
             * Your __construct() method will contain configuration options for 
             * your extension, and will pass them to parent::init()
             */
            function __construct() {
                $args = array(
                    'slug' => apply_filters('Vibe_Zoom_meetings_slug','zoom-meetings'),
                    'name' =>  __('Zoom Meetings','vibe-zoom'),
                    'access' => apply_filters('Vibe_Zoom_meetings_authority','member'),
                );
                parent::init( $args );
            }
         
            /**
             * display() contains the markup that will be displayed on the main 
             * plugin tab
             */
            function display( $group_id = NULL ) {
                $group_id = bp_get_group_id();
                $zoom = Vibe_Zoom::init();
                $user_id = get_current_user_id();
                echo '<table id="user-tours" class="table table-hover">';
                echo '<thead><tr><th>'._x('Meeting name','','vibe-zoom').'</th><th>'._x('Status','','vibe').'</th><th>'._x('Action','','vibe').'</th></tr></thead><tbody>';
                if(!empty($zoom->Vibe_Zoom_meetings)){
                    foreach ($zoom->Vibe_Zoom_meetings as $meetng_id => $meeting) {
                        $scope = $meeting['restrictions']['scope'];
                        $flag = 0;
                        $users = $bbb->users_from_restriction($meeting,1);
                        
                        if(in_array($user_id,$users) && in_array($group_id ,$meeting['restrictions']['data'])){
                            
                            $status = _x('NA','','vibe-zoom');
                            if(!empty($meeting['meeting_details']) && !empty($meeting['meeting_details']['start_time'])){
                                $start_time = strtotime($meeting['meeting_details']['start_time']);

                                $_start_time = strtotime($meeting['start_date'].' '.$meeting['start_time']);

                                $_expiry_time = $_start_time + ($meeting['duration']['duration']* $meeting['duration']['parameter']);                            $expiry_time = $start_time + ($meeting['duration']['duration']* $meeting['duration']['parameter']);
                                
                                $format = get_option( 'date_format' ).' '.get_option('time_format');
                                $readable_time_start = date_i18n($format ,$_start_time);
                                $readable_time_expire =date_i18n($format , $_expiry_time); 
                                if(time() >= $start_time &&  time() <= $expiry_time ){
                                    $status = _x('Ongoing','','vibe-zoom');
                                }elseif(time() <= $start_time){
                                
                                    $status = sprintf(_x('To be started on %s (%s)','','vibe-zoom'), $readable_time_start ,$meeting['timezone']);
                                }elseif(time() >= $expiry_time){
                                    $status =sprintf( _x('Meeting over on %s (%s)','','vibe-zoom'),$readable_time_expire,$meeting['timezone'] );
                                }
                            }
                        
                        
                            echo '<tr><td>'.$meeting['name'].'</td>';
                            echo '<td>'.$status.'</td>';
                            echo '<td>'.do_shortcode('[vibe_zoom token="'.$meeting['id'].'" popup="1" size="1"]').'</td>';
                            echo '<tr>';
                        }
                    }
                }
                echo '</tbody></table>';
            }
         
           
        }
        $for_mods = apply_filters('Vibe_Zoom_meetings_authority_for_mods',true);
        if($for_mods){
            bp_register_group_extension( 'Vibe_Zoom_Meetings_tab_Mods' );
        }
    
    endif; 
}

