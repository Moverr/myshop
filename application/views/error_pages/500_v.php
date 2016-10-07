<div class="widget-body">
    <div class="error-page" style="min-height: 800px">
        <img src="<?=base_url()?>img/500.png" alt="500 error">
        <h1>
            <strong>500!</strong> <br>
            OOPS! <?php
            if(isset($message))
            {
                echo $message;
            }
            else
            {
                echo 'Something went wrong.';
            }
            ?>
        </h1>
    </div>
</div>