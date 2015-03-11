<?php

class FrmProCopy{
    var $table_name;

    function __construct(){
        global $wpmuBaseTablePrefix, $wpdb;
        $prefix = ($wpmuBaseTablePrefix) ? $wpmuBaseTablePrefix : $wpdb->base_prefix;
        $this->table_name = "{$prefix}frmpro_copies";
    }
    
    function create( $values ){
        global $wpdb, $blog_id, $frmpro_display;
        
        $exists = $wpdb->query("DESCRIBE {$this->table_name}");
        if(!$exists)
            $this->install(true);
        unset($exists);
            
        $new_values = array();
        $new_values['blog_id'] = $blog_id;
        $new_values['form_id'] = isset($values['form_id']) ? (int)$values['form_id']: null;
        $new_values['type'] = isset($values['type']) ? $values['type']: 'form'; //options here are: form, display
        if ($new_values['type'] == 'form'){
            $frm_form = new FrmForm();
            $form_copied = $frm_form->getOne($new_values['form_id']);
            $new_values['copy_key'] = $form_copied->form_key;
        }else{
            $form_copied = $frmpro_display->getOne($new_values['form_id']);
            $new_values['copy_key'] = $form_copied->post_name;
        }
        $new_values['created_at'] = current_time('mysql', 1);
        
        $exists = $this->getAll(array('blog_id' => $blog_id, 'form_id' => $new_values['form_id'], 'type' => $new_values['type']), '', ' LIMIT 1');
        if ( $exists ) {
            return false;
        }
        $query_results = $wpdb->insert( $this->table_name, $new_values );

        if($query_results)
            return $wpdb->insert_id;
        else
           return false;
    }
    
    function destroy( $id ){
      global $wpdb;
      return $wpdb->delete($this->table_name, array('id' => $id));
    }
    
    function getAll($where = '', $order_by = '', $limit = ''){
        global $wpdb;
        $query = "SELECT * FROM $this->table_name ". 
                FrmAppHelper::prepend_and_or_where(' WHERE ', $where) . $order_by . $limit;
        if ($limit == ' LIMIT 1')
            $results = $wpdb->get_row($query);
        else
            $results = $wpdb->get_results($query);
        return $results;
    }
    
    function install($force=false){
        $db_version = 1.2; // this is the version of the database we're moving to
        $old_db_version = get_site_option('frmpro_copies_db_version');

        global $wpdb, $blog_id;
        
        if (($db_version != $old_db_version) or $force){
            $force = true;
            
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            $charset_collate = '';
            if( $wpdb->has_cap( 'collation' ) ){
                if( !empty($wpdb->charset) )
                  $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
                if( !empty($wpdb->collate) )
                  $charset_collate .= " COLLATE $wpdb->collate";
            }

            /* Create/Upgrade Display Table */
            $sql = "CREATE TABLE {$this->table_name} (
                    id int(11) NOT NULL auto_increment,
                    type varchar(255) default NULL,
                    copy_key varchar(255) default NULL,
                    form_id int(11) default NULL,
                    blog_id int(11) default NULL,
                    created_at datetime NOT NULL,
                    PRIMARY KEY id (id),
                    KEY form_id (form_id),
                    KEY blog_id (blog_id)
            ) {$charset_collate};";

            dbDelta($sql);

            update_site_option('frmpro_copies_db_version', $db_version);
        }

        //copy forms
        if(!$force){ //don't check on every page load
            $last_checked = get_option('frmpro_copies_checked');

            if(!$last_checked or ((time() - $last_checked) >= (60*60))) //check every hour
                $force = true;
        }
        
        if($force){        
            //get all forms to be copied from global table
            $templates = $wpdb->get_results($wpdb->prepare(
                "SELECT c.*, p.post_name FROM $this->table_name c LEFT JOIN {$wpdb->prefix}frm_forms f ON (c.copy_key = f.form_key) LEFT JOIN $wpdb->posts p ON (c.copy_key = p.post_name) WHERE blog_id != %d AND ((type = %s AND f.form_key is NULL) OR (type = %s AND p.post_name is NULL)) ORDER BY type DESC",
                $blog_id, 'form', 'display'
            ));
            
            foreach ($templates as $temp){
                if ($temp->type == 'form'){
                    $frm_form = new FrmForm();
                    $frm_form->duplicate($temp->form_id, false, true, $temp->blog_id);
                }else{
                    global $frmpro_display;
                    $values = $frmpro_display->getOne( $temp->form_id, $temp->blog_id, true );
                    if ( !$values || 'trash' == $values->post_status ) {
                        continue;
                    }
                    
                    // check if post with slug already exists
                    $post_name = wp_unique_post_slug( $values->post_name, 0, 'publish', 'frm_display', 0 );
                    if ( $post_name != $values->post_name ) {
                        continue;
                    }
                    
                    if ( $values->post_name != $temp->copy_key ) {
                        $wpdb->update($this->table_name, array('copy_key' => $values->post_name), array('id' => $temp->id) );
                    }
                    
                    $frmpro_display->duplicate($temp->form_id, true, $temp->blog_id);

                    //TODO: replace any ids with field keys in the display before duplicated
                }
                unset($temp);
            }
                
            update_option('frmpro_copies_checked', time());
        }
    }
    
    function uninstall(){
        if ( !current_user_can('administrator') ) {
            global $frm_settings;
            wp_die($frm_settings->admin_permission);
        }
        
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS {$this->table_name}");
        delete_option('frmpro_copies_db_version');
        delete_option('frmpro_copies_checked');
    }
}        
