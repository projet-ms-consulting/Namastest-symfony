document.querySelectorAll('.deleteLink').forEach(deleteLink => {
    deleteLink.addEventListener('click', function(e) {
        if(!confirm(this.dataset.message)) {
            e.preventDefault();
        }
    });
})