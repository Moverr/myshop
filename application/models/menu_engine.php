<?php
#Used to format menu displays
class Menu_engine extends CI_Model{

    var $menu_level = 0;
    
    #Constructor
    function Menu_engine()
    {
        parent::__construct();
        $this->load->database();
    }

    function menu_crumbs($current = '')
    {
        $crumb_str = '';
        $menu_bucket = $this->menu_bucket();

        if (!empty($current)) {
            $menu_parent = $this->find_my_parent($current);
            #exit($menu_parent);
            if (!empty($menu_parent)) {
                $crumb_str = '<li><a href="' . $menu_bucket[$menu_parent]['attr']['url'] . '">' . $menu_bucket[$menu_parent]['attr']['text'] . '</a>' .
                    '<span class="divider">&nbsp;</span>' .
                    '</li>' .
                    '<li><a href="' . $menu_bucket[$menu_parent]['child'][$current]['url'] . '">' . $menu_bucket[$menu_parent]['child'][$current]['text'] . '</a>' .
                    '<span class="divider-last">&nbsp;</span>' .
                    '</li>';
            } elseif (!empty($menu_bucket[$current])) {
                $crumb_str = '<li><a href="' . $menu_bucket[$current]['attr']['url'] . '">' . $menu_bucket[$current]['attr']['text'] . '</a>' .
                    '<span class="divider-last">&nbsp;</span>' .
                    '</li>';
            }
        }

        return $crumb_str;
    }
    
