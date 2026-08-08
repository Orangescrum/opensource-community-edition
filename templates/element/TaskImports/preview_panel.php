<?php
/**
 * Task Import Preview Panel Element
 * Shows data preview with mapping validation results
 */
?>
<style type="text/css">
    .preview-panel {
        margin-top: 20px;
        border-radius: 5px;
        background: #f9f9f9;
    }

    .preview-header {
        background: #e9ecef;
        padding: 10px 15px;
        border-bottom: 1px solid #ddd;
        font-weight: bold;
        color: #495057;
    }

    .preview-content {
        padding: 15px;
        max-height: 300px;
        overflow-y: auto;
    }

    .preview-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }

    .preview-table th,
    .preview-table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
        vertical-align: top;
    }

    .preview-table th {
        background: #f1f3f4;
        font-weight: bold;
    }

    .preview-table td {
        background: #fff;
        max-width: 150px;
        word-wrap: break-word;
    }

    .validation-panel {
        margin-top: 10px;
        border-radius: 5px;
        font-size: 13px;
    }

    .validation-success {
        background: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
        padding: 10px;
    }

    .validation-warning {
        background: #fff3cd;
        border: 1px solid #ffeaa7;
        color: #856404;
        padding: 10px;
    }

    .validation-error {
        background: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
        padding: 10px;
    }

    .validation-item {
        margin: 5px 0;
        padding: 3px 0;
        border-bottom: 1px dotted rgba(0,0,0,0.1);
    }

    .validation-item:last-child {
        border-bottom: none;
    }

    .mapping-stats {
        background: #e3f2fd;
        border: 1px solid #bbdefb;
        color: #1565c0;
        padding: 8px 12px;
        border-radius: 3px;
        font-size: 12px;
        margin-bottom: 10px;
    }

    .unmapped-preview {
        color: #999;
        font-style: italic;
        background: #f5f5f5;
    }

    .no-data-message,
    .no-mapping-message,
    .no-mapped-fields-message {
        text-align: center;
        padding: 40px 20px;
        color: #666;
        font-style: italic;
        background: #f8f9fa;
        border: 1px dashed #ccc;
        border-radius: 5px;
        margin: 10px 0;
    }

    .no-mapped-fields-message {
        background: #fff3cd;
        border-color: #ffeaa7;
        color: #856404;
        padding: 15px;
        margin-top: 10px;
        font-size: 13px;
    }

    .no-data-cell {
        text-align: center;
        color: #999;
        font-style: italic;
        padding: 20px;
        background: #f8f9fa;
    }

    .empty-value {
        color: #bbb;
        font-size: 11px;
    }

    .mapped-header {
        background: #d4edda !important;
        color: #155724;
    }

    .unmapped-header {
        background: #f8d7da !important;
        color: #721c24;
    }

    .mapped-cell {
        background: #f8fff9 !important;
        border-left: 3px solid #28a745;
    }

    .preview-table th.mapped-header small {
        color: #0c5d20;
        font-weight: normal;
    }

    .preview-table th.unmapped-header small {
        color: #491217;
        font-weight: normal;
    }
</style>

<!-- Preview Panel -->
<div class="preview-panel">
    <div class="preview-content">
        <div id="validation-results"></div>
        <div id="preview-table-container">
            <table class="preview-table" id="preview-table">
                <thead>
                    <tr id="preview-headers"></tr>
                </thead>
                <tbody id="preview-body"></tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Preview Panel JavaScript Functions
