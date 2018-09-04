function check() {
    var vl = document.getElementById('url'),
        ling = document.getElementById('ling');
    var pattern =
            /^http(s?):\/\/(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/;

    var vue = vl.value;
    if( vue == null || vue == "" || vue == undefined)
    {
        alert("请输入链接！");
        return false;
    }else
    {
        if(!pattern.test(vue))
        {
            alert("非合法网址！");
            return false;
        }
        ling.innerHTML = "";
        ajaxDownload(vue);
        vl.value = "";//清空文本
        ling.innerHTML = "开始下载详情图，请稍后......";
    }
}



function ajaxDownload(url)
{
    var xmlhttp;
    if (window.XMLHttpRequest)
    {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp=new XMLHttpRequest();
    }
    else
    {// code for IE6, IE5
        xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange=function()
    {
        if (xmlhttp.readyState==4 && xmlhttp.status==200)
        {
            document.getElementById("ling").innerHTML=xmlhttp.responseText;
        }
    }
    xmlhttp.open("GET","catch_taobao.php?url=" + url,true);
    xmlhttp.send();
}
