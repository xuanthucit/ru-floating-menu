jQuery(document).ready(function ($) {
    var container = $('#ru-fm-items-container');
    var items = ruFmData.items || [];

    // Initialize with existing items
    items.forEach(function (item) {
        addItemRow(item);
    });

    // Add Item Button
    $('#ru-fm-add-item').on('click', function () {
        addItemRow();
    });

    // Remove Item
    container.on('click', '.ru-fm-remove', function () {
        if (confirm('Are you sure you want to remove this item?')) {
            $(this).closest('.ru-fm-item-row').remove();
        }
    });

    // Move Up
    container.on('click', '.ru-fm-move-up', function () {
        var row = $(this).closest('.ru-fm-item-row');
        row.prev().before(row);
    });

    // Move Down
    container.on('click', '.ru-fm-move-down', function () {
        var row = $(this).closest('.ru-fm-item-row');
        row.next().after(row);
    });

    // Upload Image
    container.on('click', '.ru-fm-upload-btn', function (e) {
        e.preventDefault();
        var button = $(this);
        var row = button.closest('.ru-fm-item-row');
        var input = row.find('.ru-fm-icon-input');
        var preview = row.find('.ru-fm-icon-preview');

        var frame = wp.media({
            title: 'Select Icon',
            multiple: false,
            library: { type: 'image' },
            button: { text: 'Use this icon' }
        });

        frame.on('select', function () {
            var attachment = frame.state().get('selection').first().toJSON();
            input.val(attachment.url);
            preview.html('<img src="' + attachment.url + '" alt="icon preview">');
        });

        frame.open();
    });

    // Form Submit - Serialize Data
    $('form').on('submit', function () {
        var data = [];
        container.find('.ru-fm-item-row').each(function () {
            var row = $(this);
            var item = {
                icon: row.find('.ru-fm-icon-input').val(),
                name: row.find('.ru-fm-name-input').val(),
                link: row.find('.ru-fm-link-input').val(),
                target: row.find('.ru-fm-target-input').val()
            };
            data.push(item);
        });
        $('#ru_fm_items_json').val(JSON.stringify(data));
    });

    function addItemRow(item) {
        item = item || { icon: '', name: '', link: '', target: '_self' };

        var html = `
            <div class="ru-fm-item-row">
                <div class="ru-fm-handle dashicons dashicons-move"></div>
                <div class="ru-fm-icon-preview">
                    ${item.icon ? '<img src="' + item.icon + '">' : '<span class="dashicons dashicons-format-image"></span>'}
                </div>
                <div class="ru-fm-inputs">
                    <div>
                        <input type="text" class="widefat ru-fm-name-input" placeholder="Name (e.g. Facebook)" value="${escapeHtml(item.name)}">
                    </div>
                    <div>
                        <input type="text" class="widefat ru-fm-link-input" placeholder="Link (https://...)" value="${escapeHtml(item.link)}">
                    </div>
                    <div>
                         <input type="hidden" class="ru-fm-icon-input" value="${escapeHtml(item.icon)}">
                         <button type="button" class="button ru-fm-upload-btn">Select Icon</button>
                    </div>
                    <div>
                        <select class="ru-fm-target-input">
                            <option value="_self" ${item.target === '_self' ? 'selected' : ''}>Curr. Tab</option>
                            <option value="_blank" ${item.target === '_blank' ? 'selected' : ''}>New Tab</option>
                        </select>
                    </div>
                </div>
                <div class="ru-fm-actions">
                    <button type="button" class="button ru-fm-move-up dashicons dashicons-arrow-up-alt2"></button>
                    <button type="button" class="button ru-fm-move-down dashicons dashicons-arrow-down-alt2"></button>
                    <button type="button" class="button ru-fm-remove dashicons dashicons-trash" style="color: #a00;"></button>
                </div>
            </div>
        `;
        container.append(html);
    }

    function escapeHtml(text) {
        if (!text) return '';
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
});