function updatePreview() {
    var systemItems = $('#customize_left_menu .dd-item');
    var headers = [];
    var mappedData = [];

    // Check if CSV data is available
    if (!window.csvData || window.csvData.length === 0) {
        $('#preview-table-container').html('<div class="no-data-message">No CSV data available for preview. Please upload a CSV file first.</div>');
        $('#validation-results').html('<div class="validation-warning"><strong>⚠ No Data:</strong><br>Please upload a CSV file to see the data preview and configure field mappings.</div>');
        return;
    }

    // Build headers from current mapping or from CSV fields if available
    if ($('.mapping-pair').length > 0) {
        $('.mapping-pair').each(function(index) {
            var csvFieldName = $(this).find('.csv-field').text().replace(/\s*\([^)]*\)\s*$/, '');
            var systemField = $(this).find('.system-field');
            
            if (systemField.hasClass('unmapped-field')) {
                headers.push({
                    csv: csvFieldName,
                    system: 'Not Mapped',
                    mapped: false
                });
            } else {
                var systemFieldName = systemField.text().replace(/\s*\([^)]*\)\s*$/, '');
                headers.push({
                    csv: csvFieldName,
                    system: systemFieldName,
                    mapped: true
                });
            }
        });
    } else if (window.csvFields && window.csvFields.length > 0) {
        // If no mapping pairs are displayed yet, use CSV fields directly
        window.csvFields.forEach(function(field) {
            headers.push({
                csv: field.name,
                system: 'Not Mapped',
                mapped: false
            });
        });
    } else if (window.csvData && window.csvData.length > 0) {
        // Fallback: try to extract headers from CSV data keys
        var firstRow = window.csvData[0];
        if (firstRow && typeof firstRow === 'object') {
            Object.keys(firstRow).forEach(function(key, index) {
                var displayName = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                headers.push({
                    csv: displayName,
                    system: 'Not Mapped',
                    mapped: false
                });
            });
        }
    }

    // Handle case where no mapping pairs exist
    if (headers.length === 0) {
        $('#preview-table-container').html('<div class="no-mapping-message">No field mappings configured. Please set up field mappings to see preview.</div>');
        return;
    }

    // Check if any fields are mapped
    var hasMappedFields = headers.some(function(header) { return header.mapped; });
    
    // Build preview table
    var tableHtml = '<table class="preview-table" id="preview-table"><thead><tr id="preview-headers">';
    
    headers.forEach(function(header) {
        var className = header.mapped ? 'mapped-header' : 'unmapped-header';
        tableHtml += '<th class="' + className + '">' + 
                     header.csv + '<br><small>→ ' + header.system + '</small></th>';
    });
    
    tableHtml += '</tr></thead><tbody id="preview-body">';

    // Build data rows
    var maxRows = Math.min(window.csvData.length, 5);
    if (maxRows === 0) {
        tableHtml += '<tr><td colspan="' + headers.length + '" class="no-data-cell">No sample data available</td></tr>';
    } else {
        for (var i = 0; i < maxRows; i++) {
            tableHtml += '<tr>';
            headers.forEach(function(header, index) {
                // Handle different CSV data structures
                var value;
                
                // Try to get value by field ID first, then by column index
                if (window.csvData[i] && typeof window.csvData[i] === 'object') {
                    // Try various possible field ID formats
                    var fieldIds = [
                        'col_' + (index + 1),
                        header.csv.toLowerCase().replace(/[^a-z0-9]/g, '_'),
                        Object.keys(window.csvData[i])[index]
                    ];
                    
                    for (var j = 0; j < fieldIds.length; j++) {
                        if (window.csvData[i][fieldIds[j]] !== undefined) {
                            value = window.csvData[i][fieldIds[j]];
                            break;
                        }
                    }
                    
                    // If still no value, try to get by array index
                    if (value === undefined) {
                        var keys = Object.keys(window.csvData[i]);
                        if (keys[index]) {
                            value = window.csvData[i][keys[index]];
                        }
                    }
                } else if (Array.isArray(window.csvData[i])) {
                    // If data is array format
                    value = window.csvData[i][index];
                }
                
                var className = header.mapped ? 'mapped-cell' : 'unmapped-preview';
                
                // Handle empty or undefined values
                if (value === undefined || value === null || value === '') {
                    value = '<em class="empty-value">empty</em>';
                } else {
                    // Convert to string and truncate long values
                    value = String(value);
                    if (value.length > 50) {
                        value = value.substring(0, 47) + '...';
                    }
                    // Escape HTML to prevent XSS
                    value = $('<div>').text(value).html();
                }
                
                var originalValue = '';
                if (window.csvData[i]) {
                    // Get original value for tooltip
                    var keys = Object.keys(window.csvData[i]);
                    if (keys[index]) {
                        originalValue = String(window.csvData[i][keys[index]] || '');
                    }
                }
                
                tableHtml += '<td class="' + className + '" title="' + 
                            $('<div>').text(originalValue).html() + '">' + value + '</td>';
            });
            tableHtml += '</tr>';
        }
    }
    
    tableHtml += '</tbody></table>';
    
    // Add message if no fields are mapped
    if (!hasMappedFields) {
        tableHtml += '<div class="no-mapped-fields-message">No fields are currently mapped. Mapped fields will appear in green.</div>';
    }
    
    $('#preview-table-container').html(tableHtml);
}

