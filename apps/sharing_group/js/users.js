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
    
    checktristate: function(groupname){
        var checkLength = $('#checkuser').data("checkeduser").length;
        var userLength = $('#checkuser').data("user").length;
        var $tristate = $('#checkuser').tristate();
        
        if(groupname != undefined){ 
            groupLength = $('#usergrouplist').data(groupname).length;
            var $tristate = $('#id-' + groupname);
            var difference = [];
                
            difference = $.grep($('#usergrouplist').data(groupname), function(el) { 
                return $.inArray(el, $('#checkuser').data("checkeduser")) == -1;
            })
            
            //console.dir(difference);
            if(difference.length != groupLength) {
                $tristate.tristate('state', null);
            }
            if(groupLength != 0 && difference.length == 0){
                $tristate.tristate('state', true);
            }
            $tristate.data("origin",$tristate.val());
            console.dir($tristate.data("origin"));
        }
        else {
            if(checkLength == 0) {
                $tristate.tristate('state', false);
            }
         
            if(checkLength == userLength)  {
                $tristate.tristate('state', true);
            }

            if(checkLength != 0) {
                $tristate.tristate('state', null);
            }
        }
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
		//console.dir(UserList.currentGid);
        var pattern = filter.getPattern();
		$.get(
			OC.generateUrl('/apps/sharing_group/user'),
			{ offset: UserList.offset, limit: limit, gid: gid, pattern: pattern },
			function (result) {
                $('#checkuser').data({
                    'user':result ,
                    'checkeduser':[] ,
                    'different': [],
                });

                $.each(result, function (index, value) {
				    var li = $('<li>').attr({class:'users'});
                    var checkbox = $('<input>').attr({type: 'checkbox', id: 'id-' + value, checked:false});
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
};

$(document).ready(function () {
	$userList = $('#userlist');
	$groupsselect = $('.groupsselect');
    //$userListBody = $userList.find('tbody');
         
	// Implements User Search
	filter = new UserManagementFilter($('#usersearchform input'), UserList, GroupList);

    //UserList.availableGroups = $userList.data('groups');

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

    $('#controls').delegate("input:checkbox","click",function(){
        console.dir($(this).data('origin'));
        console.dir($(this).attr('indeterminate')!=undefined)
        if($(this).data('origin') == 'unchecked' && $(this).attr('indeterminate')!=undefined) {
            $(this).tristate('state', false);
        }
    });

    $userList.delegate("input:checkbox","click",function(){
        $(this).prop('checked') ? $(this).attr({'checked':true}) :  $(this).attr({'checked':false}) ;
        var name = $(this).closest('li').find('label').text();
        
        if($(this).prop('checked')) {
           $('#checkuser').data("checkeduser").push(name);
        }
        else {
           var index = $('#checkuser').data("checkeduser").indexOf(name);

           $('#checkuser').data("checkeduser").splice( index , 1);
        }
        UserList.checktristate();
        
        $.each(GroupList.groups, function(index, value) {
            UserList.checktristate(value); 
        });
    
    });
    $('#checkall').click(function() {
         
        $.each($('#checkuser').data("user") , function(index,value) {
            $('#checkuser').data("checkeduser")[index] = value;
            $('#id-' + value).attr({'checked':true});
        });
        UserList.checktristate();
        $.each(GroupList.groups, function(index, value) {
            UserList.checktristate(value); 
        });
    

    });

    $('#clearall').click(function() {
        $.each($('#checkuser').data("checkeduser"), function(index,value) {
            $('#id-' + value).attr({'checked':false});
        });
        $('#checkuser').data("checkeduser",[]);
        UserList.checktristate();
        
        $.each(GroupList.groups, function(index, value) {
            //console.dir(value);
            UserList.checktristate(value); 
        });
    
    });
    
    $('#inverse').click(function() {
        var difference = [];
        difference = $.grep($('#checkuser').data("user"), function(el) { 
            return $.inArray(el, $('#checkuser').data("checkeduser")) == -1;
        })
        $.each($('#checkuser').data("checkeduser") , function(index,value) {
            $('#id-' + value).attr({'checked':false});
        });
        $('#checkuser').data("checkeduser",difference);
        $.each($('#checkuser').data("checkeduser") , function(index,value) {
            $('#id-' + value).attr({'checked':true});
        });
        UserList.checktristate();
        
        $.each(GroupList.groups, function(index, value) {
            //console.dir(value);
            UserList.checktristate(value); 
        });
    });
   
    $('#mutigroupselect').click(function() {
        var mutiGroup = {};
        
        $.each(GroupList.groups, function(index, value) {
            if($('#id-'+value).attr('checked') != undefined) {
                var action = {
                    add: $('#checkuser').data('checkeduser')
                }
                mutiGroup[value] = action;
            }
            if($('#id-'+value).attr('checked') == undefined && $('#id-'+value).attr('indeterminate') == undefined) {
                var action = {
                    remove: $('#checkuser').data('checkeduser')
                }
                mutiGroup[value] = action;
            }
        });
        console.dir(mutiGroup); 
        $.post(
			OC.generateUrl('/apps/sharing_group/controlGroupUser'),
            {
                mutigroup: mutiGroup
            },
            function(result){
            }
            ,'json'
        );
    });
	// trigger loading of users on startup
	UserList.update(UserList.currentGid, initialUserCountLimit);

});
