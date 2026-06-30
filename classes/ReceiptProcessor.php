<?php
/**
 * Belegverarbeitung und OCR
 * 
 * Erkennung-Pipeline:
 * 1. OpenAI Vision API (wenn API-Key vorhanden) → beste Erkennung
 * 2. Tesseract OCR (wenn installiert) → lokale Erkennung
 * 3. Basis-Fallback → manuelle Eingabe nötig
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

class ReceiptProcessor {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Verarbeitet einen hochgeladenen Beleg
     */
    public function processReceipt($file, $userId, $manualCategory = null) {
        // Datei validieren
        $validation = $this->validateFile($file);
        if (!$validation['valid']) {
            return ['success' => false, 'error' => $validation['error']];
        }
        
        // Datei speichern (inkl. optionaler PDF-Konvertierung)
        $savedFile = $this->saveFile($file, $userId);
        if (!$savedFile['success']) {
            return $savedFile;
        }
        
        // OCR durchführen (Pipeline: OpenAI → Tesseract → Fallback)
        // Für die OCR bevorzugen wir ggf. eine separate Bilddatei (z.B. aus PDF gerendert)
        $ocrFilePath = $savedFile['ocr_file_path'] ?? $savedFile['file_path'];
        $ocrMimeType = $savedFile['ocr_file_type'] ?? ($savedFile['file_type'] ?? ($file['type'] ?? ''));
        $ocrResult = $this->performOCR($ocrFilePath, $ocrMimeType);
        
        // Kategorie bestimmen
        if ($manualCategory && in_array($manualCategory, ['einnahmen', 'ausgaben', 'sonstige'])) {
            $category = $manualCategory;
        } else {
            $category = $this->determineCategory($ocrResult);
        }
        
        // Beleg in Datenbank speichern
        $receiptId = $this->saveReceiptToDatabase($userId, $savedFile, $ocrResult, $category);
        
        // Buchungsvorschläge generieren (auch bei "sonstige" wenn Betrag erkannt)
        $bookingSuggestions = [];
        if (in_array($category, ['einnahmen', 'ausgaben'])) {
            $bookingSuggestions = $this->generateBookingSuggestions($receiptId, $category, $ocrResult);
        } elseif ($category === 'sonstige' && !empty($ocrResult['amount'])) {
            // Bei "sonstige" mit erkanntem Betrag → als Ausgabe vorschlagen
            $bookingSuggestions = $this->generateBookingSuggestions($receiptId, 'ausgaben', $ocrResult);
        }
        
        return [
            'success' => true,
            'receipt_id' => $receiptId,
            'category' => $category,
            'ocr_result' => $ocrResult,
            'booking_suggestions' => $bookingSuggestions,
            'ocr_method' => $ocrResult['method'] ?? 'unknown'
        ];
    }
    
    /**
     * Validiert die hochgeladene Datei
     */
    private function validateFile($file) {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return ['valid' => false, 'error' => 'Keine Datei hochgeladen'];
        }
        
        if ($file['size'] > UPLOAD_MAX_SIZE) {
            return ['valid' => false, 'error' => 'Datei zu groß (max. ' . (UPLOAD_MAX_SIZE / 1024 / 1024) . 'MB)'];
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ALLOWED_EXTENSIONS)) {
            return ['valid' => false, 'error' => 'Ungültiger Dateityp. Erlaubt: ' . implode(', ', ALLOWED_EXTENSIONS)];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Speichert die Datei auf dem Server
     */
    private function saveFile($file, $userId) {
        $uploadDir = UPLOAD_DIR . $userId . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $originalExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $originalFilename = $file['name'];
        $tmpPath = $file['tmp_name'];
        $mimeType = $file['type'] ?? '';
        
        $baseName = uniqid('receipt_', true);
        $storedExtension = $originalExtension;
        $storedMimeType = $mimeType;
        
        // Zielpfad für ursprüngliche Datei
        $rawFilename = $baseName . '.' . $originalExtension;
        $rawPath = $uploadDir . $rawFilename;
        
        if (!move_uploaded_file($tmpPath, $rawPath)) {
            return ['success' => false, 'error' => 'Fehler beim Speichern der Datei'];
        }
        
        $storedFilePath = $rawPath;
        $ocrFilePath = $rawPath;
        $ocrFileType = $mimeType;
        
        $isImage = in_array($originalExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'heic', 'heif']);
        $isPdf = $originalExtension === 'pdf';
        
        // 1) Wenn Bild: optional als PDF speichern, Bild für OCR behalten
        if ($isImage && class_exists('\Imagick')) {
            try {
                $img = new \Imagick($rawPath);
                $img->setImageFormat('pdf');
                
                $pdfFilename = $baseName . '.pdf';
                $pdfPath = $uploadDir . $pdfFilename;
                $img->writeImage($pdfPath);
                
                // Gespeicherte Hauptdatei ist jetzt PDF
                $storedFilePath = $pdfPath;
                $storedExtension = 'pdf';
                $storedMimeType = 'application/pdf';
                
                // Für OCR weiter das ursprüngliche Bild verwenden (bessere Erkennung)
                $ocrFilePath = $rawPath;
                $ocrFileType = $mimeType;
            } catch (\Throwable $e) {
                // Fallback: Bild bleibt im Originalformat gespeichert
                $storedFilePath = $rawPath;
                $storedExtension = $originalExtension;
                $storedMimeType = $mimeType;
                $ocrFilePath = $rawPath;
                $ocrFileType = $mimeType;
            }
        }
        
        // 2) Wenn PDF: optional Vorschaubild für OCR erzeugen
        if ($isPdf && class_exists('\Imagick')) {
            try {
                $img = new \Imagick();
                $img->setResolution(200, 200);
                $img->readImage($storedFilePath . '[0]'); // erste Seite
                $img->setImageFormat('jpeg');
                
                $previewFilename = $baseName . '_ocr.jpg';
                $previewPath = $uploadDir . $previewFilename;
                $img->writeImage($previewPath);
                
                // Hauptdatei bleibt PDF, OCR arbeitet mit gerendertem Bild
                $ocrFilePath = $previewPath;
                $ocrFileType = 'image/jpeg';
                
                $storedExtension = 'pdf';
                $storedMimeType = 'application/pdf';
            } catch (\Throwable $e) {
                // Fallback: OCR arbeitet direkt auf der PDF-Datei (Tesseract kann ggf. konfiguriert werden)
                $ocrFilePath = $storedFilePath;
                $ocrFileType = $storedMimeType;
            }
        }
        
        return [
            'success' => true,
            'filename' => basename($storedFilePath),
            'original_filename' => $originalFilename,
            'file_path' => $storedFilePath,
            'file_type' => $storedMimeType,
            'file_size' => $file['size'],
            'ocr_file_path' => $ocrFilePath,
            'ocr_file_type' => $ocrFileType,
        ];
    }
    
    /**
     * Führt OCR auf dem Beleg durch (Pipeline)
     */
    private function performOCR($filePath, $mimeType = '') {
        // 1. Versuche OpenAI Vision API (beste Erkennung)
        $openaiKey = getSettingFromDb('api_openai_key', '');
        if (!empty($openaiKey) && $this->isImageFile($filePath)) {
            $result = $this->performOpenAIOCR($filePath, $mimeType, $openaiKey);
            if ($result && !empty($result['text']) && $result['confidence'] > 0.3) {
                $result['method'] = 'openai';
                return $result;
            }
        }
        
        // 2. Versuche Tesseract OCR (falls installiert)
        if (OCR_SERVICE === 'tesseract' && $this->isTesseractAvailable()) {
            $result = $this->performTesseractOCR($filePath);
            if (!empty($result['text']) && $result['text'] !== '') {
                $result['method'] = 'tesseract';
                return $result;
            }
        }
        
        // 3. Fallback: Basis-Erkennung
        $result = $this->performBasicRecognition($filePath);
        $result['method'] = 'fallback';
        return $result;
    }
    
    /**
     * Prüft ob es ein Bild ist (für OpenAI Vision)
     */
    private function isImageFile($filePath) {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }
    
    /**
     * OpenAI Vision API - Belegerkennung
     */
    private function performOpenAIOCR($filePath, $mimeType, $apiKey) {
        try {
            // Bild als Base64
            $imageData = file_get_contents($filePath);
            if ($imageData === false) return null;
            
            $base64Image = base64_encode($imageData);
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $mime = $mimeType ?: ('image/' . ($ext === 'jpg' ? 'jpeg' : $ext));
            
            $model = getSettingFromDb('api_openai_model', 'gpt-4o-mini');
            // Für Vision brauchen wir ein Vision-fähiges Modell
            if (strpos($model, 'gpt-4') === false && strpos($model, 'gpt-4o') === false) {
                $model = 'gpt-4o-mini';
            }
            
            $prompt = 'Analysiere diesen Beleg/Rechnung und extrahiere folgende Informationen als JSON. 
Antworte NUR mit einem validen JSON-Objekt, kein anderer Text davor oder danach.

Erwartete Felder:
{
  "vendor_name": "Firmenname des Lieferanten/Verkäufers",
  "invoice_number": "Rechnungsnummer (oder null)",
  "invoice_date": "Datum im Format YYYY-MM-DD (oder null)",
  "amount": Gesamtbetrag als Zahl (Brutto, z.B. 119.00),
  "tax_amount": Steuerbetrag als Zahl (z.B. 19.00, oder null),
  "tax_rate": Steuersatz als Zahl (z.B. 19, 7, 0, oder null),
  "category": "einnahmen" oder "ausgaben" (was ist es aus Sicht des Empfängers? Rechnung an mich = ausgaben, Rechnung von mir = einnahmen),
  "description": "Kurze Beschreibung was gekauft/verkauft wurde",
  "expense_type": "Kategorie der Ausgabe: wareneingang, buero, werbung, reise, bewirtung, miete, versicherung, telefon, fahrzeug, sonstige",
  "text": "Vollständiger erkannter Text des Belegs"
}';

            $requestBody = [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            ['type' => 'text', 'text' => $prompt],
                            [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => "data:{$mime};base64,{$base64Image}",
                                    'detail' => 'high'
                                ]
                            ]
                        ]
                    ]
                ],
                'max_tokens' => 2000,
                'temperature' => 0.1
            ];
            
            $ch = curl_init('https://api.openai.com/v1/chat/completions');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($requestBody),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $apiKey
                ],
                CURLOPT_TIMEOUT => 60,
                CURLOPT_SSL_VERIFYPEER => true
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($httpCode !== 200 || empty($response)) {
                error_log("OpenAI API Error: HTTP {$httpCode}, cURL: {$curlError}, Response: " . substr($response, 0, 500));
                return null;
            }
            
            $responseData = json_decode($response, true);
            $content = $responseData['choices'][0]['message']['content'] ?? '';
            
            if (empty($content)) return null;
            
            // JSON aus der Antwort extrahieren
            $jsonContent = $this->extractJsonFromText($content);
            if (!$jsonContent) return null;
            
            $parsed = json_decode($jsonContent, true);
            if (!$parsed) return null;
            
            return [
                'text' => $parsed['text'] ?? $content,
                'confidence' => 0.9,
                'amount' => isset($parsed['amount']) ? floatval($parsed['amount']) : null,
                'tax_amount' => isset($parsed['tax_amount']) ? floatval($parsed['tax_amount']) : null,
                'tax_rate' => isset($parsed['tax_rate']) ? floatval($parsed['tax_rate']) : null,
                'vendor_name' => $parsed['vendor_name'] ?? null,
                'invoice_number' => $parsed['invoice_number'] ?? null,
                'invoice_date' => $parsed['invoice_date'] ?? null,
                'items' => [],
                'ai_category' => $parsed['category'] ?? null,
                'ai_description' => $parsed['description'] ?? null,
                'ai_expense_type' => $parsed['expense_type'] ?? null
            ];
            
        } catch (\Exception $e) {
            error_log("OpenAI OCR Exception: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Extrahiert JSON aus Text (OpenAI gibt manchmal Markdown-Codeblocks zurück)
     */
    private function extractJsonFromText($text) {
        // Versuche direktes JSON
        $text = trim($text);
        if (substr($text, 0, 1) === '{') {
            return $text;
        }
        
        // Suche nach JSON in Markdown-Codeblock
        if (preg_match('/```(?:json)?\s*\n?([\s\S]*?)\n?```/', $text, $matches)) {
            return trim($matches[1]);
        }
        
        // Suche nach erstem { bis letztem }
        $start = strpos($text, '{');
        $end = strrpos($text, '}');
        if ($start !== false && $end !== false && $end > $start) {
            return substr($text, $start, $end - $start + 1);
        }
        
        return null;
    }
    
    /**
     * Prüft ob Tesseract verfügbar ist
     */
    private function isTesseractAvailable() {
        $output = [];
        $returnVar = 0;
        $tesseractPath = $this->getTesseractPath();
        @exec("\"$tesseractPath\" --version 2>&1", $output, $returnVar);
        return $returnVar === 0;
    }
    
    /**
     * Holt den Tesseract-Pfad aus der DB oder nutzt Default
     */
    private function getTesseractPath() {
        $path = getSettingFromDb('api_tesseract_path', '');
        return !empty($path) ? $path : 'tesseract';
    }
    
    /**
     * Führt Tesseract OCR durch
     */
    private function performTesseractOCR($filePath) {
        $output = [];
        $returnVar = 0;
        $tempFile = sys_get_temp_dir() . '/ocr_' . uniqid();
        
        $tesseractPath = $this->getTesseractPath();
        @exec("\"$tesseractPath\" \"$filePath\" \"$tempFile\" -l deu 2>&1", $output, $returnVar);
        
        $text = '';
        if (file_exists($tempFile . '.txt')) {
            $text = file_get_contents($tempFile . '.txt');
            @unlink($tempFile . '.txt');
        }
        
        if (empty(trim($text))) {
            return [
                'text' => '',
                'confidence' => 0,
                'amount' => null,
                'tax_amount' => null,
                'tax_rate' => null,
                'vendor_name' => null,
                'invoice_number' => null,
                'invoice_date' => null,
                'items' => []
            ];
        }
        
        return [
            'text' => $text,
            'confidence' => 0.7,
            'amount' => $this->extractAmount($text),
            'tax_amount' => $this->extractTaxAmount($text),
            'tax_rate' => $this->extractTaxRate($text),
            'vendor_name' => $this->extractVendorName($text),
            'invoice_number' => $this->extractInvoiceNumber($text),
            'invoice_date' => $this->extractInvoiceDate($text),
            'items' => []
        ];
    }
    
    /**
     * Basis-Erkennung (Fallback)
     */
    private function performBasicRecognition($filePath) {
        return [
            'text' => '',
            'confidence' => 0,
            'amount' => null,
            'tax_amount' => null,
            'tax_rate' => null,
            'vendor_name' => null,
            'invoice_number' => null,
            'invoice_date' => null,
            'items' => [],
            'ai_category' => null,
            'ai_description' => null,
            'ai_expense_type' => null
        ];
    }
    
    // ========================================
    // Text-Extraktion (für Tesseract)
    // ========================================
    
    private function extractAmount($text) {
        // Suche nach typischen Gesamtbeträgen
        $patterns = [
            '/(?:gesamt|summe|total|brutto|endbetrag|zu\s*zahlen|zahlbetrag)[\s:]*€?\s*([\d]{1,3}(?:[.,]\d{3})*[.,]\d{2})\s*€?/i',
            '/(?:gesamt|summe|total|brutto|endbetrag|zu\s*zahlen|zahlbetrag)[\s:]*([\d.,]+)\s*[€$]?/i',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return $this->parseGermanNumber($matches[1]);
            }
        }
        
        // Suche nach Beträgen mit €-Zeichen
        if (preg_match_all('/([\d]{1,3}(?:[.,]\d{3})*[.,]\d{2})\s*€/', $text, $matches)) {
            $amounts = array_map(function($m) {
                return $this->parseGermanNumber($m);
            }, $matches[1]);
            $amounts = array_filter($amounts, function($a) { return $a > 0; });
            if (!empty($amounts)) {
                return max($amounts);
            }
        }
        
        return null;
    }
    
    /**
     * Parst deutsche Zahlenformate (1.234,56 → 1234.56)
     */
    private function parseGermanNumber($numberStr) {
        $numberStr = trim($numberStr);
        // Wenn Komma als Dezimaltrenner (deutsch): 1.234,56
        if (preg_match('/^\d{1,3}(\.\d{3})+,\d{2}$/', $numberStr)) {
            return floatval(str_replace(',', '.', str_replace('.', '', $numberStr)));
        }
        // Einfaches Komma als Dezimaltrenner: 123,45
        if (preg_match('/^\d+,\d{2}$/', $numberStr)) {
            return floatval(str_replace(',', '.', $numberStr));
        }
        // Punkt als Dezimaltrenner: 123.45
        return floatval(str_replace(',', '.', str_replace('.', '', $numberStr)));
    }
    
    private function extractTaxAmount($text) {
        if (preg_match('/(?:ust|mwst|mehrwertsteuer|steuer|tax)[\s.:]*€?\s*([\d.,]+)\s*[€$]?/i', $text, $matches)) {
            return $this->parseGermanNumber($matches[1]);
        }
        return null;
    }
    
    private function extractTaxRate($text) {
        if (preg_match('/(\d{1,2})\s*[%]\s*(?:ust|mwst|mehrwertsteuer|steuer)/i', $text, $matches)) {
            return floatval($matches[1]);
        }
        if (preg_match('/(?:ust|mwst|mehrwertsteuer|steuer)[\s.:]*(\d{1,2})\s*[%]/i', $text, $matches)) {
            return floatval($matches[1]);
        }
        return null;
    }
    
    private function extractVendorName($text) {
        $lines = explode("\n", $text);
        foreach ($lines as $line) {
            $line = trim($line);
            if (strlen($line) > 3 && strlen($line) < 80 && preg_match('/^[A-ZÄÖÜ][A-ZÄÖÜa-zäöüß\s&.\-]+$/', $line)) {
                return $line;
            }
        }
        return null;
    }
    
    private function extractInvoiceNumber($text) {
        if (preg_match('/(?:rechnung|rechnungs-?nr|invoice|beleg-?nr|nr|no)[\s.:]*#?\s*([A-Z0-9\-\/]+)/i', $text, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }
    
    private function extractInvoiceDate($text) {
        // Deutsche Datumsformate: DD.MM.YYYY, DD/MM/YYYY
        if (preg_match('/(\d{1,2})[.\/](\d{1,2})[.\/](\d{2,4})/', $text, $matches)) {
            $day = intval($matches[1]);
            $month = intval($matches[2]);
            $year = strlen($matches[3]) === 2 ? '20' . $matches[3] : $matches[3];
            if ($day >= 1 && $day <= 31 && $month >= 1 && $month <= 12) {
                return $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
            }
        }
        return null;
    }
    
    // ========================================
    // Kategorie-Bestimmung
    // ========================================
    
    /**
     * Bestimmt die Kategorie des Belegs
     */
    private function determineCategory($ocrResult) {
        // OpenAI hat Kategorie direkt geliefert → verwenden
        if (!empty($ocrResult['ai_category']) && in_array($ocrResult['ai_category'], ['einnahmen', 'ausgaben'])) {
            return $ocrResult['ai_category'];
        }
        
        $text = strtolower($ocrResult['text'] ?? '');
        
        if (empty($text)) {
            return 'sonstige';
        }
        
        $incomeScore = 0;
        $expenseScore = 0;
        
        // Eindeutige Einnahmen-Keywords
        $incomeKeywords = [
            'rechnungsstellung' => 3, 'verkauf' => 2, 'erlös' => 3, 'einnahme' => 3,
            'zahlung erhalten' => 3, 'gutschrift' => 2, 'honorar' => 3,
            'provision' => 2, 'umsatzerlöse' => 3
        ];
        
        // Eindeutige Ausgaben-Keywords
        $expenseKeywords = [
            'einkauf' => 2, 'bezahlt' => 2, 'ausgabe' => 3, 'kosten' => 2,
            'lieferant' => 3, 'lieferung' => 2, 'rechnung von' => 3,
            'quittung' => 2, 'kassenbon' => 3, 'kassenbeleg' => 3,
            'tankstelle' => 3, 'supermarkt' => 3, 'restaurant' => 3,
            'hotel' => 2, 'rewe' => 3, 'aldi' => 3, 'lidl' => 3,
            'edeka' => 3, 'amazon' => 2, 'dhl' => 2, 'post' => 1,
            'miete' => 2, 'versicherung' => 2, 'strom' => 2, 'gas' => 1,
            'telefon' => 1, 'internet' => 1, 'tankbeleg' => 3,
            'parkgebühr' => 2, 'fahrkarte' => 2, 'büromaterial' => 3,
            'bewirtungsbeleg' => 3
        ];
        
        foreach ($incomeKeywords as $keyword => $weight) {
            if (strpos($text, $keyword) !== false) {
                $incomeScore += $weight;
            }
        }
        
        foreach ($expenseKeywords as $keyword => $weight) {
            if (strpos($text, $keyword) !== false) {
                $expenseScore += $weight;
            }
        }
        
        // "rechnung" allein ist mehrdeutig → leichter Bonus für Ausgaben
        // (Die meisten Belege die man hochlädt sind Eingangsrechnungen = Ausgaben)
        if (strpos($text, 'rechnung') !== false && $incomeScore === 0) {
            $expenseScore += 1;
        }
        
        if ($incomeScore > $expenseScore && $incomeScore >= 2) {
            return 'einnahmen';
        }
        
        if ($expenseScore > $incomeScore && $expenseScore >= 1) {
            return 'ausgaben';
        }
        
        // Wenn irgendein Betrag vorhanden → wahrscheinlich Ausgabe
        if (!empty($ocrResult['amount'])) {
            return 'ausgaben';
        }
        
        return 'sonstige';
    }
    
    // ========================================
    // Datenbank & Buchung
    // ========================================
    
    private function saveReceiptToDatabase($userId, $fileData, $ocrResult, $category) {
        $stmt = $this->db->prepare("
            INSERT INTO receipts (
                user_id, filename, original_filename, file_path, file_type, file_size,
                category, amount, tax_amount, tax_rate, vendor_name, invoice_number,
                invoice_date, ocr_data, ocr_confidence, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        
        $stmt->execute([
            $userId,
            $fileData['filename'],
            $fileData['original_filename'],
            $fileData['file_path'],
            $fileData['file_type'],
            $fileData['file_size'],
            $category,
            $ocrResult['amount'],
            $ocrResult['tax_amount'],
            $ocrResult['tax_rate'],
            $ocrResult['vendor_name'],
            $ocrResult['invoice_number'],
            $ocrResult['invoice_date'],
            json_encode($ocrResult, JSON_UNESCAPED_UNICODE),
            $ocrResult['confidence']
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Generiert Buchungsvorschläge für SKR 03
     */
    private function generateBookingSuggestions($receiptId, $category, $ocrResult) {
        $suggestions = [];
        
        if ($category === 'einnahmen') {
            $taxRate = $ocrResult['tax_rate'] ?? 19;
            $accountNumber = $this->getIncomeAccount($taxRate);
            
            $suggestions[] = [
                'receipt_id' => $receiptId,
                'account_number' => $accountNumber,
                'account_name' => SKR03_EINNAHMEN[$accountNumber] ?? 'Erlöse',
                'debit_amount' => null,
                'credit_amount' => $ocrResult['amount'],
                'tax_account' => '3806',
                'tax_amount' => $ocrResult['tax_amount'],
                'description' => ($ocrResult['ai_description'] ?? null) 
                    ? 'Erlös: ' . $ocrResult['ai_description']
                    : 'Erlös aus ' . ($ocrResult['vendor_name'] ?? 'Verkauf')
            ];
        } elseif ($category === 'ausgaben') {
            $taxRate = $ocrResult['tax_rate'] ?? 19;
            $accountNumber = $this->getExpenseAccount($ocrResult);
            
            $suggestions[] = [
                'receipt_id' => $receiptId,
                'account_number' => $accountNumber,
                'account_name' => SKR03_AUSGABEN[$accountNumber] ?? 'Betriebsausgaben',
                'debit_amount' => $ocrResult['amount'],
                'credit_amount' => null,
                'tax_account' => $taxRate == 19 ? '1406' : ($taxRate == 7 ? '1407' : null),
                'tax_amount' => $ocrResult['tax_amount'],
                'description' => ($ocrResult['ai_description'] ?? null)
                    ? 'Ausgabe: ' . $ocrResult['ai_description']
                    : 'Ausgabe: ' . ($ocrResult['vendor_name'] ?? 'Einkauf')
            ];
        }
        
        // Vorschläge in Datenbank speichern
        foreach ($suggestions as $suggestion) {
            $stmt = $this->db->prepare("
                INSERT INTO booking_suggestions (
                    receipt_id, account_number, account_name, debit_amount, credit_amount,
                    tax_account, tax_amount, description
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $suggestion['receipt_id'],
                $suggestion['account_number'],
                $suggestion['account_name'],
                $suggestion['debit_amount'],
                $suggestion['credit_amount'],
                $suggestion['tax_account'],
                $suggestion['tax_amount'],
                $suggestion['description']
            ]);
        }
        
        return $suggestions;
    }
    
    private function getIncomeAccount($taxRate) {
        if ($taxRate == 19) return '8400';
        if ($taxRate == 7) return '8401';
        if ($taxRate == 0) return '8402';
        return '8403';
    }
    
    private function getExpenseAccount($ocrResult) {
        // OpenAI hat Ausgabe-Typ geliefert
        $aiType = $ocrResult['ai_expense_type'] ?? '';
        if (!empty($aiType)) {
            $typeMap = [
                'wareneingang' => '3400',
                'buero' => '6000',
                'werbung' => '6300',
                'reise' => '6305',
                'bewirtung' => '6308',
                'miete' => '6400',
                'versicherung' => '6500',
                'telefon' => '6600',
                'fahrzeug' => '6700',
                'sonstige' => '6800'
            ];
            if (isset($typeMap[$aiType])) {
                // USt-Variante wählen
                $base = $typeMap[$aiType];
                $taxRate = $ocrResult['tax_rate'] ?? 19;
                if ($taxRate == 19 && isset(SKR03_AUSGABEN[$base + 1])) {
                    // Prüfe ob 19%-Variante existiert
                    $withTax = (string)(intval($base) + 1);
                    if (isset(SKR03_AUSGABEN[$withTax])) return $withTax;
                }
                return $base;
            }
        }
        
        // Fallback: Text-basierte Erkennung
        $text = strtolower($ocrResult['text'] ?? '');
        
        if (strpos($text, 'miete') !== false || strpos($text, 'pacht') !== false) return '6400';
        if (strpos($text, 'versicherung') !== false) return '6500';
        if (strpos($text, 'telefon') !== false || strpos($text, 'internet') !== false || strpos($text, 'mobilfunk') !== false) return '6600';
        if (strpos($text, 'fahrzeug') !== false || strpos($text, 'kraftstoff') !== false || strpos($text, 'tanken') !== false || strpos($text, 'tankstelle') !== false) return '6700';
        if (strpos($text, 'werbung') !== false || strpos($text, 'marketing') !== false) return '6300';
        if (strpos($text, 'reise') !== false || strpos($text, 'fahrkarte') !== false || strpos($text, 'flug') !== false) return '6305';
        if (strpos($text, 'bewirtung') !== false || strpos($text, 'restaurant') !== false || strpos($text, 'gaststätte') !== false) return '6308';
        if (strpos($text, 'büro') !== false || strpos($text, 'papier') !== false || strpos($text, 'toner') !== false) return '6000';
        if (strpos($text, 'ware') !== false || strpos($text, 'einkauf') !== false || strpos($text, 'material') !== false) return '3400';
        
        return '6800'; // Sonstige Betriebsausgaben
    }
}
