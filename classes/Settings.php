<?php
/**
 * Zentrale Settings-Verwaltung
 * Lädt alle Einstellungen aus der Datenbank und cached sie
 */
require_once __DIR__ . '/../config/database.php';

class Settings {
    private static $instance = null;
    private $settings = [];
    private $db;
    private $loaded = false;

    private function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->loadAll();
    }

    /**
     * Singleton
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Alle Settings aus DB laden
     */
    private function loadAll() {
        if ($this->loaded) return;
        
        try {
            $stmt = $this->db->query("SELECT setting_key, setting_value FROM settings");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->settings[$row['setting_key']] = $row['setting_value'];
            }
            $this->loaded = true;
        } catch (PDOException $e) {
            // Tabelle existiert evtl. noch nicht
            $this->loaded = false;
        }
    }

    /**
     * Einzelnen Wert holen
     */
    public function get($key, $default = '') {
        return $this->settings[$key] ?? $default;
    }

    /**
     * Einzelnen Wert setzen
     */
    public function set($key, $value) {
        try {
            $stmt = $this->db->prepare(
                "UPDATE settings SET setting_value = ? WHERE setting_key = ?"
            );
            $stmt->execute([$value, $key]);
            $this->settings[$key] = $value;
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Mehrere Werte auf einmal setzen
     */
    public function setMultiple($data) {
        $success = true;
        $this->db->beginTransaction();
        
        try {
            $stmt = $this->db->prepare(
                "UPDATE settings SET setting_value = ? WHERE setting_key = ?"
            );
            foreach ($data as $key => $value) {
                $stmt->execute([$value, $key]);
                $this->settings[$key] = $value;
            }
            $this->db->commit();
        } catch (PDOException $e) {
            $this->db->rollBack();
            $success = false;
        }
        
        return $success;
    }

    /**
     * Alle Settings einer Gruppe holen (für Admin-Seite)
     */
    public function getGroup($group) {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM settings WHERE setting_group = ? ORDER BY setting_order ASC"
            );
            $stmt->execute([$group]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Alle Gruppen holen
     */
    public function getGroups() {
        return [
            'company' => ['label' => 'Firmendaten', 'icon' => '🏢'],
            'app'     => ['label' => 'App & Domain', 'icon' => '🌐'],
            'api'     => ['label' => 'API-Schlüssel', 'icon' => '🔑'],
            'legal'   => ['label' => 'Rechtliches', 'icon' => '⚖️'],
            'mail'    => ['label' => 'E-Mail (SMTP)', 'icon' => '📧'],
        ];
    }

    /**
     * Shortcut-Methoden für häufig genutzte Werte
     */
    public function companyName()    { return $this->get('company_name', 'RD Formstack Solutions'); }
    public function companyOwner()   { return $this->get('company_owner'); }
    public function companyForm()    { return $this->get('company_legal_form'); }
    public function companyStreet()  { return $this->get('company_street'); }
    public function companyZip()     { return $this->get('company_zip'); }
    public function companyCity()    { return $this->get('company_city'); }
    public function companyCountry() { return $this->get('company_country', 'Deutschland'); }
    public function companyPhone()   { return $this->get('company_phone'); }
    public function companyEmail()   { return $this->get('company_email'); }
    public function companyWebsite() { return $this->get('company_website'); }
    public function companyUstId()   { return $this->get('company_ust_id'); }
    public function companyTaxNr()   { return $this->get('company_tax_number'); }
    public function isKleinunternehmer() { return (bool) $this->get('company_kleinunternehmer', false); }
    
    public function appName()        { return $this->get('app_name', 'RD Formstack Solutions'); }
    public function appDomain()      { return $this->get('app_domain', ''); }
    public function appDescription() { return $this->get('app_description', ''); }
    public function appKeywords()    { return $this->get('app_keywords', ''); }
    
    public function openAiKey()      { return $this->get('api_openai_key', ''); }
    public function openAiModel()    { return $this->get('api_openai_model', 'gpt-4-turbo-preview'); }
    public function lexofficeKey()   { return $this->get('api_lexoffice_key', ''); }

    /**
     * Vollständige Adresse als HTML
     */
    public function companyAddressHtml() {
        $parts = [];
        if ($this->companyName()) $parts[] = '<strong>' . htmlspecialchars($this->companyName()) . '</strong>';
        if ($this->companyOwner()) $parts[] = 'Inh. ' . htmlspecialchars($this->companyOwner());
        if ($this->companyForm()) $parts[] = htmlspecialchars($this->companyForm());
        if ($this->companyStreet()) $parts[] = htmlspecialchars($this->companyStreet());
        $zipCity = trim($this->companyZip() . ' ' . $this->companyCity());
        if ($zipCity) $parts[] = htmlspecialchars($zipCity);
        if ($this->companyCountry() && $this->companyCountry() !== 'Deutschland') {
            $parts[] = htmlspecialchars($this->companyCountry());
        }
        return implode('<br>', $parts);
    }

    /**
     * Cache leeren (nach Update)
     */
    public function reload() {
        $this->settings = [];
        $this->loaded = false;
        $this->loadAll();
    }
}
