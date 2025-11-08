<?php
// ============================================
// app/Exports/InventaireLivresExport.php
// ============================================
namespace App\Exports;

use App\Models\Livre;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class InventaireLivresExport implements 
    FromCollection, 
    WithHeadings, 
    WithMapping, 
    WithStyles,
    WithTitle,
    ShouldAutoSize
{
    /**
     * Récupère la collection de livres avec leurs relations
     */
    public function collection()
    {
        return Livre::with(['categorie', 'exemplaires'])->get();
    }

    /**
     * Définit les en-têtes des colonnes
     */
    public function headings(): array
    {
        return [
            'ID',
            'Titre',
            'Auteur',
            'ISBN',
            'Éditeur',
            'Année',
            'Pages',
            'Langue',
            'Catégorie',
            'Total Exemplaires',
            'Disponibles',
            'Empruntés',
            'Réservés',
            'Perdus/Endommagés',
            'Statut',
            'Date d\'Ajout',
        ];
    }

    /**
     * Mappe chaque livre vers une ligne du tableau
     */
    public function map($livre): array
    {
        // Compter les exemplaires par statut
        $totalExemplaires = $livre->exemplaires->count();
        $disponibles = $livre->exemplaires->where('statut', 'disponible')->count();
        $empruntes = $livre->exemplaires->where('statut', 'emprunte')->count();
        $reserves = $livre->exemplaires->where('statut', 'reserve')->count();
        $perdusEndommages = $livre->exemplaires->whereIn('statut', ['perdu', 'endommage'])->count();

        // Déterminer le statut global
        $statut = $disponibles > 0 ? 'Disponible' : 'Indisponible';
        if ($perdusEndommages > 0) {
            $statut .= ' (⚠️ ' . $perdusEndommages . ' endommagé(s))';
        }

        return [
            $livre->id,
            $livre->titre,
            $livre->auteur,
            $livre->isbn,
            $livre->editeur ?? 'Non renseigné',
            $livre->annee_publication ?? 'N/A',
            $livre->nombre_pages ?? 'N/A',
            $livre->langue ?? 'Français',
            $livre->categorie ? $livre->categorie->nom : 'Sans catégorie',
            $totalExemplaires,
            $disponibles,
            $empruntes,
            $reserves,
            $perdusEndommages,
            $statut,
            $livre->created_at ? $livre->created_at->format('d/m/Y') : 'N/A',
        ];
    }

    /**
     * Applique les styles au tableau Excel
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style de l'en-tête (ligne 1)
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 12,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '667eea'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    /**
     * Titre de la feuille Excel
     */
    public function title(): string
    {
        return 'Inventaire Livres';
    }
}