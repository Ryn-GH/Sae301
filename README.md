# 🌊 Aquavision - SAE 3.01

**Aquavision** est une application web interactive conçue pour visualiser et analyser des données océanographiques (température de l'eau, salinité, chlorophylle A) réparties par zones maritimes. 

Ce projet a été réalisé dans le cadre de la **SAE 3.01** (Situation d'Apprentissage et d'Évaluation) et met en œuvre une architecture Cloud moderne, découplée et automatisée.

🔗 **[Voir le site en direct (Live Demo)](https://sae301-aquavison.vercel.app/landing_page)**

---

##  Fonctionnalités Principales

* **Cartographie Interactive (`map.html`) :** Visualisation spatiale des points de mesure et des zones maritimes.
* **Tableau de Bord Statistique (`stats.html`) :** Analyse croisée des données de salinité, de température et de taux de chlorophylle A.
* **Filtres Dynamiques :** Tri et affichage asynchrone des données via des requêtes API sans rechargement de page.

---

##  Architecture Technique

Le projet repose sur une architecture découplée (séparation stricte entre le client et le serveur) et déployée dans le Cloud via des pipelines CI/CD.

### 1. Front-End (Interface Utilisateur)
* **Technologies :** HTML5, CSS3, JavaScript (Vanilla).
* **Hébergement :** [Vercel](https://vercel.com/)
* **Principe :** Application statique interrogeant l'API de manière asynchrone (`fetch`). Déploiement continu à chaque push sur la branche `main` de GitHub.

### 2. Back-End (API REST)
* **Technologies :** Framework PHP **Laravel**, Docker.
* **Hébergement :** [Render](https://render.com/) (Conteneur Docker).
* **Principe :** API RESTful exposant les données au format JSON (ex: `/api/zones`). Conteneurisé via un `Dockerfile` personnalisé pour garantir un environnement PHP stable.

### 3. Base de Données
* **Technologies :** MySQL managé.
* **Hébergement :** [TiDB Cloud](https://tidbcloud.com/) (Serverless).
* **Principe :** Base de données relationnelle persistante contenant les tables `pointmesure`, `chlorophylle_a`, `salinite`, et `temperature`.

---

##  Points Techniques Avancés (Pour la soutenance)

* **Pipeline CI/CD :** Le code source hébergé sur GitHub alimente automatiquement Vercel (Front) et Render (Back) à chaque mise à jour.
* **Gestion du "Cold Start" Serverless :** Le back-end étant hébergé sur une instance gratuite (Render), il se met en veille en cas d'inactivité. L'application est conçue pour supporter ce délai de réveil initial (~30-50s) lors de la première requête.
* **Cache Persistant (Stateful) :** Pour contrer l'amnésie (Stateless) du serveur Render lors de ses redémarrages, le système de cache de Laravel a été déporté directement dans la base de données TiDB. Cela permet de conserver l'historique des "Cache Hits/Miss" de manière permanente.
