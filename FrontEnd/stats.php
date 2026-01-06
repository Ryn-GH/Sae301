<?php
$page_title = 'Statistiques - AquaVision';
$zone_choisie = isset($_GET['zone']) ? htmlspecialchars($_GET['zone']) : null;
$date_debut = isset($_GET['date_debut']) ? htmlspecialchars($_GET['date_debut']) : null;
$date_fin = isset($_GET['date_fin']) ? htmlspecialchars($_GET['date_fin']) : null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Statistiques - AquaVision</title>
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;700&display=swap" rel="stylesheet" />
    
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#1193d4",
                        "background-light": "rgb(224 233 241);",
                        "background-dark": "#101c22",
                        "abyss": "#080f13",
                    },
                    fontFamily: {
                        "display": ["Space Grotesk", "sans-serif"]
                    },
                },
            },
        }
    </script>
    <style>
        /* CSS Overlay Transition */
        .transition-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            z-index: 9999;
            pointer-events: none;
            display: flex;
            filter: url('#goo');
            padding: 0 -10px;
        }

        .transition-strip {
            flex: 1;
            height: 100%;
            background-color: #101c22;
            transform: scaleY(0);
            transform-origin: top;
            margin: 0 -5px;
        }

        .nav-roll {
            position: relative;
            overflow: hidden;
            display: inline-block;
            line-height: 1.2;
            text-decoration: none;
        }

        .nav-roll span {
            display: block;
            transition: transform 0.4s cubic-bezier(0.76, 0, 0.24, 1);
        }

        .nav-roll::after {
            content: attr(data-text);
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            height: 100%;
            transition: transform 0.4s cubic-bezier(0.76, 0, 0.24, 1);
            color: #1193d4;
        }

        .nav-roll:hover span,
        .nav-roll:hover::after {
            transform: translateY(-100%);
        }
    </style>
