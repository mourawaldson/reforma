$(function () {
    const API_BASE = '/api-proxy';
    const $wrapper = $('#tags-table-wrapper');
    const $status = $('#tags-status');
    const tableTemplate = document.getElementById('tags-table-template');
    const rowTemplate = document.getElementById('tag-row-template');

    function showStatus(message, type) {
        $wrapper.prop('hidden', true).empty();
        $status
            .removeClass('text-muted alert-info alert-danger')
            .addClass(type)
            .text(message)
            .prop('hidden', false);
    }

    function loadTags() {
        $.getJSON(API_BASE + '/tags', function (data) {
            if (!Array.isArray(data) || data.length === 0) {
                showStatus('Nenhuma tag cadastrada.', 'alert alert-info');
                return;
            }

            const table = tableTemplate.content.firstElementChild.cloneNode(true);
            const rows = table.querySelector('[data-rows]');

            data.forEach(function (tag) {
                const row = rowTemplate.content.firstElementChild.cloneNode(true);
                row.querySelector('[data-field="name"]').textContent = tag.name || '-';

                const editLink = row.querySelector('[data-action="edit"]');
                editLink.href = '/tags/' + encodeURIComponent(tag.id) + '/edit';
                row.querySelector('.btn-remove').dataset.id = tag.id;
                rows.appendChild(row);
            });

            $status.prop('hidden', true);
            $wrapper.empty().append(table).prop('hidden', false);
        }).fail(function () {
            showStatus('Erro ao carregar tags.', 'alert alert-danger');
        });
    }

    $wrapper.on('click', '.btn-remove', function () {
        const id = $(this).data('id');
        if (!confirm('Deseja realmente remover esta tag?')) return;

        $.ajax({
            url: API_BASE + '/tags/' + encodeURIComponent(id),
            method: 'DELETE',
            success: loadTags,
            error: function (xhr) {
                alert('Erro ao remover tag: ' + xhr.responseText);
            }
        });
    });

    loadTags();
});
