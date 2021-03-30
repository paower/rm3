






//匹配
var pipei=function(id)
{
    var str;
    str='';
    $('input[name="subid[]"]:checked').each(function(){
        str+=$(this).val()+',';
    });
    str = $.trim(str);
    var aid = $('input[name="aid"]').val();

    $.post('/admin/Finance/test',{'str':str,'id':aid},function(res){
        
        if(res.code==1)
        {
            alert(res.msg);
            location.href='/admin/Finance/index.html';
            // window.location.href=
        }else{
            alert(res.msg);
        }
    },'json');
}

//匹配
var pipeiget=function(id)
{
    var str;
    str='';
    $('input[name=T'+id+']:checked').each(function(){
        str+=$(this).val()+',';

    })
    $('#buttong'+id).prop('disabled',true);

    $.ajax({
        type: "POST",
        url: "/admin/index/dopipei",
        data: {'action':2,'id':id,'str':str},
        async:false,
        //dataType: "json",
        success: function(data){



            if(data==1)
            {
                alert('匹配成功！');
                //$('#button'+id).prop('disabled',false);
                location.href='/admin/index/pipei.html';

            }



        }
    })
}



var caidan=function(id,t)
{
    location.href='/admin/index/dpipei.html?id='+id+'&t='+t;
}

