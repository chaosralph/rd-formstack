    </main>

    <div class="dms-toast-container" id="toastContainer"></div>

    <!-- Detail Modal -->
    <div class="dms-modal-overlay" id="detailModal">
        <div class="dms-modal">
            <div class="dms-modal-header">
                <h3 class="dms-modal-title" id="detailModalTitle">Dokument</h3>
                <button class="dms-modal-close" onclick="closeModal('detailModal')">&times;</button>
            </div>
            <div class="dms-modal-body" id="detailModalBody"></div>
            <div class="dms-modal-footer" id="detailModalFooter"></div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="dms-modal-overlay" id="editModal">
        <div class="dms-modal">
            <div class="dms-modal-header">
                <h3 class="dms-modal-title">Dokument bearbeiten</h3>
                <button class="dms-modal-close" onclick="closeModal('editModal')">&times;</button>
            </div>
            <div class="dms-modal-body" id="editModalBody"></div>
            <div class="dms-modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('editModal')">Abbrechen</button>
                <button class="btn btn-primary" id="editSaveBtn" onclick="saveDocumentEdit()">
                    <span class="material-icons-round" style="font-size:1rem">save</span>
                    Speichern
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Confirm Modal -->
    <div class="dms-modal-overlay" id="deleteModal">
        <div class="dms-modal" style="max-width:420px">
            <div class="dms-modal-header">
                <h3 class="dms-modal-title">Dokument löschen</h3>
                <button class="dms-modal-close" onclick="closeModal('deleteModal')">&times;</button>
            </div>
            <div class="dms-modal-body">
                <p>Soll das Dokument <strong id="deleteDocTitle"></strong> wirklich gelöscht werden?</p>
                <p style="color: var(--text-secondary); font-size: 0.875rem; margin-top: 0.5rem;">Diese Aktion kann nicht rückgängig gemacht werden.</p>
            </div>
            <div class="dms-modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('deleteModal')">Abbrechen</button>
                <button class="btn btn-danger" id="deleteConfirmBtn">
                    <span class="material-icons-round" style="font-size:1rem">delete</span>
                    Löschen
                </button>
            </div>
        </div>
    </div>

    <!-- Category Management Modal -->
    <div class="dms-modal-overlay" id="categoryModal">
        <div class="dms-modal">
            <div class="dms-modal-header">
                <h3 class="dms-modal-title">Kategorien verwalten</h3>
                <button class="dms-modal-close" onclick="closeModal('categoryModal')">&times;</button>
            </div>
            <div class="dms-modal-body" id="categoryModalBody"></div>
            <div class="dms-modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('categoryModal')">Schließen</button>
            </div>
        </div>
    </div>

    <script src="<?= SITE_URL ?>/assets/js/app.js"></script>
    <?php if (!empty($extraJs)): ?>
        <?php foreach ($extraJs as $js): ?>
            <script src="<?= $js ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
