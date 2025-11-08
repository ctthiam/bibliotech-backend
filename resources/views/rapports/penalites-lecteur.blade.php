{{-- ============================================
     resources/views/rapports/penalites-lecteur.blade.php
     ============================================ --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Pénalités</title>
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
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
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
            background: #fff5f5;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #f5576c;
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
            grid-template-columns: repeat(4, 1fr);
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
        
        .stat-card.danger {
            border-color: #f5576c;
            background: #fff5f5;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-value.danger {
            color: #f5576c;
        }
        
        .stat-value.success {
            color: #4ade80;
        }
        
        .stat-label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
        }
        
        .alert {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin-bottom: 30px;
            border-radius: 4px;
        }
        
        .alert strong {
            color: #856404;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        thead {
            background: #f5576c;
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
            background: #fff5f5;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .badge-danger {
            background: #fee;
            color: #c00;
        }
        
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-secondary {
            background: #e2e8f0;
            color: #64748b;
        }
        
        .montant {
            font-weight: bold;
            font-size: 14px;
            color: #f5576c;
        }
        
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #e0e0e0;
            text-align: center;
            font-size: 10px;
            color: #999;
        }
        
        .total-box {
            background: #fff5f5;
            border: 2px solid #f5576c;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .total-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .total-amount {
            font-size: 36px;
            font-weight: bold;
            color: #f5576c;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>💰 BiblioTech</h1>
        <p>Mes Pénalités</p>
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
    
    @if($impayees > 0)
    <div class="alert">
        <strong>⚠️ Attention !</strong> Vous avez {{ $impayees }} pénalité(s) impayée(s) pour un montant total de {{ number_format($montant_total_impaye, 0, ',', ' ') }} FCFA.
    </div>
    @endif
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{{ $total_penalites }}</div>
            <div class="stat-label">Total Pénalités</div>
        </div>
        <div class="stat-card danger">
            <div class="stat-value danger">{{ $impayees }}</div>
            <div class="stat-label">Impayées</div>
        </div>
        <div class="stat-card">
            <div class="stat-value success">{{ $total_penalites - $impayees }}</div>
            <div class="stat-label">Payées</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ number_format($montant_total_paye + $montant_total_impaye, 0, ',', ' ') }}</div>
            <div class="stat-label">Total (FCFA)</div>
        </div>
    </div>
    
    @if($montant_total_impaye > 0)
    <div class="total-box">
        <div class="total-label">Montant Total À Payer</div>
        <div class="total-amount">{{ number_format($montant_total_impaye, 0, ',', ' ') }} FCFA</div>
    </div>
    @endif
    
    <h2 style="margin-bottom: 20px; color: #f5576c;">Détail des Pénalités</h2>
    
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Livre</th>
                <th>Motif</th>
                <th>Montant</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach($penalites as $penalite)
            <tr>
                <td>{{ \Carbon\Carbon::parse($penalite->date_creation)->format('d/m/Y') }}</td>
                <td>
                    @if($penalite->emprunt && $penalite->emprunt->exemplaire)
                        {{ $penalite->emprunt->exemplaire->livre->titre }}
                    @else
                        -
                    @endif
                </td>
                <td>{{ $penalite->motif }}</td>
                <td class="montant">{{ number_format($penalite->montant, 0, ',', ' ') }} FCFA</td>
                <td>
                    @if($penalite->statut === 'impayee')
                        <span class="badge badge-danger">Impayée</span>
                    @elseif($penalite->statut === 'payee')
                        <span class="badge badge-success">Payée</span>
                    @else
                        <span class="badge badge-secondary">Annulée</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-top: 30px;">
        <h3 style="color: #667eea; margin-bottom: 15px;">💡 Comment payer vos pénalités ?</h3>
        <ul style="margin-left: 20px; line-height: 2;">
            <li>Rendez-vous à la bibliothèque avec votre carte</li>
            <li>Présentez-vous au bureau d'accueil</li>
            <li>Le paiement peut se faire en espèces ou par mobile money</li>
            <li>Un reçu vous sera remis après paiement</li>
        </ul>
    </div>
    
    <div class="footer">
        <p>Document généré automatiquement par BiblioTech le {{ $date_generation }}</p>
        <p>© {{ date('Y') }} BiblioTech - Tous droits réservés</p>
    </div>
</body>
</html>