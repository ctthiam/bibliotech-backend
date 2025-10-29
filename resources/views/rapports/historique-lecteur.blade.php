{{-- ============================================
     resources/views/rapports/historique-lecteur.blade.php
     ============================================ --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique des Emprunts</title>
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
        
        .header p {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .info-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .info-label {
            font-weight: bold;
            color: #666;
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
            padding: 15px;
            text-align: center;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
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
            padding: 12px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }
        
        td {
            padding: 10px 12px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        tbody tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
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
        <h1>📚 BiblioTech</h1>
        <p>Historique des Emprunts</p>
    </div>
    
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Lecteur :</span>
            <span>{{ $lecteur->user->prenom }} {{ $lecteur->user->nom }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Email :</span>
            <span>{{ $lecteur->user->email }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Numéro de carte :</span>
            <span>{{ $lecteur->numero_carte }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Date de génération :</span>
            <span>{{ $date_generation }}</span>
        </div>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{{ $total_emprunts }}</div>
            <div class="stat-label">Total Emprunts</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $en_cours }}</div>
            <div class="stat-label">En Cours</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $termines }}</div>
            <div class="stat-label">Terminés</div>
        </div>
    </div>
    
    <h2 style="margin-bottom: 20px; color: #667eea;">Liste des Emprunts</h2>
    
    <table>
        <thead>
            <tr>
                <th>Livre</th>
                <th>Auteur</th>
                <th>Date Emprunt</th>
                <th>Date Retour</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach($emprunts as $emprunt)
            <tr>
                <td>{{ $emprunt->exemplaire->livre->titre }}</td>
                <td>{{ $emprunt->exemplaire->livre->auteur }}</td>
                <td>{{ $emprunt->date_emprunt->format('d/m/Y') }}</td>
                <td>{{ $emprunt->date_retour_prevue->format('d/m/Y') }}</td>
                <td>
                    @if($emprunt->statut === 'en_cours')
                        <span class="badge badge-warning">En cours</span>
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
    
    <div class="footer">
        <p>Document généré automatiquement par BiblioTech le {{ $date_generation }}</p>
        <p>© {{ date('Y') }} BiblioTech - Tous droits réservés</p>
    </div>
</body>
</html>