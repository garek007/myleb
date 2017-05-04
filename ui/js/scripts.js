// JavaScript Document


//my code


var monthNames = ["jan", "feb", "mar", "apr", "may", "jun", "jul", "aug", "sep", "oct", "nov", "dec"];
var monthNums = ["01","02","03","04","05","06","07","08","09","10","11","12"];
var monthNamesFull = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
var d = new Date();
var todaysDate = d.getDate();
var todaysWeekday = d.getDay();
var monthAbbr = monthNames[d.getMonth()];
var monthNum = monthNums[d.getMonth()];
var nextMonthAbbr = monthNames[d.getMonth() + 1];
var fullMonthName = monthNamesFull[d.getMonth()];
var year = d.getFullYear();
var startDate = getNearestTuesday(todaysDate, todaysWeekday);
var nextFriday = getNearestFriday(todaysDate,todaysWeekday);
var endDate = Number(startDate + 5);
var yearMonDay = year+"-"+(monthNums[d.getMonth()])+"-"+todaysDate;
var monDayYear = fullMonthName + " " + todaysDate + ", " + year;

function getNearestTuesday(t, wd) {
    var sDate; //this will be set to Tuesday below.
    switch (wd) {
        case 0:
            sDate = t + 2;
            break;
        case 1:
            sDate = t + 1;
            break;
        case 2:
            sDate = t;
            break;
        case 3:
            sDate = t - 1;
            break;
        case 4:
            sDate = t - 2;
            break;
        case 5:
            sDate = t - 3;
            break;
        case 6:
            sDate = t - 4;
            break;
        default:
            Date = t;
    }
    return sDate;
}
function getNearestFriday(t, wd) {
    var sDate; //this will be set to Friday below.
    switch (wd) {
        case 0:
            sDate = t + 5;
            break;
        case 1:
            sDate = t + 4;
            break;
        case 2:
            sDate = t+3;
            break;
        case 3:
            sDate = t + 2;
            break;
        case 4:
            sDate = t + 1;
            break;
        case 5:
            sDate = t;
            break;
        case 6:
            sDate = t - 1;
            break;
        default:
            Date = t;
    }
    return sDate;
}
function pad(n) {
    return (n < 10) ? ("0" + n) : n;
}


function tagForGA(url, tmp) {
    var today = new Date();
    if (tmp == "tttd") {
        var cname = "Top Things to Do";
        var source = $('#utm_source').val();//year + '_' + monthAbbr + '_' + startDate + '-' + endDate;
    } else if (tmp == "monthly") {
        var cname = "Consumer Newsletter Monthly";
        var source = monthNames[d.getMonth() + 1];
    }
    var taggedURL = url + '?utm_campaign=' + cname + '&utm_source=' + source + '&utm_medium=email';
    return taggedURL;
}

