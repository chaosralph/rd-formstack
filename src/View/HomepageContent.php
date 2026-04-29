<?php

declare(strict_types=1);

namespace App\View;

final class HomepageContent
{
    /**
     * @return array{0:string,1:string}
     */
    public static function heroSecondaryCta(string $path): array
    {
        $map = [
            '/' => ['/leistungen', 'Leistungen entdecken'],
            '/leistungen' => ['/referenzen', 'Referenzen ansehen'],
            '/referenzen' => ['/kontakt', 'Projektanfrage starten'],
            '/kontakt' => ['/leistungen', 'Leistungen entdecken'],
            '/login' => ['/kontakt', 'Pilotzugang anfragen'],
            '/dms' => ['/kontakt', 'DMS-Use-Case besprechen'],
        ];

        return $map[$path] ?? ['/leistungen', 'Leistungen entdecken'];
    }

    /**
     * @return array{0:string,1:string}
     */
    public static function mobileActionCta(string $path): array
    {
        $map = [
            '/' => ['/kontakt', 'Erstgespräch'],
            '/leistungen' => ['/referenzen', 'Referenzen'],
            '/referenzen' => ['/kontakt', 'Projektstart'],
            '/login' => ['/kontakt', 'Pilotzugang'],
            '/dms' => ['/kontakt', 'DMS-Anfrage'],
        ];

        return $map[$path] ?? ['/kontakt', 'Erstgespräch'];
    }

    /**
     * @return list<array{title:string,description:string,highlights:list<string>}>
     */
    public static function services(): array
    {
        return [
            [
                'title' => 'Webentwicklung',
                'description' => 'Performante Websites und Anwendungen mit intuitiver Navigation, klarer Struktur und langlebiger Codebasis.',
                'highlights' => [
                    'Informationsarchitektur und Seitenlogik',
                    'Responsive Frontend mit klarer Nutzerführung',
                    'Saubere technische Basis für Weiterentwicklung',
                ],
            ],
            [
                'title' => 'Digitale Lösungen',
                'description' => 'Individuelle Workflows und Integrationen, die manuelle Übergaben reduzieren und den Prozessfluss verbessern.',
                'highlights' => [
                    'Abstimmungsarme Prozessübergaben',
                    'Regelbasierte Aufgaben- und Statuslogik',
                    'Nachvollziehbare Schnittstellen zu Drittsystemen',
                ],
            ],
            [
                'title' => 'Belegverwaltung',
                'description' => 'Nachvollziehbare Erfassung und Zuordnung von Belegen für schnellere Bearbeitung und höhere Datenqualität.',
                'highlights' => [
                    'Einheitliche Erfassungs- und Prüfstrecken',
                    'Transparente Zuordnung nach Vorgang und Status',
                    'Fehlerreduzierung bei Übergabe und Ablage',
                ],
            ],
            [
                'title' => 'DMS',
                'description' => 'Dokumentenmanagement mit klaren Rollen, hoher Auffindbarkeit und konsistenten Freigabestrecken.',
                'highlights' => [
                    'Versionierung und Freigabehistorie',
                    'Suche, Filter und nachvollziehbare Ablageregeln',
                    'Grundlage für revisionsnahe Dokumentenprozesse',
                ],
            ],
        ];
    }

    /**
     * @return list<array{title:string,industry:string,description:string,outcome:string,focus:list<string>}>
     */
    public static function references(): array
    {
        return [
            [
                'title' => 'Mittelstand Backoffice',
                'industry' => 'Finanznahe Verwaltung',
                'description' => 'Digitale Eingangsverarbeitung für Belege und Anfragen mit klarer Priorisierung und reduziertem Abstimmungsaufwand.',
                'outcome' => 'Schnellere Bearbeitungszeiten und klarere Verantwortlichkeiten in der täglichen Vorgangsbearbeitung.',
                'focus' => ['Workflow-Design', 'Formularlogik', 'Status-Transparenz'],
            ],
            [
                'title' => 'Service-Organisation',
                'industry' => 'Technischer Service',
                'description' => 'Webbasiertes Arbeitsboard für Status, Verantwortlichkeiten und Übergaben zwischen Fachabteilungen.',
                'outcome' => 'Weniger Rückfragen durch einheitliche Datenlage und ein klar priorisiertes Aufgabenbild.',
                'focus' => ['Web-Frontend', 'Rollenlogik', 'Prozesssteuerung'],
            ],
            [
                'title' => 'Verwaltung & Dokumente',
                'industry' => 'Öffentliche Verwaltung',
                'description' => 'Vereinheitlichte Dokumentenablage mit nachvollziehbaren Freigaben und schneller Auffindbarkeit.',
                'outcome' => 'Strukturierte Dokumentenstrecken mit stabiler Suchbarkeit und besserer Nachvollziehbarkeit.',
                'focus' => ['Dokumentenstruktur', 'Freigaben', 'Suchkonzept'],
            ],
        ];
    }

