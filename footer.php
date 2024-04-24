    <script>
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