</head>
<body data-barba="wrapper" class="bg-background-light dark:bg-background-dark font-display text-gray-800 dark:text-gray-200 overflow-x-hidden">

    <svg style="visibility: hidden; position: absolute;" width="0" height="0" xmlns="http://www.w3.org/2000/svg" version="1.1">
        <defs>
            <filter id="goo">
                <feGaussianBlur in="SourceGraphic" stdDeviation="15" result="blur" />
                <feColorMatrix in="blur" mode="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 25 -9" result="goo" />
                <feComposite in="SourceGraphic" in2="goo" operator="atop" />
            </filter>
        </defs>
    </svg>

    <div class="transition-overlay">
        <div class="transition-strip"></div>
        <div class="transition-strip"></div>
        <div class="transition-strip"></div>
        <div class="transition-strip"></div>
        <div class="transition-strip"></div>
        <div class="transition-strip"></div>
        <div class="transition-strip"></div>
    </div>

    <div data-barba="container" data-barba-namespace="stats" class="relative w-full flex flex-col min-h-screen">

        <header class="fixed top-0 left-0 right-0 z-50 flex items-center justify-between whitespace-nowrap px-10 py-4 bg-background-light/80 dark:bg-background-dark/80 backdrop-blur-sm border-b border-primary/20">
            <div class="flex items-center gap-3 text-primary">
                <div class="size-8">
                    <svg fill="none" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_6_319)">
                            <path d="M8.57829 8.57829C5.52816 11.6284 3.451 15.5145 2.60947 19.7452C1.76794 23.9758 2.19984 28.361 3.85056 32.3462C5.50128 36.3314 8.29667 39.7376 11.8832 42.134C15.4698 44.5305 19.6865 45.8096 24 45.8096C28.3135 45.8096 32.5302 44.5305 36.1168 42.134C39.7033 39.7375 42.4987 36.3314 44.1494 32.3462C45.8002 28.361 46.2321 23.9758 45.3905 19.7452C44.549 15.5145 42.4718 11.6284 39.4217 8.57829L24 24L8.57829 8.57829Z" fill="currentColor"></path>
                        </g>
                        <defs>
                            <clipPath id="clip0_6_319">
                                <rect fill="white" height="48" width="48"></rect>
                            </clipPath>
                        </defs>
                    </svg>
                </div>
                <h2 class="text-xl font-bold">AquaVision</h2>
            </div>

            <nav class="hidden md:flex absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 items-center gap-8">
                <a class="nav-roll text-sm font-medium text-gray-800 dark:text-gray-200" href="landing_page.html" data-text="Accueil" data-barba-prevent><span>Accueil</span></a>
                <a class="nav-roll text-sm font-medium text-gray-800 dark:text-gray-200" href="#" data-text="Carte Interactive"><span>Carte Interactive</span></a>
                <a class="nav-roll text-sm font-medium text-gray-800 dark:text-gray-200" href="stats.php" data-text="Graphiques"><span>Graphiques</span></a>
                <a class="nav-roll text-sm font-medium text-gray-800 dark:text-gray-200" href="Sources.html" data-text="Sources"><span>Sources</span></a>
            </nav>
            <div class="flex items-center gap-8"></div>
        </header>
    
        <main class="flex-grow pt-32 pb-12 px-6 lg:px-10">
            <div class="max-w-7xl mx-auto">
                
                <div class="text-center mb-12">
                    <h1 class="text-4xl md:text-5xl font-bold tracking-tight mb-4">Observatoire Océanique</h1>
                    <p class="text-lg text-gray-600 dark:text-gray-400">Analyse des données environnementales</p>
                </div>

                <!-- Formulaire -->
                <div class="bg-white/50 dark:bg-background-dark/50 border border-gray-200 dark:border-gray-800 rounded-2xl p-8 mb-12 shadow-sm backdrop-blur-sm">
                    <div class="mb-6 border-b border-gray-200 dark:border-gray-700 pb-4">
                        <h5 class="text-xl font-semibold text-primary">Paramètres d'analyse</h5>
                    </div>
                    
                    <form action="" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">
                        <div class="md:col-span-4">
                            <label for="zone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Zone Maritime</label>
                            <select id="zone" name="zone" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-background-dark text-gray-900 dark:text-gray-100 focus:border-primary focus:ring-primary">
                                <option value="" disabled selected>Choisir une zone...</option>
                            </select>
                        </div>

                        <div class="md:col-span-3">
                            <label for="date_debut" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date de début</label>
                            <input type="date" id="date_debut" name="date_debut" value="<?php echo $date_debut; ?>" max="<?php echo date('Y-m-d'); ?>" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-background-dark text-gray-900 dark:text-gray-100 focus:border-primary focus:ring-primary">
                        </div>

                        <div class="md:col-span-3">
                            <label for="date_fin" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date de fin</label>
                            <input type="date" id="date_fin" name="date_fin" value="<?php echo $date_fin; ?>" max="<?php echo date('Y-m-d'); ?>" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-background-dark text-gray-900 dark:text-gray-100 focus:border-primary focus:ring-primary">
                        </div>

                        <div class="md:col-span-2">
                            <button type="submit" class="w-full h-[42px] bg-primary hover:bg-primary/90 text-white font-bold rounded-lg transition-colors shadow-lg shadow-primary/20">
                                Analyser
                            </button>
                        </div>
                    </form>
                </div>

                <?php if ($zone_choisie): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Température -->
                        <div class="md:col-span-2 bg-white/50 dark:bg-background-dark/50 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-sm">
                            <h5 class="text-lg font-semibold text-red-500 mb-4">Température de l'eau (°C)</h5>
                            <div class="relative h-[300px] w-full">
                                <canvas id="graphTemp"></canvas>
                            </div>
                        </div>

                        <!-- Salinité -->
                        <div class="bg-white/50 dark:bg-background-dark/50 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-sm">
                            <h5 class="text-lg font-semibold text-blue-500 mb-4">Salinité (PSU)</h5>
                            <div class="relative h-[300px] w-full">
                                <canvas id="graphSalinite"></canvas>
                            </div>
                        </div>

                        <!-- Chlorophylle -->
                        <div class="bg-white/50 dark:bg-background-dark/50 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-sm">
                            <h5 class="text-lg font-semibold text-green-500 mb-4">Chlorophylle-a (mg/m3)</h5>
                            <div class="relative h-[300px] w-full">
                                <canvas id="graphChloro"></canvas>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-6 text-center">
                        <p class="text-blue-700 dark:text-blue-300">Veuillez sélectionner une zone et une période ci-dessus pour afficher les statistiques.</p>
                    </div>
                <?php endif; ?>

                <!-- Scripts déplacés DANS le conteneur pour être rechargés par le hook -->
                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/@barba/core"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>

    <script>
    (function () {
        const API_BASE_URL = 'http://localhost/sae301/erddap-api/public/api';
    
        // Récupération des variables PHP
        const zone = "<?php echo $zone_choisie; ?>"; 
        const dateDebut = "<?php echo $date_debut; ?>";
        const dateFin = "<?php echo $date_fin; ?>";

        // --- 2. FONCTION D'AFFICHAGE ---
        window.chargerDonneesEtGraphiques = function() {
            // A. Remplir le menu déroulant des zones depuis l'API
            const select = document.getElementById('zone');
            if (select && select.options.length <= 1) {
                fetch(`${API_BASE_URL}/zones`)
                    .then(response => response.json())
                    .then(zones => {
                        zones.forEach(z => {
                            const option = document.createElement('option');
                            option.value = z.slug;
                            option.textContent = z.name;
                            if (z.slug === zone) option.selected = true;
                            select.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error("Erreur lors de la récupération des zones:", error);
                    });
            }

            // B. Dates (simple synchro min/max)
            const inputDebut = document.getElementById('date_debut');
            const inputFin = document.getElementById('date_fin');
            if (inputDebut && inputFin) {
                inputDebut.addEventListener('change', function () { inputFin.min = this.value; });
                if (inputFin.value) {
                    inputDebut.max = inputFin.value;
                }
                inputFin.addEventListener('change', function () { inputDebut.max = this.value; });
            }

            // C. Dessiner les graphiques SI une zone et des dates sont sélectionnées
            if (zone && dateDebut && dateFin) {
                const url = `${API_BASE_URL}/stats?zone=${zone}&date_debut=${dateDebut}&date_fin=${dateFin}`;
                
                fetch(url)
                    .then(response => {
                        if (!response.ok) throw new Error(`Erreur HTTP: ${response.status}`);
                        return response.json();
                    })
                    .then(stats => {
                        if (stats.error) {
                            throw new Error(stats.error);
                        }

                        const createChart = (canvasId, label, dataValues, labels, color) => {
                            const canvas = document.getElementById(canvasId);
                            if (!canvas) return;

                            const ctx = canvas.getContext('2d');
                            const existingChart = Chart.getChart(ctx);
                            if (existingChart) existingChart.destroy();

                            new Chart(ctx, {
                                type: 'bar',
                                data: {
                                    labels: labels,
                                    datasets: [{
                                        label: label,
                                        data: dataValues,
                                        borderColor: color,
                                        backgroundColor: color,
                                        tension: 0.2,
                                        fill: true
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    animation: { duration: 800 },
                                    scales: {
                                        x: {
                                            ticks: {
                                                maxRotation: 70,
                                                minRotation: 45,
                                                autoSkip: true,
                                                maxTicksLimit: 20
                                            }
                                        }
                                    }
                                }
                            });
                        };

                        createChart('graphTemp', 'Température (°C)', stats.temperature, stats.dates, '#e21a1aff');
                        createChart('graphSalinite', 'Salinité (PSU)', stats.salinite, stats.dates, '#2a2ad3ff');
                        createChart('graphChloro', 'Chlorophylle-a (mg/m3)', stats.chlorophylle, stats.dates, '#27be27ff');
                    })
                    .catch(error => {
                        console.error("Erreur lors de la récupération des statistiques:", error);
                        const chartsContainer = document.querySelector('.grid.grid-cols-1.md\\:grid-cols-2.gap-8');
                        if (chartsContainer) {
                            chartsContainer.innerHTML = `<div class="md:col-span-2 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-6 text-center">
                                <p class="text-red-700 dark:text-red-300"><strong>Erreur :</strong> Impossible de charger les données. <br>(${error.message}).<br>Vérifiez que l'API est bien démarrée et accessible sur <em>${API_BASE_URL}</em>.</p>
                            </div>`;
                        }
                    });
            }
        };

        // --- 3. EXÉCUTION IMMÉDIATE ---
        window.chargerDonneesEtGraphiques();

        // --- 4. CONFIGURATION BARBA ---
        if (!window.barbaInitialized) {
            barba.init({
                transitions: [{
                    name: 'fade',
                    namespace: 'stats',
                    afterEnter(data) {
                        // Recharger les graphiques après la transition
                        window.chargerDonneesEtGraphiques();
                    },
                    enter(data) {
                        gsap.set('.transition-strip', { transformOrigin: 'bottom', scaleY: 1 });
                        gsap.to('.transition-strip', {
                            scaleY: 0, stagger: 0.05, duration: 0.5, delay: 0.1
                        });
                    }
                }]
            });
            window.barbaInitialized = true;
        }
    })();
    </script>
            </div>
        </main>
    </div>
</body>
</html>
