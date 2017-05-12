// JavaScript Document
//my code
function pad(n) {
    return (n < 10) ? ("0" + n) : n;
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

$(document).ready(function() {

    var $container = $("#container");

    if ($container.hasClass("new_project")) {

        var $tmp = $container.data("template");
        $.get("templates/" + $tmp + ".html", function(data) {

            $container.append(data);
            $container.removeClass("new_project");
            $(".senddate").text(fullMonthName + " " + nextFriday + ", " + year);


        });


    }
    function processData(csv) {
        var allTextLines = csv.split(/\r\n|\n/);
        var lines = [];
        for (var i=0; i<allTextLines.length; i++) {
            var data = allTextLines[i].split(';');
                var tarr = [];
                for (var j=0; j<data.length; j++) {
                    tarr.push(data[j]);
                }
                lines.push(tarr);
        }
      console.log(lines);
    }


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
    var $uploadLocation = 'myleb_folder';
    $(document).delegate('.drop-area', 'drop', function(e) {
      e.preventDefault();
      $(this).css('border', 'none');
      //get type of file dropped
      var $me = $(this);

      var reader = new FileReader();
      var csv = e.originalEvent.dataTransfer.files[0];
      //console.log(csv);
      reader.readAsText(csv);
      //console.log(reader);
      reader.onload = function(e) {
        var file = e.target.result;
        var allTextLines = file.split(/\r\n|\n/);
        var lines = [];
        for (var i=0; i<allTextLines.length; i++) {
            var thisLine = allTextLines[i].split(',');
              console.log(thisLine[0]);
              $('input[name="' + thisLine[0] + '"]').val(thisLine[1]);



                var tarr = [];
                for (var j=0; j<thisLine.length; j++) {
                    tarr.push(thisLine[j]);
                }
                //console.log(tarr);
                lines.push(tarr);
        }


      }
      if($me.hasClass("csv")){

      }else{




        //if($(this).is('.cloudinary')){$uploadLocation = 'cloudinary';}
        //$uploadLocation = 'cloudinary';
        //$(this).css('background', '#D8F9D3');


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
        imageName = new Date().getUTCMilliseconds() + image.name;

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
            console.log('wtf ' + imageName);
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

      }//end file type check
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
                    'uploadLocation': $uploadLocation
                },
                dataType: 'json',
            }).done(function(data) {
                $('#cropbox').fadeOut('slow', function() {
                    //$('.activeImage').attr('src', 'http://image.updates.sandiego.org/lib/fe9e15707566017871/m/4/' + imageName);
                    var $target = $(".activeImage");
                    if ($target.is(":input")) {
                        $target.val(data['url']);
                    } else {
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
        var $html;
        formData = $(this).serialize();
        $.get("ui/email_template_start.htm", function(start) {
            $.get("ui/email_template_end.htm", function(end) {
                $html = start + $("#send-html").html() + end;
                console.log($html);

                //console.log($html);
                $.ajax({
                    type: "POST",
                    dataType: "text",
                    url: "/send",
                    data: {
                        html: $html,
                        formData: formData
                    },
                  dataType: 'json'
                }).done(function(data) {
                    alert(data['statusCode']);
                    console.log(data['results']);

                });//end of ajax call

            });//end of second get email_template_end

        });//end of first get email_template_start

    });//end of submit delegate function






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
  $(".auto-date").prepend("<div class=\"auto-date--cb\"><input type=\"checkbox\"> Use today's date</div>");
  $("body").on("click",".auto-date--cb input",function(){
    var today = new Date();
    var $nextInput = $(this).parent().nextAll("input");
    console.log($nextInput);
    var $cVal = $nextInput.val();
    var $nVal = $cVal + " " + today.getFullYear() + "-" + pad(today.getMonth()) + "-" + pad(today.getDate());
    $nextInput.val($nVal);
  });

});
