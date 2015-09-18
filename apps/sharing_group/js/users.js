var $userList;
//var $userListBody;
var $userListUl;
var filter;
var uid = [];

var UserList = {
	availableGroups: [],
	offset: 0,
	usersToLoad: 10, //So many users will be loaded when user scrolls down
	currentGid: '',


	preSortSearchString: function(a, b) {
		var pattern = filter.getPattern();
		if(typeof pattern === 'undefined') {
			return undefined;
		}
		pattern = pattern.toLowerCase();
		var aMatches = false;
		var bMatches = false;
		if(typeof a === 'string' && a.toLowerCase().indexOf(pattern) === 0) {
			aMatches = true;
		}
		if(typeof b === 'string' && b.toLowerCase().indexOf(pattern) === 0) {
			bMatches = true;
		}

		if((aMatches && bMatches) || (!aMatches && !bMatches)) {
			return undefined;
		}

		if(aMatches) {
			return -1;
		} else {
			return 1;
		}
	},
	
    // From http://my.opera.com/GreyWyvern/blog/show.dml/1671288
	alphanum: function(a, b) {
		function chunkify(t) {
			var tz = [], x = 0, y = -1, n = 0, i, j;

			while (i = (j = t.charAt(x++)).charCodeAt(0)) {
				var m = (i === 46 || (i >=48 && i <= 57));
				if (m !== n) {
					tz[++y] = "";
					n = m;
				}
				tz[y] += j;
			}
			return tz;
		}

		var aa = chunkify(a.toLowerCase());
		var bb = chunkify(b.toLowerCase());

		for (var x = 0; aa[x] && bb[x]; x++) {
			if (aa[x] !== bb[x]) {
				var c = Number(aa[x]), d = Number(bb[x]);
				if (c === aa[x] && d === bb[x]) {
					return c - d;
				} else {
					return (aa[x] > bb[x]) ? 1 : -1;
				}
			}
		}
		return aa.length - bb.length;
	},
    
	checkUsersToLoad: function() {
		//30 shall be loaded initially, from then on always 10 upon scrolling
		if(UserList.isEmpty === false) {
			UserList.usersToLoad = 10;
		} else {
			UserList.usersToLoad = 30;
		}
	},
   
    empty: function() {
		//one row needs to be kept, because it is cloned to add new rows
		$userList.find('li').remove();
		
        UserList.isEmpty = true;
		UserList.offset = 0;
		UserList.checkUsersToLoad();
	},
    

	update: function (gid, limit) {
		if (UserList.updating) {
			return;
		}
		
        if(!limit) {
			limit = UserList.usersToLoad;
		}
		$userList.siblings('.loading').css('visibility', 'visible');
		UserList.updating = true;
		if(gid === undefined) {
			gid = '';
		}
		UserList.currentGid = gid;
		var pattern = filter.getPattern();
		$.get(
			OC.generateUrl('/apps/sharing_group/user'),
			{ offset: UserList.offset, limit: limit, gid: gid, pattern: pattern },
			function (result) {
				
                $.each(result, function (index, value) {
				    var li = $('<li>').attr({class:'users'});
                    var checkbox = $('<input>').attr({type: 'checkbox', id: 'id-' + value});
                    var label = $('<label>').attr({ for: 'id-' + value}).text(value);

                    li.append(checkbox);
                    li.append(label);

                    $userList.append(li);
                });
				if (result.length > 0) {
					$userList.siblings('.loading').css('visibility', 'hidden');
					// reset state on load
					UserList.noMoreEntries = false;
				}
				else {
					UserList.noMoreEntries = true;
					$userList.siblings('.loading').remove();
				}
				UserList.offset += limit;
			}).always(function() {
				UserList.updating = false;
			});
	},
   /* 
    applyGroupSelect: function (element) {
		var checked = [];
		var $element = $(element);
		//var user = UserList.getUID($element);

		if ($element.data('user-groups')) {
			if (typeof $element.data('user-groups') === 'string') {
				checked = $element.data('user-groups').split(", ");
			}
			else {
				checked = $element.data('user-groups');
			}
		}
	
       var checkHandler = null;
		
        if(user) { // Only if in a user row, and not the #newusergroups select
			checkHandler = function (group) {
				if (user === OC.currentUser && group === 'admin') {
					return false;
				}
				if (!oc_isadmin && checked.length === 1 && checked[0] === group) {
					return false;
				}
				$.post(
					OC.filePath('settings', 'ajax', 'togglegroups.php'),
					{
						username: user,
						group: group
					},
					function (response) {
						if (response.status === 'success') {
							GroupList.update();
							var groupName = response.data.groupname;
							if (UserList.availableGroups.indexOf(groupName) === -1 &&
								response.data.action === 'add'
							) {
								UserList.availableGroups.push(groupName);
							}

							// in case this was the last user in that group the group has to be removed
							var groupElement = GroupList.getGroupLI(groupName);
							var userCount = GroupList.getUserCount(groupElement);
							if (response.data.action === 'remove' && userCount === 1) {
								_.without(UserList.availableGroups, groupName);
								GroupList.remove(groupName);
								$('.groupsselect option').filterAttr('value', groupName).remove();
								$('.subadminsselect option').filterAttr('value', groupName).remove();
							}


						}
						if (response.data.message) {
							OC.Notification.show(response.data.message);
						}
					}
				);
			};
		}
		
        var addGroup = function (select, group) {
			$('select[multiple]').each(function (index, element) {
				$element = $(element);
				if ($element.find('option').filterAttr('value', group).length === 0 &&
					select.data('msid') !== $element.data('msid')) {
                    var id = GroupList.findGroupByName(escapeHTML(group) );
                    var option = $('<option>').attr({'value':id});
                    $element.append(option);
				}
			});
			//GroupList.createGroup(escapeHTML(group));
		};
        
		var label;
		if (oc_isadmin) {
			label = t('settings', 'add group');
		}
		else {
			label = null;
		}
    
		$element.multiSelect({
			//createCallback: addGroup,
			//createText: label,
			//selectedFirst: true,
			//checked: checked,
			//oncheck: checkHandler,
			//onuncheck: checkHandler,
			minWidth: 100
		});
        
	},

    
    _onScroll: function() {
		if (!!UserList.noMoreEntries) {
			return;
		}
		if (UserList.scrollArea.scrollTop() + UserList.scrollArea.height() > UserList.scrollArea.get(0).scrollHeight - 500) {
			UserList.update(UserList.currentGid);
		}
	},
    */

};

