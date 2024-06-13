function toggle(source) {
    var checkboxes = document.querySelectorAll('.test-checkboxes');
    for (var i = 0; i < checkboxes.length; i++) {
        if (checkboxes[i] != source && !checkboxes[i].disabled)
            checkboxes[i].checked = source.checked;
    }
}