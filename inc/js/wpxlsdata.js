


jQuery(document).ready(function($) {

var pss = 1; 
var updated= 0 ; 
var added= 0 ; 
			
$('tr[data-slug ="wpxlsdata"]').click(function () {

var al = 'What do you want \n';
al+='Why do you disable the plugin?\n';
al+='Doesn,t work for you\n';
al+='whatsapp == + 989385512929 ==\n';
al+='You can help us improve by explaining why the plugin was removed';
alert(al)

});


		if ($("#startimport").length > 0  ) 
		{

			var s=parseInt($(".meter").attr("data-s"));  
			var p = parseInt($(".meter").attr("data-p"));  
			var path= $(".meter").attr("data-path");  
			var id_wpxlsdata= $(".meter").attr("data-id");  
            doimport(pss,p,s,path,id_wpxlsdata)
            $(".meter span").css({width:"5%"}); 

			

		
		}


		function doimport(s,p,sf,path,id_wpxlsdata) 

		{
          
           var data = {action: 'wpxlsdata_import',s:s,p:p,path:path,id_wpxlsdata:id_wpxlsdata};
           $.post(the_in_url.in_url, data, function(response)
           {

             var res=$.parseJSON(response);
           	if (res.status==200) 
           	{

           		added+=parseInt(res.in);
                updated+=parseInt(res.up);
           	}
            pss++;
           	if (pss <= p) 
           	{
              doimport(pss,p,sf,path,id_wpxlsdata)
            $(".meter span").css({width:(pss*sf)+"%"}); 

           	}

           	else 
           	{
           		
           		 $("#added").text($("#added").text()+" ="+added);
                 $("#updated").text($("#updated").text()+" ="+updated);
                 $(".meter").hide(400); 
                 $("#messageimport").show(400); 
                 $("#warning").hide(400)
           	}






           	//console.log(sf)
           });

		}	

$("#newshortcode").click(function (){

  $(".modalces").show(400);
});

$(".closeces").click(function (){

  $(".modalces").hide(400);
});



$(".deletes").click(function (){

  var dataid =parseInt( $(this).attr("data-id")) ; 
  var data = {action: 'wpxlsdata_delete_shortcode',dataid:dataid};
           $.post(the_in_url.in_url, data, function(response)
           {
             var res=$.parseJSON(response);
             if (res["status"]==200) 
             {

              $( ".del-"+dataid).css({"background":"red"});
              $( ".del-"+dataid).animate({
                    opacity: 0.25,
                  }, 500, function() {
                   $( ".del-"+dataid).remove();
                  }); 



             }

          });
});

$("#saveshortcode").click(function (){

  var title= $("#titleshortcode").val();
   var id_wpxlsdata = $("#idwpxlsdata").val();
   var counts =parseInt( $("#counts").val()) ;
   var limitrow = parseInt($("#limitrow").val());
  var v="";
    $(".checkboxrows:checked").each(function(index, element) {
    v+=$(element).val()+",";
 });


    var data = {action: 'wpxlsdata_add_shortcode',rows:v,title:title,id_wpxlsdata:id_wpxlsdata,limitrow:limitrow};
           $.post(the_in_url.in_url, data, function(response)
           {
             var res=$.parseJSON(response);
             $(".modalces").hide(400);
             var cc= counts+1; 

             var html ="<tr>";
              html+="<td  class='author column-author'>"+cc+"</td>";
              html+="<td  class='author column-author'>"+title+" </td>"
              html+="<td  class='author column-author'> "+res["s"]+" </td>";
            html+="</tr>" ;

             $("#trtabel").append(html) ; 
             $("#counts").val(cc) ;
           });


  
});


$("#exportdata").click(function (){

  var type_file= $('input[name="typefile"]:checked').val();
  var s=parseInt($(".meter").attr("data-s"));  
  var id_wpxlsdata= $(".meter").attr("data-id");
  $(".meter span").css({width:"10%"}); 
 $(".meter").show(400);
  exportdata(s,type_file,id_wpxlsdata);
    


    })


function exportdata(s,type_file,id_wpxlsdata) 

{

var data = {action: 'wpxlsdata_export',s:pss,type_file:type_file,id_wpxlsdata:id_wpxlsdata};
    $.post(the_in_url.in_url, data, function(response)
    {
     var res=$.parseJSON(response);
     var pp=(pss/s)*100;
     $(".meter span").css({width:pp+"%"}); 
     $("#linkexports").append("<p><a href='"+res.url+"' target='_blank'>"+res.filename+"</a></p>")

    pss++;

    if (pss <= s) 
      {
        exportdata(s,type_file,id_wpxlsdata);
      }

     
     	else 
           	{
           		
           	 $("#linkexports").show(400);	 
            $(".meter").hide(400); 
              
           	}


    });


}

$(".valwpxlsdata").click(function () {
  
  $(this).hide() ; 
 $(".none").hide() ; 
  var id_row = $(this).attr("data-id"); 
  $("#"+id_row).show() ; 


});

$(".input-edit").focusout(function() {
	    var id_row = $(this).attr("id"); 
    $(this).hide() ;
   $("."+id_row).show() ; 

});
$(".input-edit").change(function (){

	var val = $(this).val() ; 
    var id_wpxlsdata = $(this).attr("data-db"); 
    var data_col = $(this).attr("data-col"); 
    var data_row = $(this).attr("data-row"); 
    var id_row = $(this).attr("id"); 


    var data = {action: 'edit_data_row',val:val,data_col:data_col,id_wpxlsdata:id_wpxlsdata,data_row:data_row};
    $.post(the_in_url.in_url, data, function(response)
    {
     var res=$.parseJSON(response);

    });

    $(this).hide() ;
   $("."+id_row).text(val) ; 
   $("."+id_row).show() ; 




});

});