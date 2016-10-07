<div class="navbar navbar-default">
    <div class="container-fluid nav">
        <div class="collapse navbar-collapse" id="bs-megadropdown-tabs">
            <div class="col-md-12 column clearfix" style="margin:0 auto">

            <!-- Collect the nav links, forms, and other content for toggling -->
                <ul class="nav navbar-nav">
                    <li><a href="<?=base_url() . 'page/procurement_plans'?>" class="<?=((!empty($current_menu) && $current_menu == 'procurement_plans')? 'current' : '' )?>">Procurement Plans</a></li>
                    <li><a href="<?=base_url() . 'page/disposal_plans'?>" class="<?=((!empty($current_menu) && $current_menu == 'disposal_plans')? 'current' : '' )?>" >Disposal Plans + Notices</a></li>
                    <li><a href="<?=base_url() . 'page/home'?>" class="<?=(empty($current_menu)? 'current' : '' )?>">Current Tenders</a></li>
                    <li><a href="<?=base_url() . 'page/best_evaluated_bidder'?>" class="<?=((!empty($current_menu) && $current_menu == 'beb')? 'current' : '' )?>">Best Evaluated Bidder Notices</a></li>
                    <li><a href="<?=base_url() . 'page/awarded_contracts'?>" class="<?=((!empty($current_menu) && $current_menu == 'awarded_contracts')? 'current' : '' )?>">Signed Contracts</a></li>
                    <li><a href="<?=base_url() . 'page/suspended_providers'?>" class="<?=((!empty($current_menu) && $current_menu == 'suspended_providers')? 'current' : '' )?>">Suspended Providers</a></li>


                </ul>

                <form class="navbar-form pull-right" role="search" style="width:200px;font-family:'PT Sans Narrow'; font-size:16px;" id="search-form">
                    <div  class="form-group">
                        <label id="nav_src" type="text" class="form-control search-box" style="background:#006E93; border:0px;">
                        </label>
                              </div>
                </form>

            </div>
        </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
</div>