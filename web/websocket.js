(function($, window) {

    var ws = new WebSocket('ws://stage1:8889');

    ws.onmessage = function(message) {
        var data = $.parseJSON(message.data);

        if (data.data.build) {
            callbacks.build(data.data.build);
        }

        if (data.data.project) {
            callbacks.project(data.data.project);
        }

        if (typeof(callbacks[data.event]) == 'function') {
            callbacks[data.event](data.data);
        }
    };

    var prepare = [
        'nb-pending-builds',
        'build-kill-form'
    ]

    var tpl = {};

    for (i in prepare) {
        var tpl_id = prepare[i];
        if ($('#tpl-' + tpl_id).length > 0) {
            console.log('preparing template', tpl_id);
            tpl[tpl_id] = Mustache.compile($('#tpl-' + tpl_id).text());
        }
    }

    var callbacks = {
        'project': function(project) {
            update_project_nb_pending_builds(project.id, parseInt(project.nb_pending_builds));
        },
        'build': function(build) {
            update(build.id, 'status', function(el) {
                el.removeClass().addClass('label label-' + build.status_label_class).html(build.status_label);
            });

            update(build.id, 'progress', function(el) {
                el.remove();
            });

            update(build.id, 'link', function(el) {
                el.html('<a href="' + build.url + '">' + build.url + '</a>');
            });

            update(build.id, 'kill-form', function(el) {
                if ($('button i', el).hasClass('icon-refresh')) {
                    $('button', el).html('<i class="icon-ok"></i>');
                    setTimeout(function() { el.remove(); }, 1000);                    
                } else {
                    el.remove();
                }
            });

            update(build.id, 'cancel-form', function(el) { el.remove(); });

            if (build.kill_url) {
                update(build.id, 'actions', function(el) {
                    el.append(tpl['build-kill-form'](build));
                });
            }
        }
    };

    function update(build_id, type, callback) {
        callback($('#build-' + build_id + '-' + type));
    }

    function update_project_nb_pending_builds(project_id, nb_pending_builds) {
        var $nbPendingBuilds = $('#nav-project-' + project_id + ' #nb-pending-builds-' + project_id);

        if ($nbPendingBuilds.length == 0) {
            $('#nav-project-' + project_id).append(tpl['nb-pending-builds']({
                project_id: project_id,
                nb_pending_builds: nb_pending_builds
            }));
        } else {
            if (nb_pending_builds > 0) {
                $('span', $nbPendingBuilds).html(nb_pending_builds);                    
            } else {
                $nbPendingBuilds.remove();
            }
        }
    }

})(jQuery, window);