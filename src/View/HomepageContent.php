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
}
