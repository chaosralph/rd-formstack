/**
 * DMS App – Hauptseite JavaScript
 * Dokument-Galerie, Suche, Filter, Modals
 */

const DMS = {
    baseUrl: document.querySelector('meta[name="base-url"]')?.content
             || window.location.pathname.replace(/\/[^/]*$/, ''),
    currentCategory: null,
    currentPage: 1,
    searchTimeout: null,
    categories: [],
};

// ===== API Helpers =====

async function apiGet(url) {
    const resp = await fetch(url, { credentials: 'same-origin' });
    if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
    return resp.json();
}

async function apiDelete(url) {
    const resp = await fetch(url, { method: 'DELETE', credentials: 'same-origin' });
    if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
    return resp.json();
}

async function apiPut(url, data) {
    const resp = await fetch(url, {
        method: 'PUT',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
    });
    if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
    return resp.json();
}

async function apiPost(url, data) {
    const resp = await fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
    });
    if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
    return resp.json();
}

// ===== Toast Notifications =====

function showToast(message, type = 'info') {
    const container = document.getElementById('toastContainer');
    if (!container) return;
    const toast = document.createElement('div');
    toast.className = `dms-toast ${type}`;

    const icons = { success: 'check_circle', error: 'error', info: 'info' };
    toast.innerHTML = `
        <span class="material-icons-round" style="font-size:1.25rem;color:var(--${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'primary'})">${icons[type] || 'info'}</span>
        <span>${message}</span>
    `;
    container.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// ===== Modal Management =====

function openModal(id) {
    document.getElementById(id)?.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal(id) {
    document.getElementById(id)?.classList.remove('active');
    document.body.style.overflow = '';
}

document.addEventListener('click', (e) => {
    if (e.target.classList.contains('dms-modal-overlay') && e.target.classList.contains('active')) {
        e.target.classList.remove('active');
        document.body.style.overflow = '';
    }
});

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.dms-modal-overlay.active').forEach((m) => {
            m.classList.remove('active');
        });
        document.body.style.overflow = '';
    }
});

// ===== Document Grid =====

async function loadDocuments(page = 1) {
    const grid = document.getElementById('documentGrid');
    const empty = document.getElementById('emptyState');
    const pagination = document.getElementById('pagination');

    if (!grid) return;

    let url = `api/documents.php?page=${page}`;
    if (DMS.currentCategory) url += `&category=${DMS.currentCategory}`;

    const search = document.getElementById('searchInput')?.value?.trim();
    if (search) url += `&search=${encodeURIComponent(search)}`;

    try {
        const data = await apiGet(url);
        const docs = data.documents || [];
        const pag = data.pagination || {};

        DMS.currentPage = page;

        if (docs.length === 0) {
            grid.innerHTML = '';
            grid.style.display = 'none';
            empty.style.display = '';
            pagination.innerHTML = '';
            return;
        }

        grid.style.display = '';
        empty.style.display = 'none';

        grid.innerHTML = docs.map((doc) => `
            <div class="dms-card" onclick="showDocumentDetail(${doc.id})" data-id="${doc.id}">
                <div class="dms-card-actions">
                    <button class="dms-card-action-btn" onclick="event.stopPropagation();downloadDocument(${doc.id})" title="Herunterladen">
                        <span class="material-icons-round" style="font-size:1rem">download</span>
                    </button>
                    <button class="dms-card-action-btn" onclick="event.stopPropagation();openEditModal(${doc.id})" title="Bearbeiten">
                        <span class="material-icons-round" style="font-size:1rem">edit</span>
                    </button>
                    <button class="dms-card-action-btn danger" onclick="event.stopPropagation();confirmDelete(${doc.id},'${escapeHtml(doc.title)}')" title="Löschen">
                        <span class="material-icons-round" style="font-size:1rem">delete</span>
                    </button>
                </div>
                ${doc.thumbnail
                    ? `<div class="dms-card-thumb"><img src="uploads/pdfs/${doc.thumbnail}" alt="${escapeHtml(doc.title)}" loading="lazy"></div>`
                    : `<div class="dms-card-thumb-placeholder"><span class="material-icons-round" style="font-size:inherit">picture_as_pdf</span></div>`
                }
                <div class="dms-card-body">
                    <div class="dms-card-title">${escapeHtml(doc.title)}</div>
                    ${doc.description ? `<div class="dms-card-desc">${escapeHtml(doc.description)}</div>` : ''}
                    <div class="dms-card-meta">
                        <span>
                            ${doc.category_name
                                ? `<span class="dms-card-badge" style="background:${doc.category_color || '#6b7280'}">${escapeHtml(doc.category_name)}</span>`
                                : ''
                            }
                        </span>
                        <span>${doc.page_count} ${doc.page_count === 1 ? 'Seite' : 'Seiten'} &middot; ${doc.pdf_size_formatted}</span>
                    </div>
                </div>
            </div>
        `).join('');

        renderPagination(pag);
    } catch (err) {
        showToast('Fehler beim Laden der Dokumente: ' + err.message, 'error');
    }
}

