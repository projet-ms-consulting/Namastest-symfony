//Library
//https://github.com/SortableJS/Sortable
let url = document.currentScript.getAttribute('class');
const dragArea = document.querySelector('.drag-and-drop')
new Sortable(dragArea, {
    animation : 350,
    onEnd : function () {
        let ids = '';

        $('#sortable tr').each(function () {

            let id = $(this).attr('rel');

            if (ids === '') {
                ids = id;
            }
            else {
                ids = ids + ',' + id;
            }

        })

        $.ajax({
            url: url,
            data: 'ids='+ids,
            type: 'POST',
            success: function () {

                let trs = $('.ordre');
                for (let i = 0; i < trs.length; i++) {
                    let ordre = i + 1;
                    trs[i].innerHTML = ordre + '';
                }
            },
            error: function () {
                alert('Erreur lors de la sauvegarde de l\'ordre');
            }
        })
    }
});
