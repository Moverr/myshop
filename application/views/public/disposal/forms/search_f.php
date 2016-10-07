<form method="GET" action="<?=base_url().$this->uri->segment(1).'/search_disposals/'.$this->uri->segment(2)?>" accept-charset="UTF-8" class="form-horizontal ng-pristine ng-valid">
    <div class="box-tools">
        <div class="input-group input-group-sm " >
            <input placeholder="Search for disposals" id="term" class="form-control" name="term" type="text" value="">
            <div class="input-group-btn">
                <button type="submit" class="btn btn-info btn-flat">Go!</button>
            </div>
        </div>
    </div>

</form>