function renderPagination(pag) {
    const container = document.getElementById('pagination');
    if (!container || pag.total_pages <= 1) {
        if (container) container.innerHTML = '';
        return;
    }

    let html = '';
    html += `<button class="dms-pagination-btn" onclick="loadDocuments(${pag.page - 1})" ${pag.page <= 1 ? 'disabled' : ''}>
        <span class="material-icons-round" style="font-size:1rem">chevron_left</span>
    </button>`;

    for (let i = 1; i <= pag.total_pages; i++) {
        if (pag.total_pages > 7 && Math.abs(i - pag.page) > 2 && i !== 1 && i !== pag.total_pages) {
            if (i === 2 || i === pag.total_pages - 1) html += '<span style="color:var(--text-muted);padding:0 0.25rem">...</span>';
            continue;
        }
        html += `<button class="dms-pagination-btn ${i === pag.page ? 'active' : ''}" onclick="loadDocuments(${i})">${i}</button>`;
    }

    html += `<button class="dms-pagination-btn" onclick="loadDocuments(${pag.page + 1})" ${pag.page >= pag.total_pages ? 'disabled' : ''}>
        <span class="material-icons-round" style="font-size:1rem">chevron_right</span>
    </button>`;

    container.innerHTML = html;
}

// ===== Document Detail =====

async function showDocumentDetail(id) {
    try {
        const data = await apiGet(`api/documents.php?id=${id}`);
        const doc = data.document;

        document.getElementById('detailModalTitle').textContent = doc.title;

        const created = new Date(doc.created_at).toLocaleDateString('de-DE', {
            day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit',
        });

        document.getElementById('detailModalBody').innerHTML = `
            ${doc.thumbnail ? `<img src="uploads/pdfs/${doc.thumbnail}" alt="" style="width:100%;max-height:300px;object-fit:contain;border-radius:var(--radius-sm);margin-bottom:1rem;background:var(--bg-primary)">` : ''}
            ${doc.description ? `<p style="color:var(--text-secondary);margin-bottom:1rem">${escapeHtml(doc.description)}</p>` : ''}
            <div class="dms-detail-grid">
                <div>
                    <div class="dms-detail-label">Kategorie</div>
                    <div class="dms-detail-value">
                        ${doc.category_name
                            ? `<span class="dms-card-badge" style="background:${doc.category_color || '#6b7280'}">${escapeHtml(doc.category_name)}</span>`
                            : '<span style="color:var(--text-muted)">Keine</span>'
                        }
                    </div>
                </div>
                <div>
                    <div class="dms-detail-label">Seiten</div>
                    <div class="dms-detail-value">${doc.page_count}</div>
                </div>
                <div>
                    <div class="dms-detail-label">Dateigröße</div>
                    <div class="dms-detail-value">${doc.pdf_size_formatted}</div>
                </div>
                <div>
                    <div class="dms-detail-label">Erstellt am</div>
                    <div class="dms-detail-value">${created}</div>
                </div>
            </div>
        `;

        document.getElementById('detailModalFooter').innerHTML = `
            <button class="btn btn-secondary" onclick="openEditModal(${doc.id});closeModal('detailModal')">
                <span class="material-icons-round" style="font-size:1rem">edit</span> Bearbeiten
            </button>
            <a href="api/export.php?id=${doc.id}" class="btn btn-primary">
                <span class="material-icons-round" style="font-size:1rem">download</span> PDF herunterladen
            </a>
        `;

        openModal('detailModal');
    } catch (err) {
        showToast('Fehler: ' + err.message, 'error');
    }
}

// ===== Edit Document =====

let editDocId = null;

async function openEditModal(id) {
    editDocId = id;
    try {
        const [docData, catData] = await Promise.all([
            apiGet(`api/documents.php?id=${id}`),
            apiGet('api/categories.php'),
        ]);
        const doc = docData.document;
        const cats = catData.categories || [];

        document.getElementById('editModalBody').innerHTML = `
            <div class="dms-form-group">
                <label class="dms-form-label">Titel</label>
                <input type="text" class="dms-form-input" id="editTitle" value="${escapeAttr(doc.title)}">
            </div>
            <div class="dms-form-group">
                <label class="dms-form-label">Kategorie</label>
                <select class="dms-form-select" id="editCategory">
                    <option value="">Keine Kategorie</option>
                    ${cats.map((c) => `<option value="${c.id}" ${c.id == doc.category_id ? 'selected' : ''}>${escapeHtml(c.name)}</option>`).join('')}
                </select>
            </div>
            <div class="dms-form-group">
                <label class="dms-form-label">Beschreibung</label>
                <textarea class="dms-form-textarea" id="editDescription">${escapeHtml(doc.description || '')}</textarea>
            </div>
        `;

        openModal('editModal');
    } catch (err) {
        showToast('Fehler: ' + err.message, 'error');
    }
}

