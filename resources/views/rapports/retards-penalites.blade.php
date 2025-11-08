{{-- ============================================
     resources/views/rapports/retards-penalites.blade.php
     ============================================ --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport Retards et Pénalités</title>
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
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            color: white;
            padding: 30px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .alert-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 4px;
        }
        
        .alert-box h3 {
            color: #856404;
            margin-bottom: 10px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
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
        
        .stat-card.danger {
            border-color: #ff6b6b;
            background: #fff5f5;
        }
        
        .stat-card.warning {
            border-color: #ffc107;
            background: #fffbf0;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        .stat-value.danger {
            color: #ff6b6b;
        }
        
        .stat-value.warning {
            color: #ffc107;
        }
        
        .stat-label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            font-weight: bold;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #ff6b6b;
            margin: 30px 0 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ff6b6b;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        thead {
            background: #ff6b6b;
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
            background: #fff5f5;
        }
        
        tbody tr.critical {
            background: #fee !important;
            border-left: 3px solid #f00;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
        }
        
        .badge-danger {
            background: #fee;
            color: #c00;
        }
        
        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge-critical {
            background: #dc3545;
            color: white;
        }
        
        .montant {
            font-weight: bold;
            font-size: 13px;
            color: #ff6b6b;
        }
        
        .jours-retard {
            font-weight: bold;
            padding: 3px 8px;
            border-radius: 4px;
            display: inline-block;
        }
        
        .retard-faible {
            background: #fff3cd;
            color: #856404;
        }
        
        .retard-moyen {
            background: #ffc107;
            color: white;
        }
        
        .retard-eleve {
            background: #ff6b6b;
            color: white;
        }
        
        .total-box {
            background: #fff5f5;
            border: 2px solid #ff6b6b;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .total-label {
            font-size: 16px;
            color: #666;
            margin-bottom: 10px;
            font-weight: bold;
        }
        
        .total-amount {
            font-size: 42px;
            font-weight: bold;
            color: #ff6b6b;
        }
        
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #e0e0e0;
            text-align: center;
            font-size: 10px;
            color: #999;
        }
        
        .action-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 20px;
            margin-top: 30px;
            border-radius: 4px;
        }
        
        .action-box h3 {
            color: #1565c0;
            margin-bottom: 15px;
        }
        
        .action-box ul {
            margin-left: 20px;
            line-height: 2;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>⚠️ BiblioTech</h1>
        <p>Rapport des Retards et Pénalités</p>
    </div>
    
    @if($stats['total_retards'] > 0 || $stats['total_penalites_impayees'] > 0)
    <div class="alert-box">
        <h3>⚠️ Actions Requises</h3>
        <p>
            <strong>{{ $stats['total_retards'] }}</strong> emprunt(s) en retard concernant 
            <strong>{{ $stats['lecteurs_concernes'] }}</strong> lecteur(s).
            <br>
            Montant total des pénalités impayées : <strong>{{ number_format($stats['montant_total_impaye'], 0, ',', ' ') }} FCFA</strong>
        </p>
    </div>
    @endif
    
    <div class="stats-grid">
        <div class="stat-card danger">
            <div class="stat-value danger">{{ $stats['total_retards'] }}</div>
            <div class="stat-label">Emprunts en Retard</div>
        </div>
        <div class="stat-card warning">
            <div class="stat-value warning">{{ $stats['total_penalites_impayees'] }}</div>
            <div class="stat-label">Pénalités Impayées</div>
        </div>
        <div class="stat-card danger">
            <div class="stat-value danger">{{ $stats['lecteurs_concernes'] }}</div>
            <div class="stat-label">Lecteurs Concernés</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ number_format($stats['montant_total_impaye'], 0) }}</div>
            <div class="stat-label">Total (FCFA)</div>
        </div>
    </div>
    
    <div class="section-title">📕 Emprunts en Retard</div>
    
    <table>
        <thead>
            <tr>
                <th>Lecteur</th>
                <th>Livre</th>
                <th>Date Retour</th>
                <th>Jours Retard</th>
                <th>Pénalité</th>
                <th>Contact</th>
            </tr>
        </thead>
        <tbody>
            @foreach($emprunts_retard as $emprunt)
            @php
                $joursRetard = now()->diffInDays($emprunt->date_retour_prevue);
                $penalite = $joursRetard * 100;
                $classeRetard = $joursRetard <= 3 ? 'retard-faible' : ($joursRetard <= 7 ? 'retard-moyen' : 'retard-eleve');
                $critical = $joursRetard > 7;
            @endphp
            <tr @if($critical) class="critical" @endif>
                <td>{{ $emprunt->lecteur->user->prenom }} {{ $emprunt->lecteur->user->nom }}</td>
                <td>{{ $emprunt->exemplaire->livre->titre }}</td>
                <td>{{ $emprunt->date_retour_prevue->format('d/m/Y') }}</td>
                <td>
                    <span class="jours-retard {{ $classeRetard }}">
                        {{ $joursRetard }} jour(s)
                    </span>
                </td>
                <td class="montant">{{ number_format($penalite, 0, ',', ' ') }} FCFA</td>
                <td>{{ $emprunt->lecteur->user->telephone ?? $emprunt->lecteur->user->email }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="section-title">💰 Pénalités Impayées</div>
    
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Lecteur</th>
                <th>Livre</th>
                <th>Motif</th>
                <th>Montant</th>
                <th>Contact</th>
            </tr>
        </thead>
        <tbody>
            @foreach($penalites as $penalite)
            <tr>
                <td>{{ \Carbon\Carbon::parse($penalite->date_creation)->format('d/m/Y') }}</td>
                <td>{{ $penalite->lecteur->user->prenom }} {{ $penalite->lecteur->user->nom }}</td>
                <td>
                    @if($penalite->emprunt && $penalite->emprunt->exemplaire)
                        {{ $penalite->emprunt->exemplaire->livre->titre }}
                    @else
                        -
                    @endif
                </td>
                <td>{{ $penalite->motif }}</td>
                <td class="montant">{{ number_format($penalite->montant, 0, ',', ' ') }} FCFA</td>
                <td>{{ $penalite->lecteur->user->telephone ?? $penalite->lecteur->user->email }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    @if($stats['montant_total_impaye'] > 0)
    <div class="total-box">
        <div class="total-label">💰 Montant Total des Pénalités Impayées</div>
        <div class="total-amount">{{ number_format($stats['montant_total_impaye'], 0, ',', ' ') }} FCFA</div>
    </div>
    @endif
    
    <div class="action-box">
        <h3>📋 Actions Recommandées</h3>
        <ul>
            <li><strong>Relance immédiate :</strong> Contacter tous les lecteurs en retard de plus de 7 jours</li>
            <li><strong>Relance par email/SMS :</strong> Envoyer des rappels automatiques aux autres</li>
            <li><strong>Suspension :</strong> Considérer la suspension des comptes en retard de plus de 30 jours</li>
            <li><strong>Collecte :</strong> Organiser une campagne de collecte des pénalités impayées</li>
            <li><strong>Prévention :</strong> Renforcer les rappels avant les dates d'échéance</li>
        </ul>
    </div>
    
    <div class="footer">
        <p>Document généré automatiquement par BiblioTech le {{ $date_generation }}</p>
        <p>© {{ date('Y') }} BiblioTech - Tous droits réservés</p>
        <p style="margin-top: 10px; color: #ff6b6b;"><strong>Document Confidentiel - Usage Interne Uniquement</strong></p>
    </div>
</body>
</html>