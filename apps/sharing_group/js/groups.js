var $userGroupList,
    $GroupListLi,
	$sortGroupBy;

var GroupList;
GroupList = {
	activeGID: '',
	everyoneGID: '_everyone',
    groups: [],

   elementBelongsToAddGroup: function (el) {
		return !(el !== $('#newgroup-form').get(0) &&
		$('#newgroup-form').find($(el)).length === 0);
	},

    hasAddGroupNameText: function () {
		var name = $('#newgroupname').val();
		return $.trim(name) !== '';

	},

    
    showGroup: function (gid) {
		console.dir(gid);
        GroupList.activeGID = gid;
		UserList.empty();
        UserList.update(gid);
		$userGroupList.find('li').removeClass('active');
		if (gid !== undefined) {
			//TODO: treat Everyone properly
			GroupList.getGroupLI(gid).addClass('active');
		}
	},
    
	isAddGroupButtonVisible: function () {
		return $('#newgroup-init').is(":visible");
	},

	toggleAddGroup: function (event) {
		if (GroupList.isAddGroupButtonVisible()) {
			event.stopPropagation();
			$('#newgroup-form').show();
			$('#newgroup-init').hide();
			$('#newgroupname').focus();
			GroupList.handleAddGroupInput('');
		}
		else {
			$('#newgroup-form').hide();
			$('#newgroup-init').show();
			$('#newgroupname').val('');
		}
	},

	handleAddGroupInput: function (input) {
		if(input.length) {
			$('#newgroup-form input[type="submit"]').attr('disabled', null);
		} else {
			$('#newgroup-form input[type="submit"]').attr('disabled', 'disabled');
		}
	},

	isGroupNameValid: function (groupname) {
		if ($.trim(groupname) === '') {
			OC.dialogs.alert(
				t('Sharing_Group', 'A valid group name must be provided'),
				t('Sharing_Group', 'Error creating group'));
			return false;
		}
		return true;
	},
    
    /* 
	hide: function (gid) {
		GroupList.getGroupLI(gid).hide();
	},
	show: function (gid) {
		GroupList.getGroupLI(gid).show();
	},
	
    remove: function (gid) {
		GroupList.getGroupLI(gid).remove();
	},
	empty: function () {
		$userGroupList.find('.isgroup').filter(function(index, item){
			return $(item).data('gid') !== '';
		}).remove();
	},
	*/
    
    /**
	 * Edit a groups name.
	 * 
	 * @param object $element jQuery element
	 */
    editGroup: function($element) {
        //console.dir($element);
        var self = this;
		var oldname = $element.text();
		var id = $element.data('gid');
		//var owner = $element.data('owner');
        //console.dir(oldname);
        //console.dir(id);

		var $editInput = $('<input type="text" />').val(oldname).attr({ id:'editInput'});
        var button = $('<button>').attr({class:'new-button primary icon-checkmark-white', style:'display: block', id:'renamebutton'});
        var group_editing = $('<li>').attr({class:'group editing'});
        
        $element.hide();
        $editInput.insertBefore($element).wrap(group_editing);
        group_editing.append(button);
        $('.group.editing').append(button);
        var $tmpelem = $editInput.parent('li');
        $editInput.focus();
        /*
        if($editInput.val() != oldname && $.inArray($editInput.val(), GroupList.groups) >-1){
            console.dir(1);
        }
        */

        $('#usergrouplist').on('keyup', '#editInput', function(event) {
                
                if($.inArray($editInput.val(), GroupList.groups) > -1) {
                    
                    $('#editInput').addClass("ui-status-error");

                }
                else {
                    $('#editInput').removeClass("ui-status-error");
                }

                if(event.which == $.ui.keyCode.ESCAPE) {
                    $tmpelem.remove();
                    $element.show();
                }
                
                if(event.which == $.ui.keyCode.ENTER && !$('#editInput').hasClass('ui-status-error')) {
                    var newname = $editInput.val();
                    $.post(
                        OC.generateUrl('/apps/sharing_group/renameGroup'),
                        {
                            gid: id,
                            newname: newname
                        },
                        function (result) {
                            $element.find('.groupname').text(newname);
                            $tmpelem.remove();
                            $element.show();
                        });
                }
        });
        
        $('#renamebutton').click(function() {
            var newname = $editInput.val();
            if(!$('#editInput').hasClass('ui-status-error'))
            $.post(
			    OC.generateUrl('/apps/sharing_group/renameGroup'),
			    {
				    gid: id,
                    newname: newname
			    },
			    function (result) {
                    $element.find('.groupname').text(newname);
                    $tmpelem.remove();
                    $element.show();
                });
        });
        
        $(document).on('click', function(event) {
            if(event.target.parentElement.className != 'group editing'){
                event.stopPropagation();
			    event.preventDefault();
                $tmpelem.remove();
                $element.show();
                $(document).off('click');
            }
        });
        
   	},

    getGroupLI: function (gid) {
		return $userGroupList.find('li.isgroup').filter(function () {
			return GroupList.getElementGID(this) === gid;
		});
	},
    
    getElementGID: function (element) {
		return ($(element).closest('li').data('gid') || '').toString();
	},

    addOption: function(id) {
        var option = $('<option>').attr({'value':id});

        return option;
    },

    addLi: function(id, name){
        var li = $('<li>').attr({'data-gid':id , class:'isgroup'});
        var group = $('<a>')
        var groupname = $('<span>').attr({class:'groupname'});
        var util = $('<span>').attr({class:'utils'});
        var usercount = $('<span>').attr({class:'usercount'});
        var action_delete = $('<a>').attr({class:'icon-delete action delete ', original_title:'刪除'});
        var action_rename= $('<a>').attr({class:'icon-rename action rename '});

        group.append(groupname.text(name));
        util.append(action_delete);
        util.append(action_rename);
        util.append(usercount);
        li.append(group);
        li.append(util);

        return li;
    },
    
    findGroupByName: function(groupname){
       	$.post(
			OC.generateUrl('/apps/sharing_group/findID'),
			{
				name: groupname
			},
			function (result) {
                console.dir(result);
                if (typeof result != 'undefined') {
				        $GroupListLi.after(GroupList.addLi(result[0], groupname));
                        GroupList.sortGroups();
                }
		});

    },

    showGroupList: function () {
		$.get(
			OC.generateUrl('/apps/sharing_group/fetch'),
			function (result) {
			    $.each(result, function (index, value) {
                    GroupList.groups.push(value.name);
                    $GroupListLi.after(GroupList.addLi(value.id, value.name));
                    $groupsselect.append(GroupList.addOption(value.id).text(value.name) )
                    GroupList.sortGroups();
                });
			}
		);
	},

    createGroup: function (groupname) {
		$.post(
			OC.generateUrl('/apps/sharing_group/create'),
			{
				name: groupname
			},
			function (result) {
				if (result) {
                    GroupList.findGroupByName(groupname);			        
                }
                else{
				    OC.dialogs.alert('Group already exists', 'Error create group');
                }
				GroupList.toggleAddGroup();
		});
	},

    sortGroups: function () {
		var lis = $userGroupList.find('.isgroup').get();

		lis.sort(function (a, b) {
			// "Everyone" always at the top
			if ($(a).data('gid') === '_everyone') {
				return -1;
			} else if ($(b).data('gid') === '_everyone') {
				return 1;
			}

			if ($sortGroupBy === 1) {
				// Sort by user count first
				var $usersGroupA = $(a).data('usercount'),
					$usersGroupB = $(b).data('usercount');
				if ($usersGroupA > 0 && $usersGroupA > $usersGroupB) {
					return -1;
				}
				if ($usersGroupB > 0 && $usersGroupB > $usersGroupA) {
					return 1;
				}
			}

			// Fallback or sort by group name
			return UserList.alphanum(
				$(a).find('a span').text(),
				$(b).find('a span').text()
			);
		});

		var items = [];
		$.each(lis, function (index, li) {
			items.push(li);
			if (items.length === 100) {
				$userGroupList.append(items);
				items = [];
			}
		});
		if (items.length > 0) {
			$userGroupList.append(items);
		}
	},
    
    deleteGroup: function (id){
        $.post(
	        OC.generateUrl('/apps/sharing_group/deleteGroup'),
			{
				gid: id
			},
			function (result) {
		    
        });
 
    }
};

