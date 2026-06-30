/**
 * Dashboard - Belegverwaltung
 */

// API-Basis automatisch erkennen
var API_BASE = (function() {
    const path = window.location.pathname;
    const base = path.substring(0, path.lastIndexOf('/'));
    return base + '/api';
})();

let currentFile = null;

document.addEventListener('DOMContentLoaded', function() {
    loadReceipts();
    
    // Upload Modal
    const uploadBtn = document.getElementById('uploadBtn');
    const uploadModal = document.getElementById('uploadModal');
    const closeModal = document.getElementById('closeModal');
    const cameraOption = document.getElementById('cameraOption');
    const fileOption = document.getElementById('fileOption');
    const cameraInput = document.getElementById('cameraInput');
    const fileInput = document.getElementById('fileInput');
    const cancelUpload = document.getElementById('cancelUpload');
    const confirmUpload = document.getElementById('confirmUpload');
    
    uploadBtn?.addEventListener('click', () => {
        uploadModal.style.display = 'flex';
    });
    
    closeModal?.addEventListener('click', () => {
        uploadModal.style.display = 'none';
        resetUpload();
    });
    
    cameraOption?.addEventListener('click', () => {
        cameraInput.click();
    });
    
    fileOption?.addEventListener('click', () => {
        fileInput.click();
    });
    
    cameraInput?.addEventListener('change', handleFileSelect);
    fileInput?.addEventListener('change', handleFileSelect);
    
    cancelUpload?.addEventListener('click', resetUpload);
    confirmUpload?.addEventListener('click', handleUpload);
    
    // Filter
    const categoryFilter = document.getElementById('categoryFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    categoryFilter?.addEventListener('change', loadReceipts);
    statusFilter?.addEventListener('change', loadReceipts);
    
    // Receipt Modal
    const receiptModal = document.getElementById('receiptModal');
    const closeReceiptModal = document.getElementById('closeReceiptModal');
    
    closeReceiptModal?.addEventListener('click', () => {
        receiptModal.style.display = 'none';
    });
    
    // Modal schließen bei Klick außerhalb
    uploadModal?.addEventListener('click', (e) => {
        if (e.target === uploadModal) {
            uploadModal.style.display = 'none';
            resetUpload();
        }
    });
    
    receiptModal?.addEventListener('click', (e) => {
        if (e.target === receiptModal) {
            receiptModal.style.display = 'none';
        }
    });
});

function handleFileSelect(e) {
    const file = e.target.files[0];
    if (!file) return;
    
    currentFile = file;
    
    // Vorschau anzeigen
    const preview = document.getElementById('uploadPreview');
    const previewImage = document.getElementById('previewImage');
    
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = (e) => {
            previewImage.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        previewImage.src = '';
        previewImage.alt = file.name;
        preview.style.display = 'block';
    }
    
    // Upload-Optionen ausblenden
    document.querySelector('.upload-options').style.display = 'none';
}

function resetUpload() {
    currentFile = null;
    document.getElementById('uploadPreview').style.display = 'none';
    const progressDiv = document.getElementById('uploadProgress');
    progressDiv.style.display = 'none';
    const progressFill = document.getElementById('progressFill');
    if (progressFill) progressFill.style.background = '';
    const progressText = document.getElementById('progressText');
    if (progressText) progressText.textContent = 'Wird verarbeitet...';
    document.querySelector('.upload-options').style.display = 'grid';
    document.getElementById('cameraInput').value = '';
    document.getElementById('fileInput').value = '';
    const categorySelect = document.getElementById('uploadCategory');
    if (categorySelect) categorySelect.value = '';
}

async function handleUpload() {
    if (!currentFile) return;
    
    const progressDiv = document.getElementById('uploadProgress');
    const progressFill = document.getElementById('progressFill');
    const progressText = document.getElementById('progressText');
    
    document.getElementById('uploadPreview').style.display = 'none';
    progressDiv.style.display = 'block';
    progressFill.style.width = '0%';
    progressText.textContent = 'Wird hochgeladen...';
    
    const formData = new FormData();
    formData.append('file', currentFile);
    
    // Manuelle Kategorie mitsenden (falls gewählt)
    const categorySelect = document.getElementById('uploadCategory');
    if (categorySelect && categorySelect.value) {
        formData.append('category', categorySelect.value);
    }
    
    try {
        const token = localStorage.getItem('auth_token');
        
        const xhr = new XMLHttpRequest();
        
        xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                const percentComplete = (e.loaded / e.total) * 100;
                progressFill.style.width = percentComplete + '%';
                if (percentComplete >= 100) {
                    progressText.textContent = '🔍 Beleg wird analysiert...';
                }
            }
        });
        
        xhr.addEventListener('load', async () => {
            try {
                const data = JSON.parse(xhr.responseText);
                
                if (xhr.status === 200 && data.success) {
                    progressFill.style.width = '100%';
                    
                    // OCR-Ergebnis anzeigen
                    const receipt = data.receipt || {};
                    const method = data.ocr_method || 'unknown';
                    const category = data.category || 'sonstige';
                    const suggestions = receipt.booking_suggestions || [];
                    
                    let methodLabel = '📋 Basis';
                    if (method === 'openai') methodLabel = '🤖 KI (OpenAI)';
                    else if (method === 'tesseract') methodLabel = '📝 Tesseract OCR';
                    
                    let resultHtml = `<div style="text-align:left; font-size:0.85rem; margin-top:0.75rem;">`;
                    resultHtml += `<div style="color:var(--primary-color); font-weight:600; margin-bottom:0.5rem;">✅ Erfolgreich verarbeitet!</div>`;
                    resultHtml += `<div><strong>Erkennung:</strong> ${methodLabel}</div>`;
                    resultHtml += `<div><strong>Kategorie:</strong> ${getCategoryLabel(category)}</div>`;
                    
                    if (receipt.vendor_name) resultHtml += `<div><strong>Lieferant:</strong> ${receipt.vendor_name}</div>`;
                    if (receipt.amount) resultHtml += `<div><strong>Betrag:</strong> ${formatCurrency(receipt.amount)}</div>`;
                    if (receipt.tax_rate) resultHtml += `<div><strong>USt:</strong> ${receipt.tax_rate}%</div>`;
                    if (receipt.invoice_date) resultHtml += `<div><strong>Datum:</strong> ${formatDate(receipt.invoice_date)}</div>`;
                    
                    if (suggestions.length > 0) {
                        resultHtml += `<div style="margin-top:0.5rem; padding-top:0.5rem; border-top:1px solid var(--border-color);">`;
                        resultHtml += `<strong>📊 ${suggestions.length} Buchungsvorschlag(e):</strong>`;
                        suggestions.forEach(s => {
                            resultHtml += `<div style="margin-left:0.5rem;">Konto ${s.account_number}: ${s.account_name}`;
                            if (s.debit_amount) resultHtml += ` (Soll: ${formatCurrency(s.debit_amount)})`;
                            if (s.credit_amount) resultHtml += ` (Haben: ${formatCurrency(s.credit_amount)})`;
                            resultHtml += `</div>`;
                        });
                        resultHtml += `</div>`;
                    } else {
                        resultHtml += `<div style="margin-top:0.5rem; color:var(--text-light);">ℹ️ Kein Buchungsvorschlag (ggf. Kategorie manuell ändern)</div>`;
                    }
                    
                    resultHtml += `</div>`;
                    progressText.innerHTML = resultHtml;
                    
                    // Nach 4 Sekunden schließen und Liste neu laden
                    setTimeout(() => {
                        document.getElementById('uploadModal').style.display = 'none';
                        resetUpload();
                        loadReceipts();
                    }, 4000);
                } else {
                    progressText.textContent = '❌ ' + (data.error || 'Upload fehlgeschlagen');
                    progressFill.style.background = 'var(--danger-color)';
                }
            } catch (parseError) {
                progressText.textContent = '❌ Fehler bei der Verarbeitung';
                console.error('Parse error:', parseError, xhr.responseText);
            }
        });
        
        xhr.addEventListener('error', () => {
            progressText.textContent = '❌ Netzwerkfehler beim Upload';
        });
        
        xhr.open('POST', `${API_BASE}/receipts.php`);
        xhr.setRequestHeader('X-Auth-Token', token || '');
        xhr.send(formData);
        
    } catch (error) {
        progressText.textContent = '❌ Fehler: ' + error.message;
        console.error('Upload error:', error);
    }
}

