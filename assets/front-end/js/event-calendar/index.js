jQuery(window).on('elementor/frontend/init', function () {
    elementorFrontend.hooks.addAction('frontend/element_ready/eael-event-calendar.default', function ($scope, $) {
        
        var element = $scope.find('.eael-event-calendar-cls');
        var eventAll = element.data('events');
        var daysWeek = element.data('days_week');
        var eaelevModal = document.getElementById("eaelecModal");
        var eaelevSpan = document.getElementsByClassName("eaelec-modal-close")[0];
        //daysWeek1 = ['s1', 's2'];
        //alert(daysWeek);
        var calendar = $('#eael-event-calendar').fullCalendar({
            editable:true,
            header:{
            left:'prev,next today',
            center:'title',
            right:'month,agendaWeek,agendaDay'
            },
            events: eventAll,
            selectable:true,
            selectHelper:true,
            dayNamesShort: daysWeek,
            eventRender: function (event, element) {
                element.attr('href', 'javascript:void(0);');
                element.click(function() {
                    eaelevModal.style.display = "block";
                    /*
                    $("#startTime").html(moment(event.start).format('MMM Do h:mm A'));
                    $("#endTime").html(moment(event.end).format('MMM Do h:mm A'));
                    */
                    $(".eaelec-modal-header h2").html(event.title);
                    $(".eaelec-modal-body p").html(event.description);
                    $(".eaelec-modal-footer a").attr('href', event.url);
                    
                });
            }
        });

        // When the user clicks on <span> (x), close the modal
        eaelevSpan.onclick = function() {
            eaelevModal.style.display = "none";
        }
    });
});