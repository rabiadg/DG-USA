var loader = '<div class="loader"><img src="/assets/images/ajax-loader.gif"></div>';
var nestedSortableConfig = {
    forcePlaceholderSize: false,
    handle: 'div',
    helper: 'clone',
    items: 'li',
    opacity: .6,
    placeholder: 'placeholder',
    revert: 250,
    tabSize: 25,
    tolerance: 'pointer',
    toleranceElement: '> div',
    maxLevels: menu_level_limit,
    isTree: true,
    expandOnHover: 700,
    startCollapsed: true
};
$(document).ready(function () {
    //$('input').iCheck('destroy');
    $('input:checkbox').removeAttr('checked');
    $('.save_menu').on('click', function () {
        Menu.AddLoader();
        var tree = Menu.BuildTree(document.getElementById("sortable"));
        console.log(tree);
        $.ajax({
            type: "POST",
            url: saveMenu,
            data: {tree: JSON.stringify(tree)},
            dataType: 'JSON',
            success: function (data) {
                if (data.success) {
                    $('#menu_save_response').html(data.message);
                    jQuery.noConflict();
                    $('.bs-example-modal-sm').modal('show');
                    setTimeout(function () {
                        $('.bs-example-modal-sm').modal('hide');
                    }, 3000);
                } else {
                    $('#menu_save_response').html(data.message);
                    $('.bs-example-modal-sm').modal('show');
                }
            }
        }).always(function () {
            Menu.RemoveLoader();
        });
    });
    $('#add_external_pages').on('click', function () {
        /*if ($('.external_link_container #link').val() == '') {
         return false;
         }*/
        if ($('.external_link_container #title').val() == '') {
            return false
        }
        var data = {
            link: $('.external_link_container #link').val(),
            title: $('.external_link_container #title').val()
        };
        var htmlToAppend = Menu.GenerateItem(data, 'link');
        $('ol#sortable').append(htmlToAppend);
        Menu.IniSortable();
        Menu.IniToggle();
    });
    $('#add_pages').on('click', function () {
        var htmlToAppend = '';
        var selectedPages = $('select.add_pages').val();
        $(selectedPages).each(function (i,v) {
                var $Obj = $("<input>", {value: v}).data('title',$("select.add_pages option[value='"+ v +"']").text());
                htmlToAppend += Menu.GenerateItem($Obj, 'page');
        });
        if (htmlToAppend != '') {
            $('ol#sortable').append(htmlToAppend);
            Menu.IniSortable();
            Menu.IniToggle();
        }
        var $exampleMulti = $('select.add_pages').select2()
        $exampleMulti.val(null).trigger("change");

    });
    $('#add_programs').on('click', function () {

        var htmlToAppend = '';
        var selectedPages = $('select.add_programs').val();
        $(selectedPages).each(function (i,v) {
                var $Obj = $("<input>", {value: v}).data('title',$("select.add_programs option[value='"+ v +"']").text());
                htmlToAppend += Menu.GenerateItem($Obj, 'program');
        });
        if (htmlToAppend != '') {
            $('ol#sortable').append(htmlToAppend);
            Menu.IniSortable();
            Menu.IniToggle();
        }
        var $exampleMulti = $('select.add_programs').select2();
        $exampleMulti.val(null).trigger("change");
    });
    $('ol.sortable').nestedSortable(nestedSortableConfig);
    $('.disclose').on('click', function () {
        $(this).closest('li').toggleClass('mjs-nestedSortable-collapsed').toggleClass('mjs-nestedSortable-expanded');
    })

    $('#serialize').click(function () {
        serialized = $('ol.sortable').nestedSortable('serialize');
        $('#serializeOutput').text(serialized + '\n\n');
    })

    $('#toHierarchy').click(function (e) {
        hiered = $('ol.sortable').nestedSortable('toHierarchy', {startDepthCount: 0});
        hiered = dump(hiered);
        (typeof($('#toHierarchyOutput')[0].textContent) != 'undefined') ?
            $('#toHierarchyOutput')[0].textContent = hiered : $('#toHierarchyOutput')[0].innerText = hiered;
    })

    $('#toArray').click(function (e) {
        arraied = $('ol.sortable').nestedSortable('toArray', {startDepthCount: 0});
        arraied = dump(arraied);
        (typeof($('#toArrayOutput')[0].textContent) != 'undefined') ?
            $('#toArrayOutput')[0].textContent = arraied : $('#toArrayOutput')[0].innerText = arraied;
    })

});

function dump(arr, level) {
    var dumped_text = "";
    if (!level) level = 0;

    //The padding given at the beginning of the line.
    var level_padding = "";
    for (var j = 0; j < level + 1; j++) level_padding += "    ";

    if (typeof(arr) == 'object') { //Array/Hashes/Objects
        for (var item in arr) {
            var value = arr[item];

            if (typeof(value) == 'object') { //If it is an array,
                dumped_text += level_padding + "'" + item + "' ...\n";
                dumped_text += dump(value, level + 1);
            } else {
                dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
            }
        }
    } else { //Strings/Chars/Numbers etc.
        dumped_text = "===>" + arr + "<===(" + typeof(arr) + ")";
    }
    return dumped_text;
}

