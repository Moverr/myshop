<div class="widget">
    <div class="widget-title">
        <h4><i class="icon-reorder"></i>&nbsp;Insights</h4>
            <span class="tools">
                <a href="javascript:;" class="icon-chevron-down"></a>
                <a href="javascript:;" class="icon-remove"></a>
            </span>
    </div>
    <div class="widget-body" id="app-stats-overview">
    	<div class="row-fluid">
        	 
        </div>
        
        <!-- BEGIN OVERVIEW STATISTIC BLOCKS-->
        <div class="row-fluid">
         <div class="col-md-3" style="width:200px; float:left; margin-top:-5px">
                    <select id="dashboard-stats-financial-year" class="input-medium m-wrap" name="financial_year">
                           
                        </select>
                    </select>
                     <label class="control-label" style="width:160px; float:left">SELECT FINANCIAL YEAR</label>
          </div>
        <div class=" col-md-8 circle-state-overview results-stats-overview">
            <?php $this->load->view('includes/dashboard_stats');?>
         </div>
        </div>
        <!-- END OVERVIEW STATISTIC BLOCKS-->
    </div>
</div>