<?php

declare(strict_types=1);

namespace App\View;

final class HomepageContent
{
    /**
     * @return list<array{title:string,description:string}>
     */
    public static function services(): array
    {
        return [
            [
                'title' => 'Webentwicklung',
                'description' => 'Performante Websites und Anwendungen mit intuitiver Navigation, klarer Struktur und langlebiger Codebasis.',
            ],
            [
                'title' => 'Digitale Lösungen',
                'description' => 'Individuelle Workflows und Integrationen, die manuelle Übergaben reduzieren und den Prozessfluss verbessern.',
            ],
            [
                'title' => 'Belegverwaltung',
                'description' => 'Nachvollziehbare Erfassung und Zuordnung von Belegen für schnellere Bearbeitung und höhere Datenqualität.',
            ],
            [
                'title' => 'DMS',
                'description' => 'Dokumentenmanagement mit klaren Rollen, hoher Auffindbarkeit und konsistenten Freigabestrecken.',
            ],
        ];
    }

    /**
     * @return list<array{title:string,description:string}>
     */
    public static function references(): array
    {
        return [
            [
                'title' => 'Mittelstand Backoffice',
                'description' => 'Digitale Eingangsverarbeitung für Belege und Anfragen mit klarer Priorisierung und reduziertem Abstimmungsaufwand.',
            ],
            [
                'title' => 'Service-Organisation',
                'description' => 'Webbasiertes Arbeitsboard für Status, Verantwortlichkeiten und Übergaben zwischen Fachabteilungen.',
            ],
            [
                'title' => 'Verwaltung & Dokumente',
                'description' => 'Vereinheitlichte Dokumentenablage mit nachvollziehbaren Freigaben und schneller Auffindbarkeit.',
            ],
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
}