    /**
     * @return list<array{title:string,value:string}>
     */
    public static function deliveryPillars(): array
    {
        return [
            ['title' => 'Architektur', 'value' => 'Nachvollziehbar und modular geplant'],
            ['title' => 'Umsetzung', 'value' => 'In priorisierten, testbaren Inkrementen'],
            ['title' => 'Betrieb', 'value' => 'Mit Fokus auf wartbarem Tagesgeschäft'],
        ];
    }

    /**
     * @return list<array{label:string,value:string}>
     */
    public static function contactHighlights(): array
    {
        return [
            ['label' => 'Antwortzeit', 'value' => 'In der Regel innerhalb 1 Werktag'],
            ['label' => 'Projektstart', 'value' => 'Nach abgestimmtem Scope und Priorisierung'],
            ['label' => 'Fokus', 'value' => 'Web, Workflow, Belegverwaltung, DMS'],
        ];
    }

    /**
     * @return list<array{title:string,description:string}>
     */
    public static function processSteps(): array
    {
        return [
            [
                'title' => 'Analyse',
                'description' => 'Ziele, Ist-Prozesse und technische Rahmenbedingungen erfassen.',
            ],
            [
                'title' => 'Konzept',
                'description' => 'Nutzerführung, Informationsstruktur und Integrationslogik definieren.',
            ],
            [
                'title' => 'Umsetzung',
                'description' => 'Schrittweise Entwicklung mit transparenten Übergaben und kurzer Feedbackschleife.',
            ],
            [
                'title' => 'Optimierung',
                'description' => 'Feinschliff auf Basis realer Nutzung und betrieblicher Anforderungen.',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function loginFeatures(): array
    {
        return [
            'Benutzerverwaltung mit Rollenmodell',
            'Übersicht offener Vorgänge',
            'Direkte Kommunikation zu Projekten',
        ];
    }

    /**
     * @return list<string>
     */
    public static function dmsRoadmap(): array
    {
        return [
            'Suche und Filter für Dokumente',
            'Versions- und Freigabeprotokolle',
            'Import/Export über definierte Schnittstellen',
        ];
    }

    /**
     * @return list<array{phase:string,focus:string}>
     */
    public static function loginPhases(): array
    {
        return [
            ['phase' => 'Phase 1', 'focus' => 'Rollen- und Rechtebasis mit sicherer Anmeldung'],
            ['phase' => 'Phase 2', 'focus' => 'Persönliche Dashboards und Vorgangsübersicht'],
            ['phase' => 'Phase 3', 'focus' => 'Projektkommunikation und Benachrichtigungslogik'],
        ];
    }

    /**
     * @return list<array{phase:string,focus:string}>
     */
    public static function dmsPhases(): array
    {
        return [
            ['phase' => 'Phase 1', 'focus' => 'Dokumentensuche mit Filter- und Kontextansicht'],
            ['phase' => 'Phase 2', 'focus' => 'Versionierung, Freigaben und Änderungsverlauf'],
            ['phase' => 'Phase 3', 'focus' => 'Import-/Export-Strecken mit klaren Übergaberegeln'],
        ];
    }

    /**
     * @return list<array{question:string,answer:string}>
     */
    public static function faqs(): array
    {
        return [
            [
                'question' => 'Wie schnell kann ein Projekt starten?',
                'answer' => 'Nach dem Erstgespräch erhalten Sie eine realistische Einschätzung zu Umfang, Prioritäten und möglichem Startfenster.',
            ],
            [
                'question' => 'Können bestehende Prozesse übernommen werden?',
                'answer' => 'Ja. Wir analysieren bestehende Abläufe und migrieren nur dort, wo ein klarer Nutzen entsteht.',
            ],
            [
                'question' => 'Sind Login- und DMS-Bereich schon produktiv nutzbar?',
                'answer' => 'Aktuell sind beide Bereiche als technische Platzhalter angelegt und werden schrittweise mit Funktionen ausgebaut.',
            ],
        ];
    }

    /**
     * @return list<array{title:string,text:string}>
     */
    public static function nextSteps(): array
    {
        return [
            [
                'title' => '1. Kurzgespräch',
                'text' => 'Sie schildern Ziel und Rahmen in 20-30 Minuten.',
            ],
            [
                'title' => '2. Konkrete Empfehlung',
                'text' => 'Sie erhalten eine priorisierte, realistische Umsetzungsskizze.',
            ],
            [
                'title' => '3. Umsetzungsstart',
                'text' => 'Wir starten mit einem klar geschnittenen, testbaren ersten Inkrement.',
            ],
        ];
    }
}
