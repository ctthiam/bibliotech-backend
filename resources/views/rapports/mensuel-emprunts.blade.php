{{-- ============================================
     resources/views/rapports/mensuel-emprunts.blade.php
     ============================================ --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport Mensuel des Emprunts</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .header .periode {
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
        }
        
        .info-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .info-box p {
            color: #666;
            margin: 5px 0;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: #fff;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
        }
        
        .stat-card.primary {
            border-color: #667eea;
            background: linear-gradient(135deg, #667eea10 0%, #764ba210 100%);
        }
        
        .stat-value {
            font-size: 36px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 8px;
        }
        
        .stat-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            font-weight: bold;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #667eea;
            margin: 30px 0 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        thead {
            background: #667eea;
            color: white;
        }
        
        th {
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
        }
        
        td {
            padding: 10px 8px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 11px;
        }
        
        tbody tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
        }
        
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }
        
        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .summary-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .summary-box h3 {
            margin-bottom: 15px;
            font-size: 16px;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        
        .summary-item {
            background: rgba(255,255,255,0.2);
            padding: 15px;
            border-radius: 6px;
        }
        
        .summary-item-label {
            font-size: 11px;
            opacity: 0.9;
            margin-bottom: 5px;
        }
        
        .summary-item-value {
            font-size: 24px;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #e0e0e0;
            text-align: center;
            font-size: 10px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 BiblioTech</h1>
        <p>Rapport Mensuel des Emprunts</p>
        <div class="periode">{{ $mois }}</div>
    </div>
    
    <div class="info-box">
        <p><strong>Période :</strong> {{ $periode }}</p>
        <p><strong>Date de génération :</strong> {{ $date_generation }}</p>
    </div>
    
    <div class="summary-box">
        <h3>📈 Résumé du Mois</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-item-label">Total Emprunts</div>
                <div class="summary-item-value">{{ $stats['total_emprunts'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-item-label">En Cours</div>
                <div class="summary-item-value">{{ $stats['en_cours'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-item-label">Terminés</div>
                <div class="summary-item-value">{{ $stats['termines'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-item-label">En Retard</div>
                <div class="summary-item-value">{{ $stats['en_retard'] }}</div>
            </div>
        </div>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-value">{{ $stats['livres_uniques'] }}</div>
            <div class="stat-label">Livres Différents</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['lecteurs_actifs'] }}</div>
            <div class="stat-label">Lecteurs Actifs</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['total_emprunts'] > 0 ? number_format(($stats['termines'] / $stats['total_emprunts']) * 100, 1) : 0 }}%</div>
            <div class="stat-label">Taux de Retour</div>
        </div>
    </div>
    
    <div class="section-title">📚 Liste Détaillée des Emprunts</div>
    
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Lecteur</th>
                <th>Livre</th>
                <th>Auteur</th>
                <th>Retour Prévu</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach($emprunts as $emprunt)
            <tr>
                <td>{{ $emprunt->date_emprunt->format('d/m/Y') }}</td>
                <td>{{ $emprunt->lecteur->user->prenom }} {{ $emprunt->lecteur->user->nom }}</td>
                <td>{{ $emprunt->exemplaire->livre->titre }}</td>
                <td>{{ $emprunt->exemplaire->livre->auteur }}</td>
                <td>{{ $emprunt->date_retour_prevue->format('d/m/Y') }}</td>
                <td>
                    @if($emprunt->statut === 'en_cours')
                        <span class="badge badge-info">En cours</span>
                    @elseif($emprunt->statut === 'termine')
                        <span class="badge badge-success">Terminé</span>
                    @else
                        <span class="badge badge-danger">En retard</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-top: 30px;">
        <h3 style="color: #667eea; margin-bottom: 15px;">📊 Analyse</h3>
        <ul style="margin-left: 20px; line-height: 2;">
            <li><strong>Activité :</strong> 
                @if($stats['total_emprunts'] > 50)
                    Très forte activité ce mois-ci
                @elseif($stats['total_emprunts'] > 20)
                    Bonne activité
                @else
                    Activité modérée
                @endif
            </li>
            <li><strong>Retards :</strong> 
                @if($stats['en_retard'] > 0)
                    {{ $stats['en_retard'] }} emprunt(s) en retard à suivre
                @else
                    Aucun retard, excellente gestion !
                @endif
            </li>
            <li><strong>Popularité :</strong> {{ $stats['livres_uniques'] }} livres différents empruntés</li>
            <li><strong>Engagement :</strong> {{ $stats['lecteurs_actifs'] }} lecteurs actifs ce mois</li>
        </ul>
    </div>
    
    <div class="footer">
        <p>Document généré automatiquement par BiblioTech le {{ $date_generation }}</p>
        <p>© {{ date('Y') }} BiblioTech - Tous droits réservés</p>
    </div>
</body>
</html>