async function loadReceipts() {
    const category = document.getElementById('categoryFilter')?.value || '';
    const status = document.getElementById('statusFilter')?.value || '';
    
    const params = new URLSearchParams();
    if (category) params.append('category', category);
    if (status) params.append('status', status);
    
    const token = localStorage.getItem('auth_token');
    
    try {
        const response = await fetch(`${API_BASE}/receipts.php?${params.toString()}`, {
            headers: {
                'X-Auth-Token': token || ''
            }
        });
        
        if (!response.ok) {
            if (response.status === 401) {
                window.location.href = 'login.php';
                return;
            }
            throw new Error('Fehler beim Laden der Belege');
        }
        
        const data = await response.json();
        displayReceipts(data.receipts || []);
        
    } catch (error) {
        console.error('Error loading receipts:', error);
        document.getElementById('receiptsList').innerHTML = 
            '<div class="loading">Fehler beim Laden der Belege</div>';
    }
}

function displayReceipts(receipts) {
    const list = document.getElementById('receiptsList');
    
    if (receipts.length === 0) {
        list.innerHTML = '<div class="loading">Keine Belege gefunden</div>';
        return;
    }
    
    list.innerHTML = receipts.map(receipt => `
        <div class="receipt-card" onclick="showReceiptDetails(${receipt.id})">
            <div class="receipt-header">
                <span class="receipt-category ${receipt.category}">
                    ${getCategoryLabel(receipt.category)}
                </span>
                <span class="receipt-status">${getStatusLabel(receipt.status)}</span>
            </div>
            ${receipt.amount ? `<div class="receipt-amount">${formatCurrency(receipt.amount)}</div>` : ''}
            <div class="receipt-info">
                ${receipt.vendor_name ? `<div><strong>Lieferant:</strong> ${receipt.vendor_name}</div>` : ''}
                ${receipt.invoice_number ? `<div><strong>Rechnung:</strong> ${receipt.invoice_number}</div>` : ''}
                ${receipt.invoice_date ? `<div><strong>Datum:</strong> ${formatDate(receipt.invoice_date)}</div>` : ''}
                <div><strong>Hochgeladen:</strong> ${formatDate(receipt.created_at)}</div>
            </div>
            ${receipt.booking_suggestions && receipt.booking_suggestions.length > 0 ? 
                `<div class="receipt-info" style="margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px solid var(--border-color);">
                    <small>${receipt.booking_suggestions.length} Buchungsvorschlag(e)</small>
                </div>` : ''}
        </div>
    `).join('');
}

