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
/*
    var $container = $("#container");

    if ($container.hasClass("new_project")) {

        var $tmp = $container.data("template");
        $.get("templates/" + $tmp + ".html", function(data) {

            $container.append(data);
            $container.removeClass("new_project");
            $(".senddate").text(fullMonthName + " " + nextFriday + ", " + year);


        });

    }
    */




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



    $('.sortable').sortable();
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


    $("body").on("click",".add-another",function(){
      var $me = $(this);
      var $type = $me.attr("data-addType");
      if($type == "row"){
        var $dad = $me.closest(".row");
        $dad.clone().insertAfter($dad);
      }
      console.log($type);
    });


tinymce.init({
  selector:'.tinymce' ,
  toolbar: 'code newdocument bold italic underline strikethrough alignleft aligncenter alignright alignjustify styleselect formatselect fontselect fontsizeselect cut copy paste bullist numlist outdent indent blockquote undo redo removeformat subscript superscript',
  plugins: 'code paste'
});
    //SHAREABLE STUFF BELOW

    $("#datepicker").datepicker();

    $('#slider').slider({
        min: 0,
        max: 720,
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
