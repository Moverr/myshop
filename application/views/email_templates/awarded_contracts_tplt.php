
<style type="text/css">
    .tg  {border-collapse:collapse;border-spacing:0;border-color:#aabcfe;margin:0px auto;}
    .tg td{font-family:Arial, sans-serif;font-size:14px;padding:10px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;border-color:#aabcfe;color:#669;background-color:#e8edff;}
    .tg th{font-family:Arial, sans-serif;font-size:14px;font-weight:normal;padding:10px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;border-color:#aabcfe;color:#039;background-color:#b9c9fe;}
    .tg .tg-0ord{text-align:right}
    .tg .tg-ifyx{background-color:#D2E4FC;text-align:right}
    .tg .tg-s6z2{text-align:center}
    .tg .tg-vn4c{background-color:#D2E4FC}
    th.tg-sort-header::-moz-selection { background:transparent; }th.tg-sort-header::selection      { background:transparent; }th.tg-sort-header { cursor:pointer; }table th.tg-sort-header:after {  content:'';  float:right;  margin-top:7px;  border-width:0 4px 4px;  border-style:solid;  border-color:#404040 transparent;  visibility:hidden;  }table th.tg-sort-header:hover:after {  visibility:visible;  }table th.tg-sort-desc:after,table th.tg-sort-asc:after,table th.tg-sort-asc:hover:after {  visibility:visible;  opacity:0.4;  }table th.tg-sort-desc:after {  border-bottom:none;  border-width:4px 4px 0;  }@media screen and (max-width: 767px) {.tg {width: auto !important;}.tg col {width: auto !important;}.tg-wrap {overflow-x: auto;-webkit-overflow-scrolling: touch;margin: auto 0px;}}
    .page-header {
        padding-bottom: 9px;
        margin: 20px 0 30px;
        border-bottom: 1px solid #eee;
    }
    .text-center {
        text-align: center;
    }
</style>

<div class="page-header text-center">

    <?php

    if($this->session->userdata('isadmin')=='Y'){
        echo $this->input->post('pde')?'<p><b>'.get_pde_info_by_id($this->input->post('pde'),'title').'</b></p>':'';
    }else{
        echo '<p><b>'.get_pde_info_by_id($this->session->userdata('pdeid'),'title').'</b></p>';

    }
    ?>
    <?= $report_heading ?>

    <p>
    <h5>
        Financial Year : <?= $financial_year ?><br><br>
        <small>
            Reporting Period : <?= $reporting_period ?>
        </small>

    </h5>



    </p>
</div>
<div class="tg-wrap"><table id="tg-r2gGz" class="tg">

        <thead>
        <tr>
            <th>Procurement Reference Number</th>
            <?php
            if ($this->session->userdata('isadmin') == 'Y') {
                ?>
                <th>PDE</th>
            <?php
            }
            ?>

            <th>Subject of procurement</th>
            <th>Method of procurement</th>
            <th>Provider</th>
            <th>Date of award of contract</th>
            <th>Market price of the procurement</th>
            <th>Contract value (UGX)</th>


        </tr>
        </thead>

        <tbody>
        <?php
        $grand_total_actual_payments = array();
        $grand_amount = array();
        //print_array($results);
        foreach ($results as $row) {
            $grand_total_actual_payments[] = $row['estimated_amount'];
            $grand_amount[] = $row['amount'] * $row['xrate'];
            ?>
            <tr>
                <td>
                    <?= $row['procurement_ref_no'] ?>
                </td>
                <?php
                if ($this->session->userdata('isadmin') == 'Y') {
                    ?>
                    <td><?= $row['pdename'] ?></td>
                <?php
                }
                ?>
                <td>
                    <?= $row['subject_of_procurement'] ?>
                </td>
                <td>
                    <?= get_procurement_method_info_by_id($row['procurement_method'], 'title') ?>
                </td>
                <td><?= get_provider_by_procurement($row['procurement_ref_id']) ?></td>


                <td>
                    <?= custom_date_format('d M Y', $row['contract_award_date']) ?>
                </td>
                <td style="text-align: right;">
                    <?= number_format($row['estimated_amount']) ?>
                </td>

                <td style="text-align: right;">
                    <?= number_format($row['amount'] * $row['xrate']) ?>
                </td>


            </tr>
        <?php
        }
        ?>
        <tr>
            <td></td>
            <?php
            if ($this->session->userdata('isadmin') == 'Y') {
                ?>
                <td></td>
            <?php
            }
            ?>
            <td></td>

            <td></td>
            <td></td>

            <td></td>
            <td style="border-top: 1px solid #000; text-align: right; ">
                <b><?= number_format(array_sum($grand_total_actual_payments)) ?></b></td>
            <td style="border-top: 1px solid #000; text-align: right;">
                <b><?= number_format(array_sum($grand_amount)) ?></b></td>

        </tr>


        </tbody>
    </table></div>
<script type="text/javascript" charset="utf-8">var TgTableSort=window.TgTableSort||function(n,t){"use strict";function r(n,t){for(var e=[],o=n.childNodes,i=0;i<o.length;++i){var u=o[i];if("."==t.substring(0,1)){var a=t.substring(1);f(u,a)&&e.push(u)}else u.nodeName.toLowerCase()==t&&e.push(u);var c=r(u,t);e=e.concat(c)}return e}function e(n,t){var e=[],o=r(n,"tr");return o.forEach(function(n){var o=r(n,"td");t>=0&&t<o.length&&e.push(o[t])}),e}function o(n){return n.textContent||n.innerText||""}function i(n){return n.innerHTML||""}function u(n,t){var r=e(n,t);return r.map(o)}function a(n,t){var r=e(n,t);return r.map(i)}function c(n){var t=n.className||"";return t.match(/\S+/g)||[]}function f(n,t){return-1!=c(n).indexOf(t)}function s(n,t){f(n,t)||(n.className+=" "+t)}function d(n,t){if(f(n,t)){var r=c(n),e=r.indexOf(t);r.splice(e,1),n.className=r.join(" ")}}function v(n){d(n,L),d(n,E)}function l(n,t,e){r(n,"."+E).map(v),r(n,"."+L).map(v),e==T?s(t,E):s(t,L)}function g(n){return function(t,r){var e=n*t.str.localeCompare(r.str);return 0==e&&(e=t.index-r.index),e}}function h(n){return function(t,r){var e=+t.str,o=+r.str;return e==o?t.index-r.index:n*(e-o)}}function m(n,t,r){var e=u(n,t),o=e.map(function(n,t){return{str:n,index:t}}),i=e&&-1==e.map(isNaN).indexOf(!0),a=i?h(r):g(r);return o.sort(a),o.map(function(n){return n.index})}function p(n,t,r,o){for(var i=f(o,E)?N:T,u=m(n,r,i),c=0;t>c;++c){var s=e(n,c),d=a(n,c);s.forEach(function(n,t){n.innerHTML=d[u[t]]})}l(n,o,i)}function x(n,t){var r=t.length;t.forEach(function(t,e){t.addEventListener("click",function(){p(n,r,e,t)}),s(t,"tg-sort-header")})}var T=1,N=-1,E="tg-sort-asc",L="tg-sort-desc";return function(t){var e=n.getElementById(t),o=r(e,"tr"),i=o.length>0?r(o[0],"td"):[];0==i.length&&(i=r(o[0],"th"));for(var u=1;u<o.length;++u){var a=r(o[u],"td");if(a.length!=i.length)return}x(e,i)}}(document);document.addEventListener("DOMContentLoaded",function(n){TgTableSort("tg-r2gGz")});</script>