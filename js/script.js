// page scripts

var CertMaker = window.CertMaker || {};

(function (window, $) {
    
    $(document).ready(function () {

        var l = window.CertMaker;

        // setup
        l.ready = l.ready || true;
        l.template_active = l.template_active || false;
        l.template_index = l.template_index || null;
        l.init_data = l.init_data || null;
        l.ajax_response = l.ajax_response || null;

        function undo_select_template() {
            var $elem = $('#select-template');
            $elem.children('option').each(function () {
                if ($(this).val() == l.template_index) {
                    $(this).prop('selected', true);
                } else {
                    $(this).removeAttr('selected');
                }
            });
        } 

        // ajax requester
        var make_ajax_request = function (form, makepdf, success) {
            var data = {
                'request_type': 'get_rendered_theme',
                'template_index': l.template_index,
                'include_widgets': true
            };
            if ('undefined' !== typeof form) {
                data.populate_data = form;
            }
            if (!!makepdf) {
                data = {
                    'request_type': 'make_pdf',
                    'template_index': l.template_index,
                    'pdf_print_url': $.param(data)
                };
                if ('undefined' !== typeof form) {
                    data.populate_data = form;
                }
            }

            $.ajax({
                url: '',
                type: 'POST',
                data: data,
                dataType: 'json',
                success: success || function (resp) {
                    // console.log(resp);
                    if (!resp.error) {
                        l.ajax_response = resp;
                        $('#certificates').html(resp.markup);
                        var widgets = resp.widgets || '';
                        if (widgets) {
                            $('#template-widgets').html(widgets);
                        }
                        $('.submit-button-wrapper').addClass('show');
                    }
                },
                error: function (xhr, t) {
                    console.log(t);
                    console.log(xhr);
                }
            });
        };

        var get_widget_data = function () {
            var widget_data = {};
            $('#widget-form').find('input[name]').each(function () {
                var $inp = $(this);
                var name = $inp.attr('name').trim();
                if (name.length) {
                    widget_data[name] = $inp.val().trim();
                }
            });
            // console.log(widget_data);
            return widget_data;
        }

        // event handlers
        $('#select-template').on('change', function (e) {
            var $elem = $(this);
            if (!$elem.val()) {
                // undo select
                undo_select_template();
                return false;
            }

            if (l.template_active) {
                var r = confirm(
                    'Stopping Template Change ' + 
                    'Your progress will be lost. ' + 
                    'Press Cancel to Continue');
                if (r) {
                    // undo select
                    undo_select_template();
                    return false;
                }
            }

            l.template_active = true;
            l.template_index = $elem.val();

            make_ajax_request(l.init_data);

            console.log(
                'activated template: ' + 
                $elem.children('option:selected')
                .text().trim());
        });
        $('body').on('click', '.widget-icon', function (e) {
            var $elem = $(this);
            var $doc = $elem.siblings('.special-syntax-doc');
            if (!$doc.length) return false;
            $doc.toggleClass('show');
            $doc.siblings('input, textarea, select')
                .first().focus();
        });
        $('#apply-changes').on('click', function (e) {
            var populate_data = get_widget_data();
            make_ajax_request(populate_data);
        });
        $('#get-pdf').on('click', function (e) {
            var populate_data = get_widget_data();
            make_ajax_request(populate_data, true, function (resp) {
                console.log(resp);
                if (!resp.error) {
                    if ('undefined' !== typeof resp.outfile) {
                        window.open(resp.outfile);
                    } else {
                        alert('There was a problem saving to pdf');
                    }
                }
            });
        });

        if (!l.template_active && (null !== l.template_index)) {
            // autoselect template
            $('#select-template').val(l.template_index);
            $('#select-template').change();
        }
        // select first by default
        else {
            $('#select-template').val(0);
            $('#select-template').change();
        }
    });

})(window, jQuery);