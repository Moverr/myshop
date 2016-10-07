<h3 class="page-title">
    <?=(!empty($page_title)? $page_title : '' )?>
    <small><?=(!empty($page_description)? $page_description : '' )?></small>
</h3>
<ul class="breadcrumb">
    <li>
        <a href="<?=base_url(). 'user/dashboard'?>"><i class="icon-home"></i></a><span class="divider">&nbsp;</span>
    </li>
    <?=$this->menu_engine->menu_crumbs((!empty($current_menu)? $current_menu : ''));?>
    <li class="pull-right search-wrap">
        <form class="hidden-phone">
            <div class="search-input-area">
                <input id=" " class="search-query" type="text" placeholder="Search">
                <i class="icon-search"></i>
            </div>
        </form>
    </li>
</ul>