async function saveDocumentEdit() {
    if (!editDocId) return;
    const btn = document.getElementById('editSaveBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="dms-spinner"></span> Speichern...';

    try {
        await apiPut(`api/document-update.php?id=${editDocId}`, {
            title: document.getElementById('editTitle').value,
            category_id: document.getElementById('editCategory').value || null,
            description: document.getElementById('editDescription').value,
        });
        closeModal('editModal');
        showToast('Dokument aktualisiert', 'success');
        loadDocuments(DMS.currentPage);
    } catch (err) {
        showToast('Fehler: ' + err.message, 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<span class="material-icons-round" style="font-size:1rem">save</span> Speichern';
    }
}

// ===== Delete Document =====

let deleteDocId = null;

function confirmDelete(id, title) {
    deleteDocId = id;
    document.getElementById('deleteDocTitle').textContent = title;
    document.getElementById('deleteConfirmBtn').onclick = executeDelete;
    openModal('deleteModal');
}

async function executeDelete() {
    if (!deleteDocId) return;
    const btn = document.getElementById('deleteConfirmBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="dms-spinner"></span> Löschen...';

    try {
        await apiDelete(`api/documents.php?id=${deleteDocId}`);
        closeModal('deleteModal');
        showToast('Dokument gelöscht', 'success');
        loadDocuments(DMS.currentPage);
    } catch (err) {
        showToast('Fehler: ' + err.message, 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<span class="material-icons-round" style="font-size:1rem">delete</span> Löschen';
        deleteDocId = null;
    }
}

// ===== Download =====

function downloadDocument(id) {
    window.location.href = `api/export.php?id=${id}`;
}

// ===== Category Filter =====

function initCategoryFilter() {
    const container = document.getElementById('categoryFilter');
    if (!container) return;

    container.addEventListener('click', (e) => {
        const chip = e.target.closest('.dms-category-chip');
        if (!chip) return;

        container.querySelectorAll('.dms-category-chip').forEach((c) => c.classList.remove('active'));
        chip.classList.add('active');

        const cat = chip.dataset.category;
        DMS.currentCategory = cat === 'all' ? null : cat;
        loadDocuments(1);
    });
}

// ===== Category Manager =====

async function openCategoryManager() {
    try {
        const data = await apiGet('api/categories.php');
        const cats = data.categories || [];

        const body = document.getElementById('categoryModalBody');
        body.innerHTML = `
            <div style="margin-bottom:1rem">
                <div style="display:flex;gap:0.5rem;margin-bottom:1rem">
                    <input type="text" class="dms-form-input" id="newCatName" placeholder="Neue Kategorie..." style="flex:1">
                    <input type="color" id="newCatColor" value="#4f46e5" style="width:40px;height:38px;border:none;background:none;cursor:pointer">
                    <button class="btn btn-primary btn-sm" onclick="addCategory()">
                        <span class="material-icons-round" style="font-size:1rem">add</span>
                    </button>
                </div>
            </div>
            <div id="categoryList">
                ${cats.map((c) => `
                    <div style="display:flex;align-items:center;gap:0.75rem;padding:0.5rem 0;border-bottom:1px solid var(--border-color)" data-cat-id="${c.id}">
                        <span class="dms-category-dot" style="background:${c.color};width:12px;height:12px"></span>
                        <span style="flex:1;font-weight:500">${escapeHtml(c.name)}</span>
                        <span style="color:var(--text-muted);font-size:0.8125rem">${c.document_count} Dok.</span>
                        <button class="btn btn-secondary btn-sm" onclick="deleteCategory(${c.id},'${escapeAttr(c.name)}')" style="padding:0.25rem 0.5rem">
                            <span class="material-icons-round" style="font-size:0.875rem">delete</span>
                        </button>
                    </div>
                `).join('')}
            </div>
        `;

        openModal('categoryModal');
    } catch (err) {
        showToast('Fehler: ' + err.message, 'error');
    }
}

async function addCategory() {
    const name = document.getElementById('newCatName').value.trim();
    const color = document.getElementById('newCatColor').value;
    if (!name) return;

    try {
        await apiPost('api/categories.php', { name, color });
        showToast('Kategorie erstellt', 'success');
        openCategoryManager();
        setTimeout(() => location.reload(), 500);
    } catch (err) {
        showToast('Fehler: ' + err.message, 'error');
    }
}

async function deleteCategory(id, name) {
    if (!confirm(`Kategorie "${name}" wirklich löschen? Dokumente werden beibehalten.`)) return;

    try {
        await apiDelete(`api/categories.php?id=${id}`);
        showToast('Kategorie gelöscht', 'success');
        openCategoryManager();
        setTimeout(() => location.reload(), 500);
    } catch (err) {
        showToast('Fehler: ' + err.message, 'error');
    }
}

// ===== Search =====

function initSearch() {
    const input = document.getElementById('searchInput');
    if (!input) return;

    input.addEventListener('input', () => {
        clearTimeout(DMS.searchTimeout);
        DMS.searchTimeout = setTimeout(() => loadDocuments(1), 300);
    });
}

// ===== Utility =====

function escapeHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function escapeAttr(str) {
    if (!str) return '';
    return str.replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

// ===== Init =====

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('documentGrid')) {
        loadDocuments(1);
        initCategoryFilter();
        initSearch();
    }
});
