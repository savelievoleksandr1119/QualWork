<?php
/**
 * Emails\
 *
 * @class       Vibe_Projects_Mails
 * @author      VibeThemes
 * @category    Admin
 * @package     VibeBp
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function vibe_projects_add_notification($args =''){
    if ( ! bp_is_active( 'notifications' ) || !function_exists('bp_notifications_add_notification')) 
        return;
    global $bp;
    $defaults = array(
        'user_id' => $bp->loggedin_user->id,
        'item_id' => false,
        'secondary_item_id' => false,
        'component_name' => VIBE_PROJECTS_SLUG,
        'component_action'  => '',
        'date_notified'     => bp_core_current_time(),
        'is_new'            => 1,
    );

    $r = wp_parse_args( $args, $defaults );
    extract( $r );

    return  bp_notifications_add_notification( array(
        'user_id'           => $user_id,
        'item_id'           => $item_id,
        'secondary_item_id' => $secondary_item_id,
        'component_name'    => $component_name,
        'component_action'  => $component_action,
        'date_notified'     => $date_notified,
        'is_new'            => $is_new,
    ) );
    
}

function vibe_projects_add_notification_meta($args=''){
    if ( !function_exists( 'bp_activity_update_meta' ) )
        return false;

    $defaults = array(
        'id' => false,
        'meta_key' => '',
        'meta_value' => ''
    );

    $r = wp_parse_args( $args, $defaults );
    extract( $r );
}

function bp_projects_format_notifications( $action, $item_id, $secondary_item_id, $total_items, $format = 'string' ) {
    $touchpoint = Vibe_Projects_TouchPoints::init();
    $notification= '';
    switch ($action) {

        case 'vibe_projects_card_status_updated':
            $notification = sprintf(__('Card %s status updated in project %s','vibe-projects'),get_the_title($secondary_item_id),get_the_title($item_id));
        break;
        case 'vibe_projects_create_new_card':
            $notification = sprintf(__('New card %s created in project %s','vibe-projects'),get_the_title($secondary_item_id),get_the_title($item_id));
        break;
        case 'vibe_projects_add_member_to_card':
            $notification = sprintf(__('Card %s assigned to you under project %s','vibe-projects'),get_the_title($secondary_item_id),get_the_title($item_id));
        break;
        case 'vibe_projects_remove_member_from_card':
            $notification = sprintf(__('Card %s un-assigned from you under project %s','vibe-projects'),get_the_title($secondary_item_id),get_the_title($item_id));
        break;
        case 'vibe_projects_create_new_project':
            $notification = sprintf(__('Project %s updated.','vibe-projects'),get_the_title($item_id));
        break;
        case 'vibe_projects_member_added':
            $notification = sprintf(__('You are added to the project %s updated.','vibe-projects'),get_the_title($item_id));
        break;
        case 'vibe_projects_member_removed':
            $notification = sprintf(__('You are removed from the project %s updated.','vibe-projects'),get_the_title($item_id));
        break;
        case 'vibe_projects_notice_added':
             $notification = sprintf(__('New notification in project %s ','vibe-projects'),get_the_title($item_id));
        break;
        case 'vibe_projects_card_label_added':
            $notification = sprintf(__('Card %s label added by %s','vibe-projects'),get_the_title($item_id),bp_core_get_user_displayname($user_id));
        break;
        case 'vibe_projects_card_label_removed':
            $notification = sprintf(__('Card %s label removed by %s','vibe-projects'),$touchpoint->get_card_title_link($item_id),bp_core_get_userlink($secondary_item_id));
        break;
        case 'vibe_projects_update_checklist':
            $notification = sprintf(__('Card %s checklist updated by %s','vibe-projects'),$touchpoint->get_card_title_link($item_id),bp_core_get_userlink($secondary_item_id));
        break;
        case 'vibe_projects_card_duedate_set':
            $notification = sprintf(__('Card %s due date set by %s','vibe-projects'),$touchpoint->get_card_title_link($item_id),bp_core_get_userlink($secondary_item_id));
        break;
        case 'vibe_projects_upload_attachment':
            $notification = sprintf(__('Attachment uploaded in card %s by %s','vibe-projects'),$touchpoint->get_card_title_link($item_id),bp_core_get_userlink($secondary_item_id));
        break;
        case 'vibe_projects_card_completed':
            $notification = sprintf(__('Card %s marked complete by %s','vibe-projects'),$touchpoint->get_card_title_link($item_id),bp_core_get_userlink($secondary_item_id));
        break;
        case 'vibe_projects_card_milestoned':
            $notification = sprintf(__('Card %s marked as milestone by %s','vibe-projects'),$touchpoint->get_card_title_link($item_id),bp_core_get_userlink($secondary_item_id));
        break;
        case 'vibe_projects_card_archived':
            $notification = sprintf(__('Card %s archived by %s','vibe-projects'),$touchpoint->get_card_title_link($item_id),bp_core_get_userlink($secondary_item_id));
        break;
        case 'vibe_projects_add_card_comment':
            $notification = sprintf(__('Comment added in card %s by %s','vibe-projects'),$touchpoint->get_card_title_link($item_id),bp_core_get_userlink($secondary_item_id));
        break;
    }
    return $notification;
}

