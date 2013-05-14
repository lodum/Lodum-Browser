
var $ = jQuery.noConflict();


$(document).ready(function() {

	$('[rel=tooltip]').tooltip() ;

	$('.pull-down').each(function() {
    	$(this).css('margin-top', $(this).parent().height()-$(this).height())
	});

	$('#list-tabs a').click(function (e) {
	    e.preventDefault();
	  	$(this).tab('show');

	  	//init and generate pagniation for the current active tab
	  	initPagination();
	})

	$('#showpropertytable').click(function() {
		/*if($("#htmlproperties").is(":hidden")){
	    	$('.htmlproperties').toggle(400, function(){
	    	    $('#propertytable').toggle(400);
	    	});
	    
		}else{ 
	    	$('.htmlproperties').toggle(400, function(){
	    	    $('#propertytable').toggle(400);
	    	});
		}*/
		$('.htmlproperties').toggle(400);
	    $('#propertytable').toggle(400);
	});


    initPagination();

});

function initPagination(){
	//how much items per page to show
    var show_per_page = 8; 
    //getting the amount of elements inside content div
    var number_of_items = $('.tab-pane.active > .content').children().size();
    //calculate the number of pages we are going to have
    var number_of_pages = Math.ceil(number_of_items/show_per_page);
    
    //set the value of our hidden input fields
    $('#current_page').val(1);
    $('#show_per_page').val(show_per_page);
    
    if(number_of_items>=show_per_page){
    	var navigation_html = '<ul><li><a class="previous_link" href="javascript:previous();">«</a></li>';
	    var current_link = 1;
	    while(number_of_pages+1 > current_link){
	        navigation_html += '<li><a class="page_link" href="javascript:go_to_page(' + current_link +')" longdesc="' + current_link +'">'+ (current_link ) +'</a></li>';
	        current_link++;
	    }
	    navigation_html += '<li><a class="next_link" href="javascript:next();">»</a></li></ul>';
	    
	   $('.tab-pane.active > #page_navigation').html(navigation_html);
	    
	    //add active_page class to the first page link
	   // $('#page_navigation .page_link:first').addClass('active_page');
	    $($('.tab-pane.active > #page_navigation li')[1]).addClass('active')
	    //hide all the elements inside content div
	    $('.tab-pane.active > .content').children().css('display', 'none');
	    
	    //and show the first n (show_per_page) elements
	    $('.tab-pane.active > .content').children().slice(0, show_per_page).removeAttr("style");
    }
   
}


function previous(){
    page = parseInt($('#current_page').val()) - 1;
    if($('.tab-pane.active > #page_navigation li a[longdesc='+page+']').length>0){
        go_to_page(page);
    }
    
}
 
function next(){
    page = parseInt($('#current_page').val()) + 1;
    if($('.tab-pane.active > #page_navigation li a[longdesc='+page+']').length>0){
        go_to_page(page);
    }
    
}
function go_to_page(page_num){
    //get the number of items shown per page
    var show_per_page = parseInt($('#show_per_page').val());
    
    //get the element number where to start the slice from
    start_from = (page_num-1) * show_per_page;
    
    //get the element number where to end the slice
    end_on = start_from + show_per_page;
    
    //hide all children elements of content div, get specific items and show them
    $('.tab-pane.active > .content').children().css('display', 'none').slice(start_from, end_on).removeAttr("style");
    //css('display', 'block');
    
    /*get the page link that has longdesc attribute of the current page and add active_page class to it
    and remove that class from previously active page link*/
   // $('.page_link[longdesc=' + page_num +']').addClass('active_page').siblings('.active_page').removeClass('active_page');
	//$('.page_link').parent().removeClass('active');
	$('.tab-pane.active > .pagination > ul > li.active').removeClass('active');
   	$($('.tab-pane.active > .pagination > ul > li')[page_num]).addClass('active');

   // $($('.page_link[longdesc=' + page_num +']')[0].parentNode).addClass('active');
    
    //update the current page input field
    $('#current_page').val(page_num);
}