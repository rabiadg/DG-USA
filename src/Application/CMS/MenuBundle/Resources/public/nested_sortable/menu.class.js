var Menu = {
    AddLoader: function () {
        if ($('.sonata-ba-form').find('.loader').length == 0) {
            $('.sonata-ba-form').prepend(loader);
        }
    },
    RemoveLoader: function () {
        $('.loader').remove();
    },
    GenerateItem: function ($this, type) {
        var htmlToAppend = '';
        if ($this != '' && typeof $this != 'undefined' && type == 'page') {
            var liId = 'menu-item-sortable-'+type + $this.val();
            if ($('ol#sortable #' + liId).length == 0) {
                htmlToAppend += '<li id="menu-item-sortable-' +type+ $this.val() + '" data-type="' + type + '" data-page-id="' + $this.val() + '" data-page-title="' + $this.data('title') + '">';
                htmlToAppend += '<div  class="menu-item-sortable"><span class="title">' + $this.data('title') + '</span><span class="closer">x</span><span class="arrow-down"></span>';
                htmlToAppend += '<div class="info-container"> ';


                htmlToAppend += ' <div class="form-group">';
                htmlToAppend += '  <label for="title" class="control-label required">Title</label>';
                htmlToAppend += '  <div class=" sonata-ba-field sonata-ba-field-standard-natural">';
                htmlToAppend += '  <input type="text" class="link form-control" maxlength="255" value="' + $this.data('title') + '" required="required" name="title" >';
                htmlToAppend += '</div>';
                htmlToAppend += '</div>';

                htmlToAppend += '<div class="form-group">';
                /*htmlToAppend += '    <label for="link" class="checkbox control-label required">Target (Child)?</label>';*/
                htmlToAppend += '       <div class=" sonata-ba-field sonata-ba-field-standard-natural menu-checkbox ">';
                htmlToAppend += '<input type="hidden"  name="program_id" value="-1"><input type="hidden"  name="page_id" value="' + $this.val() + '"><input type="hidden"  name="type" value="' + type + '"><input type="hidden"  name="link" value="">';
                /*htmlToAppend += '              <input type="checkbox" class="link"  required="required" name="target">';*/
                htmlToAppend += '</div>';
                htmlToAppend += '</div>';
                htmlToAppend += '</div>';
                htmlToAppend += '</div></li>';
            }
        } else if ($this != '' && typeof $this != 'undefined' && type == 'program') {
            var liId = 'menu-item-sortable-' +type+ $this.val();
            if ($('ol#sortable #' + liId).length == 0) {
                htmlToAppend += '<li id="menu-item-sortable-' +type+ $this.val() + '" data-type="' + type + '" data-page-id="' + $this.val() + '" data-page-title="' + $this.data('title') + '">';
                htmlToAppend += '<div  class="menu-item-sortable"><span class="title">' + $this.data('title') + '</span><span class="closer">x</span><span class="arrow-down"></span>';
                htmlToAppend += '<div class="info-container"> ';


                htmlToAppend += ' <div class="form-group">';
                htmlToAppend += '  <label for="title" class="control-label required">Title</label>';
                htmlToAppend += '  <div class=" sonata-ba-field sonata-ba-field-standard-natural">';
                htmlToAppend += '  <input type="text" class="link form-control" maxlength="255" value="' + $this.data('title') + '" required="required" name="title" >';
                htmlToAppend += '</div>';
                htmlToAppend += '</div>';

                htmlToAppend += '<div class="form-group">';
                /*htmlToAppend += '    <label for="link" class="checkbox control-label required">Target (Child)?</label>';*/
                htmlToAppend += '       <div class=" sonata-ba-field sonata-ba-field-standard-natural menu-checkbox ">';
                htmlToAppend += '<input type="hidden"  name="program_id" value="' + $this.val() + '"><input type="hidden"  name="page_id" value="-1"><input type="hidden"  name="type" value="' + type + '"><input type="hidden"  name="link" value="">';
                /*htmlToAppend += '              <input type="checkbox" class="link"  required="required" name="target">';*/
                htmlToAppend += '</div>';
                htmlToAppend += '</div>';
                htmlToAppend += '</div>';
                htmlToAppend += '</div></li>';
            }
        } else if (type == 'link') {
            htmlToAppend += '<li  data-type="' + type + '">';
            htmlToAppend += '<div  class="menu-item-sortable"><span class="title">' + $this.title + '</span>';
            htmlToAppend += '<span class="closer">x</span><span class="arrow-down"></span>';
            htmlToAppend += '<div class="info-container"> <div class="form-group">';
            htmlToAppend += '    <label for="link" class="control-label required">Url</label>';
            htmlToAppend += '       <div class=" sonata-ba-field sonata-ba-field-standard-natural  ">';
            htmlToAppend += '              <input type="hidden"  name="page_id" value="-1"> <input type="hidden"  name="program_id" value="-1"> <input type="hidden"  name="type" value="' + type + '"><input type="hidden"  name="target" value="">';
            htmlToAppend += '              <input type="text" class="link form-control" maxlength="255" value="' + $this.link + '" required="required" name="link">';
            htmlToAppend += '          </div></div>';
            htmlToAppend += '            <div class="form-group">';
            htmlToAppend += '               <label for="title" class="control-label required">Title</label>';
            htmlToAppend += '               <div class=" sonata-ba-field sonata-ba-field-standard-natural">';
            htmlToAppend += '                    <input type="text" class="link form-control" maxlength="255" value="' + $this.title + '" required="required" name="title" >';
            htmlToAppend += '                 </div>';
            htmlToAppend += '            </div></div>';
            htmlToAppend += '</div></li>';
        }
        return htmlToAppend;
    },
    IniSortable: function () {
        $('ol.sortable').nestedSortable('destroy');
        $('ol.sortable').nestedSortable(nestedSortableConfig);
        $('.menu-item-sortable .closer').unbind('click');
        $('.menu-item-sortable .closer').on('click', function () {
            if (!confirm("Are you sure you want to remove this menu item?")) {
                return false;
            }
            $(this).parent().parent().remove();
        });
    },
    IniToggle: function () {
        $('.arrow-down').unbind('click');
        $('.arrow-down').on('click', function () {
            $(this).next().slideToggle();
        });
    },
    BuildTree: function (node) {
        if (node.nodeName == 'LI' || node.nodeName == 'OL') {
            var r = {
                tag: node.nodeName,
                data_type: $(node).data('type'),
                data_title: $(node).data('page-title'),
                type: '',
                title: '',
                target: 0,
                link: '',
                page_id: '-1',
                program_id: '-1'
            };
            if (node.nodeName == 'LI') {
                var info_container = $(node).find('.info-container').first();
                r.type = info_container.find('input[name=type]').val();
                r.title = info_container.find('input[name=title]').val();
                if (info_container.find('input[name=target]').is(':checked')) {
                    r.target = 1;
                }
                r.link = info_container.find('input[name=link]').val();
                r.page_id = info_container.find('input[name=page_id]').val();
                r.program_id = info_container.find('input[name=program_id]').val();
            }

            var a, i;
            if ($(node).find('> li').length || $(node).find('> ol').length) {
                r.children = [];
                for (i = 0; a = node.children[i]; i++) {
                    var children = Menu.BuildTree(a);
                    if (typeof children != 'undefined') {
                        r.children.push(children);
                    }
                }
            }
        }
        return r;
    }

};