    function menu_bucket ()
    {
        #Dashboard
        $menu_array['dashboard']['attr'] = array('text'=>'Dashboard', 'url'=>base_url() . 'user/dashboard', 'id'=>'menu-dashboard', 'classes'=>'icon-dashboard');
        #Settings
        $menu_array['userinformation']['child']['home'] = array('text' => 'My Dashboard', 'url' => base_url() . 'user/dashboard', 'id' => 'menu-add-user', 'classes' => '');
        $menu_array['userinformation']['attr'] = array('text' => 'My Information', 'url' => 'javascript:void(0);', 'id' => 'user-information', 'classes' => 'fa fa-cog');
        $menu_array['userinformation']['child']['my_profile'] = array('text' => 'My Profile', 'url' => base_url() . 'user/profile_form', 'id' => 'my_profile', 'classes' => '');
        $menu_array['userinformation']['child']['log_out'] = array('text' => 'Log Out', 'url' => base_url() . 'admin/logout', 'id' => 'menu-add-user', 'classes' => '');
        $menu_array['userinformation']['child']['audti_trail'] = array('text' => 'Audit Trail', 'url' => base_url() . 'admin/audit_trail', 'id' => 'audit_trail', 'classes' => '');

 

 
         
        #User links
        $menu_array['users']['attr'] = array('text' => 'Users', 'url' => 'javascript:void(0);', 'id' => 'menu-users', 'classes' => 'fa fa-user');
        $menu_array['users']['child']['view_user_list'] = array('text' => 'Manage Users', 'url' => base_url() . 'admin/manage_users', 'id' => 'menu-manage-users', 'classes' => '');
        $menu_array['users']['child']['add_users'] = array('text' => 'Add User', 'url' => base_url() . 'user/load_user_form', 'id' => 'menu-add-user', 'classes' => '');
        $menu_array['users']['child']['view_user_groups'] = array('text' => 'User Groups', 'url' => base_url() . 'admin/manage_user_groups', 'id' => 'menu-user-groups', 'classes' => '');
        $menu_array['users']['child']['add_user_group'] = array('text' => 'Add User Group', 'url' => base_url() . 'admin/user_group_form', 'id' => 'menu-add-user-group', 'classes' => '');

        #Forms links
        $menu_array['forms']['attr'] = array('text' => 'Forms', 'url' => 'javascript:void(0);', 'id' => 'ppda-forms', 'classes' => 'fa fa-file-text');
        $menu_array['forms']['child']['download-forms'] = array('text' => 'Download Forms', 'url' => 'http://ppda.go.ug/ppda-forms/', 'id' => 'menu-manage-users', 'classes' => '','target'=>'_blank');
       # $menu_array['forms']['child']['add_users'] = array('text' => 'Form 48', 'url' => '#', 'id' => 'menu-add-user', 'classes' => '');



        #Shop Management
        $menu_array['shops']['attr'] = array('text' => ' SHOPS', 'url' => 'javascript:void(0);', 'id' => 'menu-pdes', 'classes' => 'fa fa-building  ');

         $menu_array['shops']['child']['create_shop'] = array('text' => 'Add Shop', 'url' => base_url() . 'shops/add', 'id' => 'menu-add-pde', 'classes' => '');
         

        $menu_array['shops']['child']['view_shops'] = array('text' => 'Manage Shops', 'url' => base_url() . 'shops/lists', 'id' => 'menu-manage-pdes', 'classes' => '');


        #SHOP Branches
          $menu_array['shops']['child']['add_shop_branch'] = array('text' => 'Add   Branch', 'url' => base_url() . 'branches/add', 'id' => 'menu-add-pdetype', 'classes' => '');
          
        $menu_array['shops']['child']['manage_shop_branches'] = array('text' => 'Manage   Branches', 'url' => base_url() . 'branches/lists', 'id' => 'menu-manage-types', 'classes' => '');        
      




        #Item Management
        $menu_array['items']['attr'] = array('text' => ' ITEMS', 'url' => 'javascript:void(0);', 'id' => 'menu-pdes', 'classes' => 'fa fa-building  ');

        $menu_array['items']['child']['create_itemcategory'] = array('text' => 'Add Item Category', 'url' => base_url() . 'items/addcategory', 'id' => 'menu-add-itemcategory', 'classes' => '');

        $menu_array['items']['child']['manage_itemcategories'] = array('text' => 'Manage Item Categories', 'url' => base_url() . 'items/listcategories', 'id' => 'menu-add-manageitemcategory', 'classes' => '');

        $menu_array['items']['child']['create_items'] = array('text' => 'Add Items', 'url' => base_url() . 'items/additem', 'id' => 'menu-add-items', 'classes' => '');

        $menu_array['items']['child']['manage_items'] = array('text' => 'Manage Items', 'url' => base_url() . 'items/listitems', 'id' => 'menu-add-listitems', 'classes' => '');




        #Stock Management
        $menu_array['stock']['attr'] = array('text' => ' STOCK', 'url' => 'javascript:void(0);', 'id' => 'menu-pdes', 'classes' => 'fa fa-book  ');

        $menu_array['stock']['child']['add_stock'] = array('text' => 'Add New Stock ', 'url' => base_url() . 'stock/add_stock', 'id' => 'menu-add-itemcategory', 'classes' => '');

        $menu_array['stock']['child']['manage_stock'] = array('text' => 'Manage Stock ', 'url' => base_url() . 'stock/manage_stock', 'id' => 'menu-add-manageitemcategory', 'classes' => '');




        #Stock Management
        $menu_array['pos']['attr'] = array('text' => ' Service Desk', 'url' => 'javascript:void(0);', 'id' => 'menu-pdes', 'classes' => 'fa fa-book  ');

        $menu_array['pos']['child']['add_sale'] = array('text' => 'New Sales Order ', 'url' => base_url() . 'pos/new_sale', 'id' => 'new_sale', 'classes' => '');

        $menu_array['pos']['child']['manage_sales'] = array('text' => 'Manage Sales Orders ', 'url' => base_url() . 'pos/manage_sales', 'id' => 'manage_sales', 'classes' => '');
 
 

 
 

 


        #PDE links
        $menu_array['pdes']['attr'] = array('text' => 'PDEs', 'url' => 'javascript:void(0);', 'id' => 'menu-pdes', 'classes' => ' fa fa-university');
        $menu_array['pdes']['child']['view_pdes'] = array('text' => 'Manage PDEs', 'url' => base_url() . 'admin/manage_pdes', 'id' => 'menu-manage-pdes', 'classes' => '');
        $menu_array['pdes']['child']['create_pde'] = array('text' => 'Add PDE', 'url' => base_url() . 'admin/load_pde_form', 'id' => 'menu-add-pde', 'classes' => '');

        #PDE TYPES
        $menu_array['pdes']['child']['manage_pdetypes'] = array('text' => 'Manage PDE Types', 'url' => base_url() . 'admin/manage_pdetypes', 'id' => 'menu-manage-types', 'classes' => '');
        $menu_array['pdes']['child']['add_pdetype'] = array('text' => 'Add PDE Type', 'url' => base_url() . 'admin/load_pdetype_form', 'id' => 'menu-add-pdetype', 'classes' => '');

        #PDE Branches
        $menu_array['pdes']['child']['manage_pde_branches'] = array('text' => 'Manage   Branches', 'url' => base_url() . 'branches/lists', 'id' => 'menu-manage-types', 'classes' => '');
        $menu_array['pdes']['child']['add_pde_branch'] = array('text' => 'Add   Branch', 'url' => base_url() . 'branches/add', 'id' => 'menu-add-pdetype', 'classes' => '');

        
        #Providers Links
        $menu_array['providers']['attr'] = array('text' => 'Providers ', 'url' => 'javascript:void(0);', 'id' => 'menu-providers', 'classes' => ' fa fa-briefcase');
        $menu_array['providers']['child']['suspend_provider'] = array('text' => 'Suspend Provider', 'url' => base_url() . 'providers/suspend_provider', 'id' => 'menu-unconfirmed-providers', 'classes' => '');
        $menu_array['providers']['child']['manage_suspended_providers'] = array('text' => ' Suspended Providers', 'url' => base_url() . 'providers/manage_suspended_providers', 'id' => 'menu-unconfirmed-providers', 'classes' => '');
        
        $menu_array['providers']['child']['internationally_suspended'] = array('text' => 'Internationally Suspended Providers', 'url' =>  'http://web.worldbank.org/external/default/main?theSitePK=84266&contentMDK=64069844&menuPK=116730&pagePK=64148989&piPK=64148984', 'id' => 'menu-unconfirmed-providers', 'classes' => '','target'=>'_blank');

        $menu_array['providers']['child']['provider_shortlist'] = array('text' => 'Add Provider to Short List', 'url' =>  base_url().'providers/add_shortlist', 'id' => 'add_shortlist', 'classes' => '');
        $menu_array['providers']['child']['manage_shortlist'] = array('text' => 'Manage Short List', 'url' =>  base_url().'providers/manage_shortlist' , 'id' => 'manage_shortlist', 'classes' => '');
       
    
    #Public holidays
    $menu_array['publicholidays']['attr'] = array('text' => 'Public holidays ', 'url' => 'javascript:void(0);', 'id' => 'menu-help', 'classes' => 'fa fa-question-circle');
        $menu_array['publicholidays']['child']['view_public_holidays'] = array('text' => 'Manage public holidays', 'url' => base_url() . 'public_holiday/lists', 'id' => 'menu-manage-types', 'classes' => '');
        $menu_array['publicholidays']['child']['add_public_holiday'] = array('text' => 'Add public holiday', 'url' => base_url() . 'public_holiday/load_form', 'id' => 'menu-add-pdetype', 'classes' => '');


        #Help links
        $menu_array['help']['attr'] = array('text' => 'Help ', 'url' => 'javascript:void(0);', 'id' => 'menu-help', 'classes' => 'fa fa-question-circle');
        $menu_array['help']['child']['help-link'] = array('text' => 'Help Information', 'url' => base_url().'faqs/help', 'id' => 'help-link', 'classes' => '');
    $menu_array['help']['child']['list-help-link'] = array('text' => 'All Help Information', 'url' => base_url().'faqs/list_all_help', 'id' => 'list-help-link', 'classes' => '');
    $menu_array['help']['child']['add-help-link'] = array('text' => 'New Help Information', 'url' => base_url().'faqs/add_help', 'id' => 'add-help-link', 'classes' => '');


        return $menu_array;
    }

