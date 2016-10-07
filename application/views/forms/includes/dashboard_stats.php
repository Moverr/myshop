<div class="span2 responsive" data-tablet="span3" data-desktop="span2">
    <div class="circle-stat block">
        
        <div class="details">
            <div>
                <span class="number"><?=$total_procurement_records?></span>
            </div>
            <div class="title">Procurement entries </div>
        </div>
    </div>
</div>

<div class="span2 responsive" data-tablet="span3" data-desktop="span2">
    <div class="circle-stat block">
        <div class="details">
            <div>
                <span class="number"><?=$ifbs_submitted?></span>
            </div>
            <div class="title">IFBs published</div>
        </div>

    </div>
</div>


<div class="span2 responsive" data-tablet="span3" data-desktop="span2">
    <div class="circle-stat block">
        <div class="details">
            <div>
                <span class="number"><?=$bebs_published?></span>
            </div>
            <div class="title">BEBs published</div>
        </div>

    </div>
</div>


<div class="span2 responsive" data-tablet="span3" data-desktop="span2">
    <div class="circle-stat block">
        <div class="details">
            <div>
                <span class="number"><?=$contracts_awarded?></span>
            </div>
            <div class="title">Contracts</div>
        </div>

    </div>
</div>

<script>
    $(document).ready(function(){
      /*  $('.number').each(function () {
            var $this = $(this);
            jQuery({ Counter: 0 }).animate({ Counter: $this.text() }, {
                duration: 1000,
                easing: 'swing',
                step: function () {
                    $this.text(Math.ceil(this.Counter));
                }
            });
        }); */
    });  
</script>