$(document).ready( function () {
	$userGroupList = $('#usergrouplist');
	$GroupListLi = $('#usergrouplist #everyonegroup');
	GroupList.showGroupList();
	
    // Display or hide of Create Group List Element
	$('#newgroup-form').hide();
	$('#newgroup-init').on('click', function (e) {
		GroupList.toggleAddGroup(e);
	});
    
    
	$(document).on('click', function(event) {
       
        if(!GroupList.isAddGroupButtonVisible() &&
			!GroupList.elementBelongsToAddGroup(event.target)) {
			GroupList.toggleAddGroup();
		}
        
	});
    
    
    $('#newgroupname').keyup(function(event) {
        if($.inArray($('#newgroupname').val(),GroupList.groups) > -1) {
            $('#newgroupname').addClass("ui-status-error");
        }
        else {
            $('#newgroupname').removeClass("ui-status-error");
        }
        
        if(!GroupList.isAddGroupButtonVisible() && event.keyCode === $.ui.keyCode.ESCAPE) {
			GroupList.toggleAddGroup();
		}

    });
/* 
    $(document).on('click', function(event) {
        console.dir(event);
        if(event.target.parentElement.className != 'group editing'){
            //event.stopPropagation();
            //event.preventDefault();
            //$('#newgroup-form').remove();
            //$('#newgroup-init').show();
            GroupList.toggleAddGroup();
            $(document).off('click');
        }
    });
  */  

	// Responsible for Creating Groups.
	$('#newgroup-form form').submit(function (event) {
		//event.preventDefault();
        if(GroupList.isGroupNameValid($('#newgroupname').val()) && !$('#newgroupname').hasClass('ui-status-error')) {
			GroupList.createGroup($('#newgroupname').val());
        }
	});

	// click on group name
	$userGroupList.on('click', 'li.isgroup', function (event) {
        if($(event.target).is('.action.delete')) {
            //$('li.isgroup.active').removeClass('active').addClass('load');
			var id = $(this).find('a').closest('li').data('gid');
            var groupname = $(this).find('.groupname').text();

            OC.dialogs.confirm('Are you sure delete group'+ ' ' + groupname, 'Sharing_Group',
                function(result){
                    if(result == true) {
                        GroupList.deleteGroup(id);
                        $('.isgroup.active').remove();
                    }
                }, true
            
            );
            

            /*
            $('.tipsy').remove();
			$(this).addClass('loading').removeClass('active');
			event.stopPropagation();
			event.preventDefault();
			var id = $(event.target).parents('li').first().data('id');
			self.deleteGroup(id, function(response) {
				if(response.error) {
					OC.notify({message:response.message});
				}
			});
            */
        } else if($(event.target).is('.action.rename')) {
            event.stopPropagation();
			event.preventDefault();
            GroupList.editGroup($(this));
        } else {
            //console.dir($(this).data('gid'));
            GroupList.showGroup(GroupList.getElementGID($(this)));
            //GroupList.showGroup($('li.isgroup.a').data('gid'));
	    
        }
    });
    
	$('#newgroupname').on('input', function() {
		GroupList.handleAddGroupInput(this.value);
	});
    
    $('.export').click(function() {
        //document.location.href = OC.generateUrl('/apps/sharing_group/export');
        
        //var form = $('<form>').attr({action: '/owncloud/download.php', method: 'GET'});
        var form = $('<form>').attr({action: OC.generateUrl('/apps/sharing_group/export'), method: 'GET'});

        form.trigger('submit');

        //document.location.href = OC.generateUrl('/apps/sharing_group/download');
    });
    
    $('.import').click(function(){
        $('#upload').trigger('click')
        
    });
    $('#upload').fileupload({
        url: OC.generateUrl('/apps/sharing_group/importGroup'),
    });
    /*$('#upload').change(function(event){
        var file = event.target.files[0];
        var files= this.files[0]; 
        console.dir(typeof( event.target.files[0]));
        console.dir(typeof(files ));
        console.dir(files);
        console.dir(this);
        //var fd = new FormData();
        //fd.append("fileToUpload", file);

        $.ajax({
            url: OC.generateUrl('/apps/sharing_group/importGroup'),
            type: 'POST',
            
            beforeSend: function (xhr) {
                xhr.setRequestHeader('Content-Type','multipart/form-data');
            },
            
            //headers: { 'Content-Type': 'multipart/form-data' },
            cache: false,
            contentType: false,
            processData: false,
            //data: fd
        });
        $.post(
	        OC.generateUrl('/apps/sharing_group/importGroup'),
			{
				data: files
			}
        );

    });*/
        
});
