/**
 * Created by Administrator on 2016/12/20.
 */
$(function(){

})

//修改基本资料
function Check(price)
{

    $('#button').prop('disabled',true);


    if ($.trim($('#price').val()) == '')
    {

        alert("请输入要拆分的金额！");
        $('#price').focus();
        $('#button').prop('disabled',false);
        return false;


    }
    var reg = new RegExp("^[0-9]*$");
    if(!reg.test($.trim($('#price').val()))){
        alert("请输入整数!");
        $('#price').focus();
        $('#button').prop('disabled',false);
        return false;
    }

    if (parseInt($.trim($('#price').val())) <= 0)
    {
        alert("拆分的金额不能小于0！");
        $('#price').focus();
        $('#button').prop('disabled',false);
        return false;
    }


    if (parseInt($.trim($('#price').val())) > price)
    {
        alert("拆分的金额不能大于"+price+"！");
        $('#price').focus();
        $('#button').prop('disabled',false);
        return false;
    }
    $('#button').prop('disabled',false);

    return true;


}


