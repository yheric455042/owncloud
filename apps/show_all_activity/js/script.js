/**
 * ownCloud - myapp
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Your Name <mail@example.com>
 * @copyright Your Name 2015
 */

(function ($, OC) {
    var appname = 'show_all_activity';
    var activity = {
        modal: $('<div>'),
        footer: $('<div>').attr({class: 'footer'}),
        loader: $('<div>').attr({class: 'loading-activity'}),
        message: $('<p>').attr({class: 'message'}),
        more_btn: $('<button id="more">').text(t(appname, 'more')),
        finished: false,
        currentPage: 1,
        user: null
    };

    activity.generateView = function() {
        var th = $('<th>').append(t(appname, 'Actions'));

        $('#userlist thead tr #headerRemove').before($('<th>').append(th));
        
        $('#userlist  tbody tr:hidden').each(function() {
            var btn = $('<button class="activity">').text(t(appname, 'Show Activity'));

            $('#userlist  tbody tr:hidden .remove').before($('<td>').append(btn));
        });
            
        activity.footer.append(activity.loader);
        activity.footer.append(activity.message);
        activity.footer.append(activity.more_btn);
        activity.modal.append(activity.footer);
    };

    activity.settings = {
        modal: true,
        width: 400,
        height: 400,
        draggable: false,
        resizable: false
    };

    activity.reset = function() {
        activity.message.hide();
        activity.more_btn.show();
        activity.modal.find('.activity-section').remove();
        activity.currentPage = 1;
        activity.finished = false;
    };

    activity.getData = function(user, currentPage) {
        activity.loader.show();

        return $.ajax({
            method:'GET',
		    url: OC.generateUrl('/apps/show_all_activity/fetch'),
		    data: {
                user: user, 
                page: currentPage
            }
	    });
    };

    activity.transformData = function(result, modal) {
        modal.find('.footer').before(result);
        modal.find('.box a').not('.filename').remove();
        modal.find('.activitysubject a').replaceWith(function(){
            return $('<b class="filename">').append($(this).text());
        });
    };

    activity.transformTooltip = function(modal) {
        var tooltip = modal.find('.boxcontainer .activitysubject').filter(function(){
            return $(this).find('.tooltip').length ? $(this) : 0;
        });

        tooltip.each(function() {
            var current_tooltip = $(this);
            var more = $(this).find('.tooltip').attr('title').split(/[,\s?|，\s?]/g);

            more.forEach(function(v,index) {
                (more.length - index > 1 && v) && current_tooltip.find('.filename').last().after($('<b class="filename">').append(', ' + v));
            });

            current_tooltip.find('.tooltip').replaceWith($('<b class="filename">').append(more[more.length - 1]));
        });
    };

    activity.finish = function(message) {
        activity.finished = true;
        activity.message.text(t(appname, message));
        activity.more_btn.hide();
        activity.message.show();
    };

    activity.more = function() {
        if(activity.loader.is(':visible')){ return; }    
         
        if(activity.finished){ return; }

        activity.currentPage++; 

        activity.getData(activity.user, activity.currentPage).done(function(result){

            result === '' && activity.finish('No more activity.');

            activity.transformData(result, activity.modal);
            activity.transformTooltip(activity.modal);
            activity.loader.hide();
        });
    };

	$(function() {

        activity.generateView();

		$('#userlist tbody').delegate('.activity', 'click', function() {
            $.ajax({url: OC.filePath('show_all_activity','ajax','test.php'),dataType: 'json'}).done(function(result){console.dir(result)});
            activity.reset();
            activity.user = $(this).closest('tr').find('.name').text();
            activity.loader.show();

	   	    activity.getData(activity.user, activity.currentPage).done(function(result) {
                var options = $.extend({title: activity.user}, activity.settings);

                activity.loader.hide();
                activity.modal.dialog(options);

                result === '' && activity.finish('There is no activity.');

                activity.transformData(result, activity.modal);
                activity.transformTooltip(activity.modal);
            });
		});

         activity.modal.scroll(function() {
            if($(this).scrollTop() === 0){ return; }

            activity.more_btn.hide(); //The more button should be hidden when modal has scrollbar

            ($(this).scrollTop() + $(this).innerHeight() >= this.scrollHeight - 20) && activity.more();
         });

         activity.more_btn.click(function() {
            activity.more();
         });
	});
})(jQuery, OC);
