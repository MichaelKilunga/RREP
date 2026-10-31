<script>
document.addEventListener('DOMContentLoaded', function () {
    const fmt = (n) => 'TSh ' + Math.round(n).toLocaleString('en-US');

    document.querySelectorAll('form.inv-form').forEach(function (form) {
        const lines = form.querySelector('.inv-lines');

        function renumber() {
            lines.querySelectorAll('.inv-line').forEach(function (row, i) {
                row.querySelectorAll('input').forEach(function (input) {
                    input.name = input.name.replace(/items\[\d+\]/, 'items[' + i + ']');
                });
            });
        }

        function recalc() {
            let subtotal = 0;
            lines.querySelectorAll('.inv-line').forEach(function (row) {
                subtotal += (parseFloat(row.querySelector('.inv-qty').value) || 0) * (parseFloat(row.querySelector('.inv-price').value) || 0);
            });
            const tax = subtotal * ((parseFloat(form.querySelector('.inv-tax').value) || 0) / 100);
            const total = Math.max(0, subtotal + tax - (parseFloat(form.querySelector('.inv-discount').value) || 0));
            form.querySelector('.inv-subtotal').textContent = fmt(subtotal);
            form.querySelector('.inv-taxtotal').textContent = fmt(tax);
            form.querySelector('.inv-grandtotal').textContent = fmt(total);
        }

        form.querySelector('.inv-add-line').addEventListener('click', function () {
            const first = lines.querySelector('.inv-line');
            const row = first.cloneNode(true);
            row.querySelectorAll('input').forEach(i => { i.value = i.classList.contains('inv-qty') ? 1 : ''; });
            row.querySelectorAll('label').forEach(l => l.classList.add('d-md-none'));
            lines.appendChild(row);
            renumber();
            row.querySelector('input').focus();
            recalc();
        });

        lines.addEventListener('click', function (e) {
            const btn = e.target.closest('.inv-remove-line');
            if (!btn || lines.querySelectorAll('.inv-line').length === 1) { return; }
            btn.closest('.inv-line').remove();
            renumber();
            recalc();
        });

        form.addEventListener('input', recalc);
        recalc();
    });
});
</script>