function displayValidationResults(validation) {
    var html = '';
    
    // Check if validation object is valid
    if (!validation || typeof validation !== 'object') {
        html += '<div class="validation-error">';
        html += '<strong>✗ Validation Error:</strong><br>';
        html += '<div class="validation-item">Unable to validate mapping configuration.</div>';
        html += '</div>';
        $('#validation-results').html(html);
        return;
    }
    
    // Mapping statistics
    html += '<div class="mapping-stats">';
    html += '<strong>Mapping Status:</strong> ' + (validation.mappedCount || 0) + ' of ' + (validation.totalFields || 0) + ' fields mapped';
    if (validation.mappedCount > 0 && validation.totalFields > 0) {
        var percentage = Math.round((validation.mappedCount / validation.totalFields) * 100);
        html += ' (' + percentage + '%)';
    }
    html += '</div>';

    // Handle empty mapping case
    if (validation.mappedCount === 0) {
        html += '<div class="validation-warning">';
        html += '<strong>⚠ No Mappings Configured</strong><br>';
        html += '<div class="validation-item">Please drag system fields to create field mappings, or use "Auto Map" to automatically map compatible fields.</div>';
        html += '</div>';
        $('#validation-results').html(html);
        return;
    }

    // Success messages first
    if (validation.valid && (!validation.errors || validation.errors.length === 0)) {
        html += '<div class="validation-success">';
        html += '<strong>✓ Valid Mapping Configuration</strong><br>';
        if (validation.mappedCount > 0) {
            html += '<div class="validation-item">All mapped fields are compatible and ready for import.</div>';
        }
        html += '</div>';
    }

    // Warnings (show even for valid mappings)
    if (validation.warnings && validation.warnings.length > 0) {
        html += '<div class="validation-warning">';
        html += '<strong>⚠ Warnings:</strong><br>';
        validation.warnings.forEach(function(warning) {
            // Escape HTML to prevent XSS
            var escapedWarning = $('<div>').text(warning).html();
            html += '<div class="validation-item">' + escapedWarning + '</div>';
        });
        html += '</div>';
    }

    // Errors (these make the mapping invalid)
    if (validation.errors && validation.errors.length > 0) {
        html += '<div class="validation-error">';
        html += '<strong>✗ Errors:</strong><br>';
        validation.errors.forEach(function(error) {
            // Escape HTML to prevent XSS
            var escapedError = $('<div>').text(error).html();
            html += '<div class="validation-item">' + escapedError + '</div>';
        });
        html += '</div>';
    }

    // If no specific messages, show default status
    if (!validation.errors && !validation.warnings && validation.mappedCount === 0) {
        html += '<div class="validation-warning">';
        html += '<strong>⚠ No Field Mappings</strong><br>';
        html += '<div class="validation-item">Please create field mappings to proceed with the import.</div>';
        html += '</div>';
    }

    $('#validation-results').html(html);
}

// Refresh preview button event handler
$(document).ready(function() {
    $('#refresh_preview').on('click', function() {
        updatePreview();
        if (typeof saveMappingToSession === 'function') {
            saveMappingToSession();
        }
    });
});
</script>
