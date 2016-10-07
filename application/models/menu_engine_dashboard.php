<?php
#Used to format menu displays
class Menu_engine_dashboard extends CI_Model{

	var $menu_level = 0;
	
	 
	#Constructor
	function Menu_engine_dashboard($current_menu ='')
	{ 

	$this -> load -> model("menu_engine","menu_engie");
		parent::__construct();
		$this->load->database();

		$nav_menu_str = '';
		
		$menu_array = $this-> menu_engie ->menu_bucket();
		
		if(check_user_access($this, 'manage_reports'));
		$st = "";
		if(!empty($menu_array))
		{
			
			$x = 0;
					  
			foreach($menu_array AS $text=>$link)
			{	
				if(!empty($link['child']))
				{

		 
			$x ++;		
			
				$parent_links_html = '<li class="'. (!empty($link['child'])? ' has-sub' : '') . 
									(($text == $current_menu || $text == $this->find_my_parent($current_menu))? ' active open' : '') .'">'.
					 				'<a href="'. $link['attr']['url'] . '" id="' . $link['attr']['id'] . '" class="">'.
					 				'<span class="icon-box"><i class="' . $link['attr']['classes'] . '"></i></span>  &nbsp;'.
					 				$link['attr']['text'].
					 				(!empty($link['child'])? '<span class="arrow"></span>' : '').
					 				'</a>';
					 
				$child_links_html = '';
					
				if(!empty($link['child']))
				{				
					foreach($link['child'] as $child_text=>$child_link)
					{
						if(check_user_access($this, $child_text) || in_array($child_text, array('my_profile','log_out','home')))
						{
							$child_links_html .= '<li class="'. (($child_text == $current_menu)? 'active' : '') .'">'.
												 '<a href="'.  $child_link['url'] . '" class="">'.
												 $child_link['text'].
												 '</a>'.
												 '</li>';	
						}
					}
				}
				
				if(!empty($child_links_html))
				{
					$st .= '<div class="span3">  <ul>';	
					
					$st .= $parent_links_html.
						  '<ul id="' . $link['attr']['id'] . '-child" class="sub">'. 
						  $child_links_html .
						  '</ul></li>';
				}
				$st .= "</ul> </div>";
				if($x > 1)
				{
					echo $st;

				}
				$st = "";
			}		  
			 
			}
		}
	}
	
	 
	 
	function display_menu($current_menu = '')
	{
		$nav_menu_str = '';
		
		$menu_array = $this-> menu_engie -> menu_bucket();
		
		if(check_user_access($this, 'manage_reports'));
		
		if(!empty($menu_array))
		{
			echo '<div class="span3"> <ul>';
					  
			foreach($menu_array AS $text=>$link)
			{				
				$parent_links_html = '<li class="'. (!empty($link['child'])? ' has-sub' : '') . 
									(($text == $current_menu || $text == $this->find_my_parent($current_menu))? ' active open' : '') .'">'.
					 				'<a href="'. $link['attr']['url'] . '" id="' . $link['attr']['id'] . '" class="sed">'.
					 				'<span class="icon-box"><i class="' . $link['attr']['classes'] . '"></i></span>'.
					 				$link['attr']['text'].
					 				(!empty($link['child'])? '<span class="arrow"></span>' : '').
					 				'</a>';
					 
				$child_links_html = '';
					
				if(!empty($link['child']))
				{				
					foreach($link['child'] as $child_text=>$child_link)
					{
						if(check_user_access($this, $child_text) || in_array($child_text, array('my_profile','log_out','home')))
						{
							$child_links_html .= '<li class="'. (($child_text == $current_menu)? 'active' : '') .'">'.
												 '<a href="'.  $child_link['url'] . '" class="">'.
												 $child_link['text'].
												 '</a>'.
												 '</li>';	
						}
					}
				}
				
				if(!empty($child_links_html))
				{
					print $parent_links_html.
						  '<ul id="' . $link['attr']['id'] . '-child" class="sub">'. 
						  $child_links_html .
						  '</ul></li>';
				}
			}		  
			 
			echo "</ul> </div>";
		}
	}
	
	
	#Find menu parent
	function find_my_parent($menu_item = '')
	{
		$parent_index = '';
		$menu_items = $this-> menu_engie -> menu_bucket();
		
		foreach($menu_items as $item_key=>$item)
		{
			if($item_key == $menu_item)
			{
				return '';
			}
			else
			{
				if(is_array($item) && !empty($item['child']))
				{
					foreach($item['child'] as $child_key=>$child)
					{
						if($child_key == $menu_item)
						{
							return $item_key;
						}
					}
				}
			}
		}
		
		return $parent_index;
		
	}
	
}

?>