{{-- ============================================
     resources/views/rapports/annuel.blade.php
     ============================================ --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport Annuel {{ $annee }}</title>
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
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 40px 30px;
            margin-bottom: 40px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .header .year {
            font-size: 48px;
            font-weight: bold;
            margin: 15px 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        
        .intro-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 40px;
            text-align: center;
        }
        
        .intro-box h2 {
            font-size: 20px;
            margin-bottom: 15px;
        }
        
        .intro-box p {
            font-size: 14px;
            line-height: 1.8;
            opacity: 0.95;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: #fff;
            border: 3px solid #e0e0e0;
            border-radius: 10px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .stat-card.primary {
            border-color: #4facfe;
            background: linear-gradient(135deg, #4facfe10 0%, #00f2fe10 100%);
        }
        
        .stat-card.success {
            border-color: #4ade80;
            background: linear-gradient(135deg, #4ade8010 0%, #22c55e10 100%);
        }
        
        .stat-card.warning {
            border-color: #fbbf24;
            background: linear-gradient(135deg, #fbbf2410 0%, #f59e0b10 100%);
        }
        
        .stat-icon {
            font-size: 36px;
            margin-bottom: 10px;
        }
        
        .stat-value {
            font-size: 42px;
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        .stat-value.primary {
            color: #4facfe;
        }
        
        .stat-value.success {
            color: #4ade80;
        }
        
        .stat-value.warning {
            color: #fbbf24;
        }
        
        .stat-label {
            font-size: 13px;
            color: #666;
            text-transform: uppercase;
            font-weight: bold;
        }
        
        .section {
            margin-bottom: 50px;
        }
        
        .section-title {
            font-size: 22px;
            font-weight: bold;
            color: #4facfe;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #4facfe;
        }
        
        .chart-container {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .chart-title {
            font-size: 16px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .bar-chart {
            margin: 20px 0;
        }
        
        .bar-row {
            margin-bottom: 15px;
        }
        
        .bar-label {
            font-size: 11px;
            font-weight: bold;
            color: #666;
            margin-bottom: 5px;
        }
        
        .bar-container {
            background: #e0e0e0;
            height: 30px;
            border-radius: 15px;
            overflow: hidden;
            position: relative;
        }
        
        .bar-fill {
            background: linear-gradient(90deg, #4facfe 0%, #00f2fe 100%);
            height: 100%;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 10px;
            color: white;
            font-weight: bold;
            font-size: 11px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        th {
            padding: 15px 10px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }
        
        td {
            padding: 12px 10px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        tbody tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .ranking {
            display: inline-block;
            width: 30px;
            height: 30px;
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 30px;
            font-weight: bold;
            font-size: 14px;
        }
        
        .ranking.gold {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        }
        
        .ranking.silver {
            background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%);
        }
        
        .ranking.bronze {
            background: linear-gradient(135deg, #fb923c 0%, #f97316 100%);
        }
        
        .highlight-box {
            background: linear-gradient(135deg, #4ade80 0%, #22c55e 100%);
            color: white;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .highlight-box h3 {
            font-size: 18px;
            margin-bottom: 15px;
        }
        
        .highlight-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 20px;
        }
        
        .highlight-item {
            background: rgba(255,255,255,0.2);
            padding: 15px;
            border-radius: 6px;
        }
        
        .highlight-item-label {
            font-size: 12px;
            opacity: 0.9;
            margin-bottom: 8px;
        }
        
        .highlight-item-value {
            font-size: 28px;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 60px;
            padding-top: 30px;
            border-top: 3px solid #4facfe;
            text-align: center;
        }
        
        .footer-logo {
            font-size: 36px;
            margin-bottom: 15px;
        }
        
        .footer-text {
            font-size: 11px;
            color: #999;
            margin: 5px 0;
        }
        
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 BiblioTech</h1>
        <div class="year">{{ $annee }}</div>
        <p>Rapport Annuel Complet</p>
    </div>
    
    <div class="intro-box">
        <h2>🎯 Vue d'Ensemble</h2>
        <p>
            Bienvenue dans le rapport annuel {{ $annee }} de BiblioTech. Ce document présente une analyse complète 
            de l'activité de notre bibliothèque, incluant les statistiques d'emprunts, la popularité des ouvrages, 
            et l'évolution de notre collection tout au long de l'année.
        </p>
    </div>
    
    <div class="section">
        <div class="section-title">📈 Statistiques Principales</div>
        
        <div class="stats-grid">
            <div class="stat-card primary">
                <div class="stat-icon">📚</div>
                <div class="stat-value primary">{{ $stats['total_livres'] }}</div>
                <div class="stat-label">Livres au Catalogue</div>
            </div>
            <div class="stat-card success">
                <div class="stat-icon">📖</div>
                <div class="stat-value success">{{ $stats['total_emprunts'] }}</div>
                <div class="stat-label">Emprunts Effectués</div>
            </div>
            <div class="stat-card warning">
                <div class="stat-icon">👥</div>
                <div class="stat-value warning">{{ $stats['total_lecteurs'] }}</div>
                <div class="stat-label">Lecteurs Inscrits</div>
            </div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card success">
                <div class="stat-icon">✨</div>
                <div class="stat-value success">{{ $stats['nouveaux_lecteurs'] }}</div>
                <div class="stat-label">Nouveaux Inscrits</div>
            </div>
            <div class="stat-card warning">
                <div class="stat-icon">⚠️</div>
                <div class="stat-value warning">{{ $stats['total_penalites'] }}</div>
                <div class="stat-label">Pénalités Émises</div>
            </div>
            <div class="stat-card primary">
                <div class="stat-icon">💰</div>
                <div class="stat-value primary">{{ number_format($stats['montant_penalites'], 0) }}</div>
                <div class="stat-label">Pénalités (FCFA)</div>
            </div>
        </div>
    </div>
    
    <div class="section">
        <div class="section-title">📊 Évolution Mensuelle des Emprunts</div>
        
        <div class="chart-container">
            <div class="chart-title">Nombre d'emprunts par mois</div>
            <div class="bar-chart">
                @php
                    $moisNoms = [
                        1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
                        5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
                        9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
                    ];
                    $maxEmprunts = $emprunts_par_mois->max('total') ?: 1;
                @endphp
                
                @for($i = 1; $i <= 12; $i++)
                    @php
                        $moisData = $emprunts_par_mois->firstWhere('mois', $i);
                        $total = $moisData ? $moisData->total : 0;
                        $pourcentage = ($total / $maxEmprunts) * 100;
                    @endphp
                    <div class="bar-row">
                        <div class="bar-label">{{ $moisNoms[$i] }}</div>
                        <div class="bar-container">
                            <div class="bar-fill" style="width: {{ $pourcentage }}%">
                                {{ $total }}
                            </div>
                        </div>
                    </div>
                @endfor
            </div>
        </div>
    </div>
    
    <div class="page-break"></div>
    
    <div class="section">
        <div class="section-title">🏆 Top 10 des Livres les Plus Empruntés</div>
        
        <table>
            <thead>
                <tr>
                    <th style="width: 60px;">Rang</th>
                    <th>Titre</th>
                    <th>Auteur</th>
                    <th style="width: 120px; text-align: center;">Emprunts</th>
                </tr>
            </thead>
            <tbody>
                @foreach($top_livres as $index => $livre)
                <tr>
                    <td>
                        @if($index == 0)
                            <span class="ranking gold">🥇</span>
                        @elseif($index == 1)
                            <span class="ranking silver">🥈</span>
                        @elseif($index == 2)
                            <span class="ranking bronze">🥉</span>
                        @else
                            <span class="ranking">{{ $index + 1 }}</span>
                        @endif
                    </td>
                    <td>{{ $livre->exemplaire->livre->titre }}</td>
                    <td>{{ $livre->exemplaire->livre->auteur }}</td>
                    <td style="text-align: center; font-weight: bold; color: #4facfe; font-size: 16px;">
                        {{ $livre->nb_emprunts }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div class="highlight-box">
        <h3>✨ Points Forts de l'Année</h3>
        <div class="highlight-grid">
            <div class="highlight-item">
                <div class="highlight-item-label">Taux d'Activité</div>
                <div class="highlight-item-value">
                    {{ $stats['total_lecteurs'] > 0 ? number_format(($stats['total_emprunts'] / $stats['total_lecteurs']), 1) : 0 }}
                    <small style="font-size: 16px;">emprunts/lecteur</small>
                </div>
            </div>
            <div class="highlight-item">
                <div class="highlight-item-label">Croissance</div>
                <div class="highlight-item-value">
                    +{{ $stats['nouveaux_lecteurs'] }}
                    <small style="font-size: 16px;">nouveaux lecteurs</small>
                </div>
            </div>
            <div class="highlight-item">
                <div class="highlight-item-label">Collection</div>
                <div class="highlight-item-value">
                    {{ $stats['total_livres'] }}
                    <small style="font-size: 16px;">titres disponibles</small>
                </div>
            </div>
            <div class="highlight-item">
                <div class="highlight-item-label">Taux de Retour</div>
                <div class="highlight-item-value">
                    {{ $stats['total_emprunts'] > 0 ? number_format((($stats['total_emprunts'] - $stats['total_penalites']) / $stats['total_emprunts']) * 100, 1) : 100 }}%
                </div>
            </div>
        </div>
    </div>
    
    <div class="section">
        <div class="section-title">📋 Recommandations pour {{ $annee + 1 }}</div>
        
        <div style="background: #f8f9fa; padding: 25px; border-radius: 8px; border-left: 4px solid #4facfe;">
            <ul style="margin-left: 20px; line-height: 2.5;">
                <li><strong>Développement :</strong> Enrichir la collection avec les genres les plus populaires</li>
                <li><strong>Fidélisation :</strong> Mettre en place un programme de lecteur régulier</li>
                <li><strong>Communication :</strong> Renforcer les campagnes de rappel pour réduire les retards</li>
                <li><strong>Technologie :</strong> Continuer à améliorer la plateforme BiblioTech</li>
                <li><strong>Événements :</strong> Organiser des clubs de lecture et rencontres d'auteurs</li>
            </ul>
        </div>
    </div>
    
    <div class="footer">
        <div class="footer-logo">📚 BiblioTech</div>
        <p class="footer-text">Rapport Annuel {{ $annee }}</p>
        <p class="footer-text">Document généré le {{ $date_generation }}</p>
        <p class="footer-text">© {{ date('Y') }} BiblioTech - Tous droits réservés</p>
        <p class="footer-text" style="margin-top: 15px; font-weight: bold; color: #4facfe;">
            Merci à tous nos lecteurs pour leur confiance !
        </p>
    </div>
</body>
</html>