async function showReceiptDetails(receiptId) {
    const token = localStorage.getItem('auth_token');
    
    try {
        const response = await fetch(`${API_BASE}/receipts.php`, {
            headers: {
                'X-Auth-Token': token || ''
            }
        });
        
        if (!response.ok) throw new Error('Fehler beim Laden');
        
        const data = await response.json();
        const receipt = data.receipts.find(r => r.id == receiptId);
        
        if (!receipt) return;
        
        const modal = document.getElementById('receiptModal');
        const details = document.getElementById('receiptDetails');
        
        details.innerHTML = `
            <div class="receipt-detail">
                <div class="receipt-detail-header">
                    <span class="receipt-category ${receipt.category}">
                        ${getCategoryLabel(receipt.category)}
                    </span>
                    <span class="receipt-status">${getStatusLabel(receipt.status)}</span>
                </div>
                
                ${receipt.amount ? `
                    <div class="receipt-amount-large">
                        ${formatCurrency(receipt.amount)}
                    </div>
                ` : ''}
                
                <div class="receipt-detail-info">
                    ${receipt.vendor_name ? `
                        <div class="info-row">
                            <strong>Lieferant:</strong>
                            <span>${receipt.vendor_name}</span>
                        </div>
                    ` : ''}
                    
                    ${receipt.invoice_number ? `
                        <div class="info-row">
                            <strong>Rechnungsnummer:</strong>
                            <span>${receipt.invoice_number}</span>
                        </div>
                    ` : ''}
                    
                    ${receipt.invoice_date ? `
                        <div class="info-row">
                            <strong>Rechnungsdatum:</strong>
                            <span>${formatDate(receipt.invoice_date)}</span>
                        </div>
                    ` : ''}
                    
                    ${receipt.tax_amount ? `
                        <div class="info-row">
                            <strong>Steuerbetrag:</strong>
                            <span>${formatCurrency(receipt.tax_amount)}</span>
                        </div>
                    ` : ''}
                    
                    ${receipt.tax_rate ? `
                        <div class="info-row">
                            <strong>Steuersatz:</strong>
                            <span>${receipt.tax_rate}%</span>
                        </div>
                    ` : ''}
                    
                    <div class="info-row">
                        <strong>Hochgeladen:</strong>
                        <span>${formatDate(receipt.created_at)}</span>
                    </div>
                    
                    <div class="info-row">
                        <strong>Dateiname:</strong>
                        <span>${receipt.original_filename}</span>
                    </div>
                </div>
                
                ${receipt.booking_suggestions && receipt.booking_suggestions.length > 0 ? `
                    <div class="booking-suggestions">
                        <h3>Buchungsvorschläge (SKR 03)</h3>
                        ${receipt.booking_suggestions.map(suggestion => `
                            <div class="suggestion-item">
                                <div class="account">Konto ${suggestion.account_number}: ${suggestion.account_name}</div>
                                ${suggestion.debit_amount ? `
                                    <div class="amount">Soll: ${formatCurrency(suggestion.debit_amount)}</div>
                                ` : ''}
                                ${suggestion.credit_amount ? `
                                    <div class="amount">Haben: ${formatCurrency(suggestion.credit_amount)}</div>
                                ` : ''}
                                ${suggestion.tax_amount ? `
                                    <div class="info-row">
                                        <strong>Vorsteuer:</strong>
                                        <span>${formatCurrency(suggestion.tax_amount)}</span>
                                    </div>
                                ` : ''}
                                ${suggestion.description ? `
                                    <div class="info-row">
                                        <strong>Beschreibung:</strong>
                                        <span>${suggestion.description}</span>
                                    </div>
                                ` : ''}
                            </div>
                        `).join('')}
                    </div>
                ` : ''}
                
                <div class="receipt-actions" style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
                    <button class="btn btn-primary" onclick="viewReceiptFile(${receipt.id})">Datei anzeigen</button>
                    <button class="btn btn-secondary" onclick="deleteReceipt(${receipt.id})">Löschen</button>
                </div>
            </div>
        `;
        
        modal.style.display = 'flex';
        
    } catch (error) {
        console.error('Error loading receipt details:', error);
        alert('Fehler beim Laden der Beleg-Details');
    }
}