    function find_my_parent($menu_item = '')
    {
        $parent_index = '';
        $menu_items = $this->menu_bucket();

        foreach ($menu_items as $item_key => $item)
        {
            if ($item_key == $menu_item)
            {
                return '';
            } else
            {
                if (is_array($item) && !empty($item['child'])) {
                    foreach ($item['child'] as $child_key => $child) {
                        if ($child_key == $menu_item) {
                            return $item_key;
                        }
                    }
                }
            }
        }

        return $parent_index;

    }


    #Find menu parent

    function display_menu($current_menu = '')
    {
        $nav_menu_str = '';

        $menu_array = $this->menu_bucket();

        if(check_user_access($this, 'manage_reports'));

        if(!empty($menu_array))
        {
            echo '<ul class="sidebar-menu">';

            foreach ($menu_array AS $text => $link) {
                $parent_links_html = '<li class="' . (!empty($link['child']) ? ' has-sub' : '') .
                                    (($text == $current_menu || $text == $this->find_my_parent($current_menu))? ' active open' : '') .'">'.
                                    '<a  style="vertical-align:top; width:200px; " href="'. $link['attr']['url'] . '"';
                                    
                                    $parent_links_html .= $link['attr']['id'] . '" class="">'.
                                    ' <i style="padding:10px; font-size:12px; color:orange;" class="' . $link['attr']['classes'] . '"> '.$link['attr']['text'] .'</i> ' .
                                    (!empty($link['child'])? ' ' : '').
                                    '  </a>';

                $child_links_html = '';

                if (!empty($link['child'])) {
                    foreach($link['child'] as $child_text=>$child_link)
                    {
                        if (check_user_access($this, $child_text) || in_array($child_text, array('my_profile', 'log_out', 'home','audti_trail')))
                        {
                            $child_links_html .= '<li class="'. (($child_text == $current_menu)? 'active' : '') .'">'.
                                                 '<a href="'.  $child_link['url'] . '" class="">'.
                                                 $child_link['text'];

                            if(!empty($child_link['attr']['target']))
                            $child_links_html .='target="'.$child_link['attr']['target'].'"' ;
                                            $child_links_html   .= '</a>'.
                                '</li>';
                        }
                    }
                }

                if (!empty($child_links_html))
                {
                    print $parent_links_html.
                        '<ul id="' . $link['attr']['id'] . '-child" class="sub" style="  width: 255px;  margin-left: -40px; text-align:left;">' .
                          $child_links_html .
                          '</ul></li>';
                }
            }

            echo "</ul>";
        }
    }
    
}

?>