<?php
// ============================================
// app/Exports/EmpruntsExport.php
// BONUS : Export des emprunts (créez-le aussi)
// ============================================
namespace App\Exports;

use App\Models\Emprunt;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class EmpruntsExport implements 
    FromQuery, 
    WithHeadings, 
    WithMapping, 
    WithStyles,
    WithTitle,
    ShouldAutoSize
{
    protected $filters;

    /**
     * Constructeur avec filtres optionnels
     */
    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * Query pour récupérer les emprunts
     */
    public function query()
    {
        $query = Emprunt::with(['lecteur.user', 'exemplaire.livre'])
            ->orderBy('date_emprunt', 'desc');

        // Appliquer les filtres
        if (isset($this->filters['statut'])) {
            $query->where('statut', $this->filters['statut']);
        }

        if (isset($this->filters['date_debut'])) {
            $query->whereDate('date_emprunt', '>=', $this->filters['date_debut']);
        }

        if (isset($this->filters['date_fin'])) {
            $query->whereDate('date_emprunt', '<=', $this->filters['date_fin']);
        }

        return $query;
    }

    /**
     * En-têtes des colonnes
     */
    public function headings(): array
    {
        return [
            'ID Emprunt',
            'Lecteur (Nom)',
            'Lecteur (Prénom)',
            'Email',
            'N° Carte',
            'Livre',
            'Auteur',
            'ISBN',
            'N° Exemplaire',
            'Date Emprunt',
            'Date Retour Prévue',
            'Date Retour Effective',
            'Jours d\'Emprunt',
            'Prolongations',
            'Statut',
            'Retard (jours)',
        ];
    }

    /**
     * Mapper chaque emprunt vers une ligne
     */
    public function map($emprunt): array
    {
        $lecteur = $emprunt->lecteur->user;
        $livre = $emprunt->exemplaire->livre;

        // Calculer les jours d'emprunt
        $dateRetour = $emprunt->date_retour_effective ?? now();
        $joursEmprunt = $emprunt->date_emprunt->diffInDays($dateRetour);

        // Calculer le retard
        $joursRetard = 0;
        if ($emprunt->statut === 'en_cours' && now()->isAfter($emprunt->date_retour_prevue)) {
            $joursRetard = now()->diffInDays($emprunt->date_retour_prevue);
        }

        return [
            $emprunt->id,
            $lecteur->nom,
            $lecteur->prenom,
            $lecteur->email,
            $emprunt->lecteur->numero_carte,
            $livre->titre,
            $livre->auteur,
            $livre->isbn,
            $emprunt->exemplaire->numero_exemplaire,
            $emprunt->date_emprunt->format('d/m/Y H:i'),
            $emprunt->date_retour_prevue->format('d/m/Y'),
            $emprunt->date_retour_effective ? $emprunt->date_retour_effective->format('d/m/Y H:i') : 'En cours',
            $joursEmprunt,
            $emprunt->nombre_prolongations,
            $this->getStatutLibelle($emprunt->statut),
            $joursRetard > 0 ? $joursRetard : '-',
        ];
    }

    /**
     * Styles du tableau
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '764ba2'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    /**
     * Titre de la feuille
     */
    public function title(): string
    {
        return 'Emprunts';
    }

    /**
     * Libellé du statut
     */
    private function getStatutLibelle($statut): string
    {
        return match($statut) {
            'en_cours' => 'En cours',
            'termine' => 'Terminé',
            'en_retard' => 'En retard',
            default => ucfirst($statut),
        };
    }
}