function viewReceiptFile(receiptId) {
    // Datei in neuem Tab öffnen
    window.open(`${API_BASE}/receipts.php?id=${receiptId}&action=view`, '_blank');
}

async function deleteReceipt(receiptId) {
    if (!confirm('Möchten Sie diesen Beleg wirklich löschen?')) return;
    
    const token = localStorage.getItem('auth_token');
    
    try {
        const response = await fetch(`${API_BASE}/receipts.php?id=${receiptId}`, {
            method: 'DELETE',
            headers: {
                'X-Auth-Token': token || ''
            }
        });
        
        if (!response.ok) throw new Error('Fehler beim Löschen');
        
        document.getElementById('receiptModal').style.display = 'none';
        loadReceipts();
        
    } catch (error) {
        console.error('Error deleting receipt:', error);
        alert('Fehler beim Löschen des Belegs');
    }
}

function getCategoryLabel(category) {
    const labels = {
        'einnahmen': 'Einnahmen',
        'ausgaben': 'Ausgaben',
        'sonstige': 'Sonstige'
    };
    return labels[category] || category;
}

function getStatusLabel(status) {
    const labels = {
        'pending': 'Ausstehend',
        'processed': 'Verarbeitet',
        'booked': 'Gebucht',
        'archived': 'Archiviert'
    };
    return labels[status] || status;
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('de-DE', {
        style: 'currency',
        currency: 'EUR'
    }).format(amount);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('de-DE', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    }).format(date);
}
