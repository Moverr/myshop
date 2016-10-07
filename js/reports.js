$(document).ready(function () {
    $('.populate').select2();




    //when finacial year changes clear month fields
    $('#financial_year').change(function () {
        $(".date-picker").val('');
    });


    $('.from_date').datepicker();
    $('.to_date').datepicker();

});