function getQueryVariable(variable) {
    var query = window.location.search.substring(1);
    var vars = query.split("&");
    for (var i = 0; i < vars.length; i++) {
        var pair = vars[i].split("=");
        if (pair[0] == variable) {
            return pair[1];
        }
    }
    return (false);
}
//maybe there's a better way to do this, I need to call this function
//every.single.time an event happens because even after a drag N drop
//jquery doesn't see the new draggable items without initializing them again
function doDraggable() {
    $('.numbered-list').find('.numbered-list-container').sortable({
        handle: ".drag",
        stop: function(event, ui) {
            var $listItem = $(this).find('.numbered-list-item');
            $.each($listItem, function(index) {
                $(this).find('.number').text(index + 1);
            });
        }
    });
    $(".draggable").draggable({
        revert: function(event, ui) {
            $('.draggedFrom').removeClass('draggedFrom');
            return true;
        },
        start: function(event, ui) {
            var $p = $(this).parent();
            $p.addClass('draggedFrom');
            var $html = $p.html();

            //$p.droppable('disable');

            $('.blockme, .nopad').not($p).not(".contentarea_container").droppable({
                hoverClass: "activated",
                drop: function(event, ui) {
									if(ui.draggable.find('.nopad').hasClass("padLeft")){
										ui.draggable.find('.nopad').css({'padding-right':'15px','padding-left':'0'}).addClass('padRight').removeClass('padLeft');
									}else if(ui.draggable.find('.nopad').hasClass("padRight")){
										ui.draggable.find('.nopad').css({'padding-left':'15px','padding-right':'0'}).addClass('padLeft');
									}


                    var $html = $(this).html();
                    $(this).empty();
                    //ui.draggable.detach().appendTo($(this)).removeAttr('style');
                    ui.draggable.detach().appendTo($(this)).css({
                        top: 0,
                        left: 0,
                        position: 'relative'
                    });
                    $('.draggedFrom').html($html).removeClass('draggedFrom');
                    $('.ui-droppable').droppable("destroy");
                    //$(this).removeClass('activated');
                    doDraggable();
                }
            });
            //console.log($html);
        }




    });
}
$(document).ajaxComplete(function() {
    doDraggable();
});
$(document).ready(function() {
    doDraggable();
	var $container = $("#container");

	if($container.hasClass("new_project")){

		var $tmp = $container.data("template");
		$.get("templates/"+$tmp+".html", function(data) {

					$container.append(data);
					$container.removeClass("new_project");
					$(".senddate").text(fullMonthName + " " + nextFriday + ", " + year);


		});


	}
	$('body').on('click', '#add-ga-tags', function() {

			$('#ga-fields').slideDown({
					duration: 1000,
					easing: 'swing'
			});

	});


    //Drag n Drop Stuff
    $(document).delegate(".drop-area", 'dragenter', function(e) {
        e.preventDefault();
        //$(this).css('background', '#BBD5B8');
        $(this).css('border', '1px solid red');
    });
    $(document).delegate(".drop-area", 'dragleave', function(e) {
        e.preventDefault();
        $(this).css('border', 'none');
    });
    $(document).delegate(".drop-area", 'dragover', function(e) {
        e.preventDefault();
    });
		var $uploadCrop;
		var imageName;
		var $uploadLocation = 'exacttarget';
    $(document).delegate('.drop-area', 'drop', function(e) {
			//if($(this).is('.cloudinary')){$uploadLocation = 'cloudinary';}
				//$uploadLocation = 'cloudinary';
        //$(this).css('background', '#D8F9D3');
        $(this).css('border', 'none');
        e.preventDefault();
        //var image = e.originalEvent.dataTransfer.files;
				console.log(e.originalEvent.dataTransfer.files);
        //createFormData(image);
        $(this).addClass('activeImage');
        var $w = $(this).data('width');
        var $h = $(this).data('height');
        var image = e.originalEvent.dataTransfer.files[0];
        //console.log(e.originalEvent.dataTransfer.files[0]);
        //console.log(e.originalEvent.dataTransfer.files[1]);
        //imageName = Date.now() + image.name;
        imageName = new Date().getUTCMilliseconds()+ image.name;

        $('#cropbox').show();

        $uploadCrop = $('#cropbox').croppie({
            viewport: {
                width: $w,
                height: $h
            },
            boundary: {
                width: $w + 100,
                height: $h + 100
            }
        });
				$('#viewport_height').slider('value', $(".cr-viewport").outerHeight());
				$(".viewport_height_value").text($(".cr-viewport").outerHeight());
        // if (input.files && input.files[0]) {
        var reader = new FileReader();
				console.log(reader);
        reader.onload = function(e) {
						console.log('wtf '+imageName);
            $uploadCrop.croppie('bind', {
                url: e.target.result
            });
        }
        reader.readAsDataURL(image);
        // }

        $('.upload-cancel').on('click', function(ev) {
            $('#cropbox').fadeOut('fast');
            $uploadCrop.croppie('destroy');
            $('.activeImage').css('border', 'none');
            $('.activeImage').removeClass('activeImage');

        });
    });

		$('.upload-result').on('click', function(ev) {
				$uploadCrop.croppie('result', {
						type: 'canvas',
						size: 'viewport',
						format: 'jpeg'
				}).then(function(resp) {
					console.log(typeof imageName);

						$('.fa-spinner').fadeIn();
						$.ajax({
								url: "/processUploads2.php",
								type: "POST",
								data: {
										'file': resp,
										'imagename': imageName,
										'uploadLocation':$uploadLocation
								},
								dataType : 'json',
						}).done(function(data) {
									console.log("my var is "+data['url']);
								$('#cropbox').fadeOut('slow', function() {
										//$('.activeImage').attr('src', 'http://image.updates.sandiego.org/lib/fe9e15707566017871/m/4/' + imageName);
										var $target = $(".activeImage");
										if($target.is(":input")){
											$target.val(data['url']);
										}else{
											$target.attr('src', data['url']);
										}

										$target.removeClass('activeImage');
										$uploadCrop.croppie('destroy');
										$('.fa-spinner').fadeOut();
								});
						}).fail(function(data) {
								alert("there was a problem uploading your image");
								console.log(data);
						});
				});
		});

$(".form").validate();
    $('#twitter_text').on('keyup', function() {
        var charsLeft = 120 - $(this).val().length;
        $('#charcount').text(charsLeft);
        if (charsLeft < 10) {
            $('#charcount').css({
                'color': 'red'
            });
        }
    });


    var w = $(window).width() - 200;
    var h = $(window).height() - 100;
    $('#load').dialog({
        resizable: false,
        height: h,
        width: w,
        dialogClass: 'noTitle',
        modal: true,
        autoOpen: false,
        resizable: true,
        position: {
            my: "left top",
            at: "left+20 top+20",
            of: window
        },
        close: function(event, ui) {
            $('#loadContent').empty();
            //$(this).dialog('destroy');
        }
    });


    $('#syncToET').click(function() {

        var theCode = '';
        $('.contentarea_container').each(function() {
            theCode += $(this).find('.contentarea').wrap('<p/>').parent().html();
        });

			$.ajax({
					type: "POST",
					dataType: "text",
					url: "00-Includes/addUpdate-ET.php",
					data: {
						html:theCode,
						update:true,
						et_id:$("#exacttarget_id").val()
					}
			}).done(function(data) {
					console.log(data);
			});
    });
	$(".type").click(function(){
		console.log($(this).is("#type_htmlpaste"));

		if($(this).is("#type_htmlpaste") &&  !$(".hidden").is(":visible")){
			$(".hidden").fadeIn();
		}else{
			$(".hidden").fadeOut();
		}
		});
var folderID;




    $('#minimize_control_panel').click(function() {
        var $cPanel = $(this).parent();
        if ($cPanel.hasClass('open')) {
            $cPanel.removeClass('open').addClass('minimized');
            $(this).find('.fa').removeClass('fa-chevron-down').addClass('fa-chevron-up');
        } else {
            $cPanel.removeClass('minimized').addClass('open');
            $(this).find('.fa').removeClass('fa-chevron-up').addClass('fa-chevron-down');
        }



    });

    //CONTROL PANEL
    $("#viewport_height").slider({
				range: "min",
				orientation: "vertical",
			  min: 0,
        max: 250,
        step: 5,
        slide: function(event, ui) {
            //$( "#amount" ).val( ui.value );

					$('.cr-viewport').css({
							'height': ui.value
					});
					$('.viewport_height_value').text(ui.value);

        }
    });



    //$('.blockme').sortable();
    $("#resize_control_panel").resizable({ //on resize, check cpanel width and change image size
        handles: "w"
    });


    $(document).delegate('#send-html-form', 'submit', function(event) {
        event.preventDefault();

        //$('#htmlform').submit();
        var $html = $("#send-html").html();
        formData = $(this).serialize();
        //console.log($html);
        $.ajax({
            type: "POST",
            dataType: "text",
            url: "/send",
            data: {html:$html, formData:formData}
        }).done(function(data) {

          console.log(data);

        });
    });

    $('#clearContainer').click(function() {
        $('#container').empty();
    });
    //don't forget, this adds the google analytics content to the right column for the events module
    $('body').on('change', 'input[name="title[]"]', function() {
        //if tagged == yes
        var item = $(this);
        var content = item.val();
        console.log(content);
        item.parent().siblings().find('input[name="ad_content[]"]').val(
            content.toLowerCase().replace(/&|\u0027|\u2019|\u2018|and|,|:|\.|!|\u2013|\u2014|\u002d/g, "").replace(/san diego/g, "sd").replace(/[ ]/g, "_").replace(/__/g, "_")
						);
    });

    $('body').on('click', '.fa-italic', function() {
			var $dates2 = $(this).closest(".contentarea_container").find(".dates2");
			if($dates2.parent("em").length){
				$dates2.unwrap("</em>");
			}else{
				$dates2.wrap("<em>");
			}

		});

    $('body').on('click', '.fa-external-link, .link', function(e) {
			e.preventDefault();
			var $me = $(this);
			var $cont = $me.closest(".contentarea_container");
			var $type = $me.data('linktype');
			switch($type){
				case "onecol":
					$cont.prepend('<input autofocus data-linktype="'+$type+'" class="editing img-input"/>');
					break;
				case "twocol-left":
					$cont.prepend('<input autofocus data-linktype="'+$type+'" class="editing img-input left"/>');
					break;
				case "twocol-right":
					$cont.prepend('<input autofocus data-linktype="'+$type+'" class="editing img-input right"/>');
					break;
				case "fourcol":
					//get position
					var $pos = $me.data("position");
					$cont.prepend('<input autofocus data-linktype="'+$type+'" data-position="'+$pos+'" class="editing img-input"/>');
					break;
				case undefined:
					if($me.hasClass("link")){
						var $link = $(this).closest("a").attr("href");
						var $type = $me.data('linktype');
						$(this).removeClass('linked');
						$(this).closest('.blockme, .first_buffer_row, .pasted, .linkinput').prepend('<input autofocus class="editing link-input"/>');
						$('.editing').val($link).focus();
						$(this).addClass('linking');
					}
					break;
				default:
					$me.closest('.blockme').prepend('<input autofocus class="editing img-input"/>');
					break;
			}

    });

    $('body').on('click', '.editable', function(event) {

        event.preventDefault();
        var $content = $(this).text();

        if (!$(this).hasClass('editing')) {
            if ($(this).hasClass('textarea')) {
                $(this).wrap('<textarea autofocus class="editing"/>');

            } else {
                $(this).wrap('<input autofocus class="editing"/>');
            }
            $(this).closest(".editing").val($content);
        }
    });
    $('body').on('click', '.editing', function(event) {
        event.preventDefault();
    });
    $('body').on('keydown', '.editing', function(e) {


        if (e.which == 9) { //9 is tab key, 13 is enter key
						var $me = $(this);
            var $type = $me.data('linktype');
						var $newValue = $me.val();
						switch($type){
							case "onecol":
								$me.closest(".contentarea_container").find(".editableImage").attr("src",$newValue);
								break;
							case "twocol-left":
								$me.closest(".contentarea_container").find(".editableImage.left").attr("src",$newValue);
								console.log("wtf left");
								break;
							case "twocol-right":
								$me.closest(".contentarea_container").find(".editableImage.right").attr("src",$newValue);
								console.log("wtf-right");
								break;
							case "fourcol":
								var $pos = $me.data("position");
								$me.closest(".contentarea_container").find(".editableImage."+$pos).attr("src",$newValue);
							case "img-input":
							case "link-input":
							default:break;
						}


            if ($me.hasClass('link-input')) {

                //decided to only tag if a campaign is selected	we don't rely enough on tagging to make
                //it important to tag every email and it would overcomplicate this app
                var $template = $('#template').val();

                if ($template == "tttd") {
                    $newValue = tagForGA($newValue, $template);
                } else if ($template == "monthly") {

                }
                $me.closest('.blockme, .numbered-list-item, .pasted').find('a').attr('href', $newValue);
                $('.linking').addClass('linked').removeClass('linking');
                $me.remove();

            } else if ($me.hasClass('img-input')) {
               // $me.closest('.blockme').find('.editableImage').attr('src', $newValue);
                $me.remove();
						} else if ($(this).hasClass('img-input2')) {
                $me.closest('.imagecont').find('.editableImage').attr('src', $newValue);
                $me.remove();
            } else {
                if ($me.find('span').is('.maintitle, .date, .eventname, .dates, .section_title, .dates2')) {
                    $newValue = $newValue.toUpperCase();
                }
                $me.find('span').removeClass('unedited').html($newValue.replace(/(?:\r\n|\r|\n)/g, '<br />')).unwrap();
                return false;
            }

        }

    });


    $('body').on('click', '.addEvent', function(event) {
        var $dad = $(this).closest(".contentarea_container");
				var $numEvents = $dad.find('.event').length;
        var isOdd = ($numEvents % 2) == 1;

        if (isOdd) {
            $.get("layouts/events/lyt-singleEvent.html", function(data) {

                $dad.find('.eventcal tr:last-child .blockme:last-child').html(data);
            })
        } else {
            $.get("layouts/events/lyt-eventRow.html", function(data) {
                $dad.find('.eventcal >tbody').append(data);
            })
        }

    });

    $('body').on('click', '.removeEvent', function(event) {

        var $dad = $(this).closest(".contentarea_container");
				var $numEvents = $dad.find('.event').length;
        var isOdd = ($numEvents % 2) == 1;
        var $lastTD = $dad.find('.eventcal tr:last-child .blockme:last-child');
        console.log($lastTD.hasClass('right'));
        if ($lastTD.hasClass('empty')) {
            $lastTD.parent().remove();
        } else {

            $lastTD.empty().addClass('empty');
        }

    });
    $('body').on('click', '.addRow', function(event) {
        var $dad = $(this).closest(".contentarea_container").find('.numbered-list-container');
        var $numRows = $dad.find('.numbered-list-item').length;
        console.log($dad);
        $.get("layouts/numbered-list/lyt-number-row.html", function(data) {
            $dad.find('.numbered-list-item').last().after(data);
            $dad.find('.number').last().text($numRows + 1);
        })


    });
    $('body').on('click', '.removeRow', function(event) {
        var $dad = $(this).closest(".contentarea_container").find('.numbered-list-container');
        var $last = $dad.find('.numbered-list-item').last().remove();
    });
    $('body').on('click', '.ltblue', function(event) {
        $(this).closest(".contentarea_container").find('.fullpad').attr('bgcolor', '#dbe7ef').attr('background', 'none');

    });
    $('body').on('click', '.white', function(event) {
        $(this).closest(".contentarea_container").find('.fullpad').attr('bgcolor', '#ffffff').attr('background', 'none');

    });
    $('body').on('click', '.orange', function(event) {
        $(this).closest(".contentarea_container").find('.fullpad').attr('bgcolor', '#fddea6').attr('background', 'http://image.exct.net/lib/fe6e15707166047a7715/m/1/sdta_nl_small_texture_tan.jpg');

    });
 		$('body').on('click', '.toggle', function(event) {
				var $main = $(this).closest(".contentarea_container");
				var $toggled = $main.hasClass("toggled");
				if($toggled == false){
					$(this).removeClass("fa-circle-o").addClass("fa-circle");
					$main.addClass("toggled");
					$main.find(".section_title").text("INDUSTRY TRENDS");
					$main.find("img").attr("src","http://image.updates.sandiego.org/lib/fe9e15707566017871/m/4/hotel-icon-modern.gif");
					$main.find(".insert").html('\
					Updated research reports are available <a href="http://www.sandiego.org/research">online</a> including:\
						<ul>\
							<li>5-year Travel Forecast</li>\
							<li>Visitor Profile Summary (Leisure & Business Overnight Travelers)</li>\
							<li>Visitor Industry Performance</li>\
						</ul>\
          Weekly Lodging Performance\
					');

				}else{
					$(this).removeClass("fa-circle").addClass("fa-circle-o");
					$main.find(".section_title").text("UPCOMING EVENTS");
					$main.removeClass("toggled");
					$main.find("img").attr("src","http://www.mylittleemailbuilder.com/images/MyLEB-placeholder_127x105.jpg");
					$main.find(".insert").html("");
				}
				console.log($toggled);

		});
 		$('body').on('click', '.toggle_sponsored', function(event) {
			var $me = $(this).closest(".contentarea_container").find(".sponsored");
			if($me.hasClass("on")){
				$me.text(" ");
				$me.removeClass("on");
			}else{
				$me.text("SPONSORED");
				$me.addClass("on");
			}
		});
 		$('body').on('click', '.remove_headline', function(event) {
			var $me = $(this);
			$me.closest(".contentarea_container").find(".headlinebar").remove();
			$me.siblings(".toggle_sponsored").remove();
		});



		$(".fa-calendar").click(function() {
       $(".eventDatepicker").show();
    });

      $('body').delegate('.eventDatepicker', 'click', function(event) {
				$(this).parent().find(".date").addClass("active");
				$(this).datepicker(
				"dialog",
				"",
				function(v){
					//var cleanDate = v.split("/");
					//v = v.replace(/\//g, "\n");
					$(".active").html(v.toUpperCase()).removeClass("active");
				},
				{
					minDate: -1,
					showButtonPanel: true,
					dateFormat: "M<br>dd"
				}
				);
				//$(this).datepicker( "dialog", "10/12/2012" );

    });
var $r1, m1, d1, date1;
     $('body').delegate('.eventDatepickerRange', 'click', function(event) {
			 event.preventDefault();
				$(this).parent().find(".date").addClass("active");
				$(this).datepicker(
				{
					minDate: -1,
					showButtonPanel: true,
					onSelect:function(v){
						//console.log(m);
						var dPickerDate = $(this).datepicker( "getDate" );
						if($r1 == undefined){
							var dTime = d.getTime();
							m1 = monthNames[dPickerDate.getMonth()];
							d1 = dPickerDate.getDate();
							var vTime = dPickerDate.getTime();
							console.log(vTime<dTime);
							if(vTime<dTime){
								date1 = "THRU";
							}else{
								date1 = m1.toUpperCase() + " " + d1;
							}

							$r1 = v;

						}else{

							var m2 = monthNames[dPickerDate.getMonth()];
							var d2 = dPickerDate.getDate();
							if(m1 != m2 || date1 == "THRU"){
								//different months

								var string = date1 + "<br>-" + m2.toUpperCase() + " " + d2;
								$(".active").html(  string ).removeClass("active");
							//	$(this).find(".ui-datepicker").hide();
							}else{
								//same month
								var string = m2.toUpperCase();
								string+= "<br>" + d1 + "-" + d2;
								$(".active").html(  string ).removeClass("active");
								//$(this).hide();
							}

						$(".eventDatepickerRange").datepicker("destroy");
						$r1 = undefined;

						}
						console.log(v);
					}
				}
				);
				//$(this).datepicker( "dialog", "10/12/2012" );
    });

//SCREENSHOT STUFF


//PR functions
//$('#richtext_editor').trumbowyg();
	$('body').on('change', '#addSignature input', function(e) {
		var $name = e.target.name;

		var $dad = $(this).closest(".contentarea_container");
		var $numSigs = $dad.find('.signature').length;
		var isOdd = ($numSigs % 2) == 1;
		if (isOdd) {
				$.get("layouts/signatures/sig-"+$name+".html", function(data) {
						$dad.find('.eventcal tr:last-child .blockme:last-child').html(data);
				})
		} else {
				$.get("layouts/signatures/lyt-sigRow.html", function(data) {
					$dad.find('.eventcal >tbody').append(data);
					$.get("layouts/signatures/sig-"+$name+".html", function(data) {
							$dad.find('.eventcal tr:last-child .blockme:first-child').html(data);
					});

				})
		}




		//var $nextEmpty = $(this).closest("contentarea_container").find(".empty");

	});





	 		$('body').on('click', '.fa-floppy-o', function(event) {
				//show alert box for name input

				//save name input to variable

				//ajax to savemodule.php send variable

				//sql query in savemodule.php saves to DB

				//response says all OK or not OK
				$(this).closest(".contentarea_container").addClass("saving");

				$("#loadContent").html('<form id="save_module" class="moduleform" title="tttdform" name="tttdform" action="layouts/events/make-layout.php" method="post"><input type="text" class="save_module"><input type="submit" value="Save Module" class="button"></form>');
				$('#load').dialog('open');
				/*
				 dialog = $( "#dialog-form" ).dialog({
						autoOpen: false,
						height: 400,
						width: 350,
						modal: true,
						buttons: {
							"Create an account": addUser,
							Cancel: function() {
								dialog.dialog( "close" );
							}
						},
						close: function() {
							form[ 0 ].reset();
							allFields.removeClass( "ui-state-error" );
						}
					});
 				 */


			});
		$('body').on('click', '.open-library', function(event) {

			console.log("wtf");
				$.ajax({
            type: "POST",
            dataType: "text",
            url: "get_module.php"
        }).done(function(data) {


								$('#loadContent').html('<div style="width:600px;transform:scale(.6) translate(-35%,-35%);">'+data+'</div>');
								$('#load').dialog('open');




				});
		});


		$("#themeSwitcher").click( function(){
      console.log(localStorage.getItem("theme"));
      if(!localStorage.getItem("theme")){
        localStorage.setItem("theme","classic");
      }
      location.reload();
    });















//SHAREABLE STUFF BELOW

    $("#datepicker").datepicker();


    $('#slider').slider({
        min: 0,
        max: 1440,
        step: 15,
        slide: function(event, ui) {
            var hours = Math.floor(ui.value / 60);
            var minutes = ui.value - (hours * 60);

            if (hours.toString().length == 1) hours = '0' + hours;
            if (minutes.toString().length == 1) minutes = '0' + minutes


            $('#time').val(hours + ':' + minutes);

        }

    });


function celebrityName (firstName) {
    var nameIntro = "This celebrity is ";
    // this inner function has access to the outer function's variables, including the parameter​
   function lastName (theLastName) {
        return nameIntro + firstName + " " + theLastName;
    }
    return lastName;
}
var mjName = celebrityName ("Michael");//At this juncture, the celebrityName outer function has returned.​

});
