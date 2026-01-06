<?php
$zone_choisie = null;
$date_debut = null;
$date_fin = null;

//On vérifie si le formulaire a bien été soumis
if (isset($_GET['zone']) && isset($_GET['date_debut']) && isset($_GET['date_fin'])) {
    $zone_choisie = $_GET['zone'];
    $date_debut = $_GET['date_debut'];
    $date_fin = $_GET['date_fin'];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques Océaniques</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    
    <div class="text-center mb-5">
        <h1>Observatoire Océanique</h1>
        <p class="lead">Analyse des données</p>
    </div>

    <div class="card shadow-sm mb-5">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Paramètres d'analyse</h5>
        </div>
        <div class="card-body">
            <form action="" method="GET" class="row g-3 align-items-end">
                
                <div class="col-md-4">
                    <label for="zone" class="form-label">Zone Maritime</label>
                    <select class="form-select" id="zone" name="zone" required>
                        <option value="" disabled selected>Choisir une zone...</option>
                        <!-- Les options seront chargées par l'API -->
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="date_debut" class="form-label">Date de début</label>
                    <input type="date" class="form-control" id="date_debut" name="date_debut" 
                           value="<?php echo $date_debut; ?>" required>
                </div>

                <div class="col-md-3">
                    <label for="date_fin" class="form-label">Date de fin</label>
                    <input type="date" class="form-control" id="date_fin" name="date_fin" 
                           value="<?php echo $date_fin; ?>" required>
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-success w-100">Analyser</button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($zone_choisie): ?>
        
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title text-danger">Température de l'eau (°C)</h5>
                        <div class="chart-container">
                            <canvas id="graphTemp"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Salinité (PSU)</h5>
                        <div class="chart-container">
                            <canvas id="graphSalinite"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title text-success">Chlorophylle-a (mg/m3)</h5>
                        <div class="chart-container">
                            <canvas id="graphChloro"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <div class="alert alert-info text-center">
            Veuillez sélectionner une zone et une période ci-dessus pour afficher les statistiques.
        </div>
    <?php endif; ?>

</div>

<!-- Import de la librairie Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // 1. Récupération des paramètres PHP dans le JS
    const zone = "<?php echo $zone_choisie; ?>";
    const dateDebut = "<?php echo $date_debut; ?>";
    const dateFin = "<?php echo $date_fin; ?>";

    // 1.5 Chargement dynamique des zones depuis l'API
    fetch('http://127.0.0.1:8000/api/zones')
        .then(response => response.json())
        .then(zones => {
            const select = document.getElementById('zone');
            zones.forEach(z => {
                const option = document.createElement('option');
                option.value = z.slug;
                option.textContent = z.name;
                // Si c'était la zone choisie avant rechargement, on la resélectionne
                if (z.slug === zone) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
        })
        .catch(error => console.error("Erreur chargement zones:", error));

    // 2. Si les paramètres sont présents, on appelle l'API
    if (zone && dateDebut && dateFin) {
        
        // TODO: Adaptez cette URL selon l'emplacement réel de votre API
        const apiUrl = `http://127.0.0.1:8000/api/stats?zone=${zone}&date_debut=${dateDebut}&date_fin=${dateFin}`;

        fetch(apiUrl)
            .then(response => {
                if (!response.ok) throw new Error("Erreur réseau ou API introuvable");
                return response.json();
            })
            .then(data => {
                // Fonction utilitaire pour créer un graphique
                const createChart = (canvasId, label, dataValues, labels, color) => {
                    const ctx = document.getElementById(canvasId).getContext('2d');
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels, // Axe X (Dates)
                            datasets: [{
                                label: label,
                                data: dataValues, // Axe Y (Valeurs)
                                borderColor: color,
                                backgroundColor: color,
                                tension: 0.1,
                                fill: false
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: { x: { display: true }, y: { beginAtZero: false } }
                        }
                    });
                };

                // 3. Initialisation des graphiques avec les données reçues
                // On suppose que l'API renvoie un objet JSON avec : dates, temperature, salinite, chlorophylle
                if (data.dates) {
                    createChart('graphTemp', 'Température (°C)', data.temperature, data.dates, 'rgb(255, 99, 132)');
                    createChart('graphSalinite', 'Salinité (PSU)', data.salinite, data.dates, 'rgb(54, 162, 235)');
                    createChart('graphChloro', 'Chlorophylle-a (mg/m3)', data.chlorophylle, data.dates, 'rgb(75, 192, 192)');
                }
            })
            .catch(error => console.error("Erreur lors du chargement des données :", error));
    }
</script>

</body>
</html>