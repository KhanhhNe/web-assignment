<div id="input-modal" class="modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form class="modal-body">
                <label for="input-name">Name</label>
                <input type="text" class="form-control" id="input-name" placeholder="Name">
            </form>
            <div class="modal-footer">
                <button onclick="modalDismiss()" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button onclick="modalSubmit()" type="button" class="btn btn-primary">Submit</button>
            </div>
        </div>
    </div>
</div>

<div id="toast-container" class="toast-container position-fixed bottom-0 end-0 m-3">
</div>

<script>
    const modal = new bootstrap.Modal('#input-modal');
    let modalResolve;

    function modalDismiss() {
        modal.hide();
        modalResolve?.({
            success: false
        });
    }

    function modalSubmit() {
        $('#input-modal').modal('hide');

        modalResolve?.({
            success: true,
            values: getFormData($('#input-modal .modal-body')),
        });
    }

    function promptModal(fields = {}) {
        $('#input-modal .modal-body').empty();
        for (const [key, {
                title,
                defaultValue = ''
            }] of Object.entries(fields)) {
            $('#input-modal .modal-body').append(`
                <label for="input-${key}">${title}</label>
                <input type="text" class="form-control" id="input-${key}" name="${key}" placeholder="${title}" value="${defaultValue}">
            `);
        }

        return new Promise((resolve) => {
            modal.show();
            modalResolve = resolve;
        });
    }

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
        new bootstrap.Toast(toast).show({
            delay: 5000,
        })
        toast.on('hidden.bs.toast', () => toast.remove())
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

    function getFormData(form) {
        const data = {};
        for (const field of $(form).serializeArray()) {
            data[field.name] = field.value;
        }
        return data;
    }
</script>
</body>

</html>