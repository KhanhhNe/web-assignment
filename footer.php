<div id="toast-container" class="toast-container position-absolute bottom-0 end-0 m-3">
</div>

<script>
    function showToast(content, options = {}) {
        const type = options.type ?? 'info';

        const id = `toast-${Math.random()}`.replaceAll('.', '');
        $('#toast-container').append(`
            <div id=${id} class="toast d-flex text-bg-${type} mb-1" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-body">
                    ${content}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `)
        const toast = $(`#${id}`);
        new bootstrap.Toast(toast).show()
    }

    function showRedDot(jqueryElem) {
        const id = `reddot-${Math.random()}`.replaceAll('.', '');
        $(jqueryElem).append(`
            <span id=${id} class="position-absolute top-0 start-100 translate-middle p-2 bg-danger border border-light rounded-circle">
                <span class="visually-hidden">New course</span>
            </span>
        `)
        setTimeout(() => $(`#${id}`).remove(), 5000);
    }
</script>
</body>

</html>