$(document).ready(function () {
	$userList = $('#userlist');
	$groupsselect = $('.groupsselect');
    //$userListBody = $userList.find('tbody');
    

	// Implements User Search
	filter = new UserManagementFilter($('#usersearchform input'), UserList, GroupList);

//	UserList.availableGroups = $userList.data('groups');

	//UserList.scrollArea = $('#app-content');
	//UserList.scrollArea.scroll(function(e) {UserList._onScroll(e);});

	$userList.after($('<div class="loading" style="height: 200px; visibility: hidden;"></div>'));
    // calculate initial limit of users to load
	var initialUserCountLimit = 20,
		containerHeight = $('#app-content').height();
	if(containerHeight > 40) {
		initialUserCountLimit = Math.floor(containerHeight/40);
		while((initialUserCountLimit % UserList.usersToLoad) !== 0) {
			// must be a multiple of this, otherwise LDAP freaks out.
			// FIXME: solve this in LDAP backend in  8.1
			initialUserCountLimit = initialUserCountLimit + 1;
		}
	}

   /* 
	$groupsselect.each(function (index, element) {
		UserList.applyGroupSelect(element);
	});
    */
    $userList.delegate("input:checkbox","click",function(){

       $(this).hasClass('checked') ? $(this).removeClass('checked') : $(this).addClass('checked');

    });
   
    $('#groupsselect_r').change(function() {
        console.dir($(this).val());
        
        var groupname = $(this).val();
        $userList.find('input:checkbox').each(function() {
            if($(this).hasClass('checked')){
                uid[uid.length] = $(this).closest('li').find('label').text();
            }
        });
        $.post(
			OC.generateUrl('/apps/sharing_group/removeUserFromGroup'),
			{
				gid: groupname,
                uids: uid
			}
        );

    });


    $('#groupsselect').change(function() {
        console.dir($(this).val());
        
        var groupname = $(this).val();
        $userList.find('input:checkbox').each(function() {
            if($(this).hasClass('checked')){
                uid[uid.length] = $(this).closest('li').find('label').text();
            }
        });
        $.post(
			OC.generateUrl('/apps/sharing_group/addUserToGroup'),
			{
				gid: groupname,
                uids: uid
			}
        );

    });


	// trigger loading of users on startup
	UserList.update(UserList.currentGid, initialUserCountLimit);

});
