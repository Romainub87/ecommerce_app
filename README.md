# Ecommerce application

## Prérequis

- [Composer](https://getcomposer.org/download/)
- [npm](https://nodejs.org/en/download/)
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) ou [Docker via WSL](https://docs.docker.com/desktop/wsl/)

## Installation
1. Cloner le dépôt et se déplacer dans le répertoire du projet
   ```bash
   git clone git@github.com:Romainub87/ecommerce_app.git
   cd ecommerce_app
    ```
2. Installer les dépendances PHP
   ```bash
   composer install
   ``` 
3. Installer les dépendances JavaScript
   ```bash
    npm install
    ```
4. Compiler les assets
    ```bash
    npm run watch
    ```
5. Exécuter les migrations.
    ```bash
    php bin/console doctrine:migrations:diff
    php bin/console doctrine:migrations:migrate
    ```
6. Lancer les conteneurs docker
   ```bash
   docker compose up -d
   ```
7. Lancer l'application symfony
   ```bash
   symfony serve
   ```
8. Accéder à l'application \
   Ouvrir un navigateur et aller à l'adresse [http://localhost:8000](http://localhost:8000)

## Langages & frameworks

- **PHP 8.2** : Langage principal côté serveur, utilisé pour la logique métier et la gestion des requêtes.
- **Symfony 6.4** : Framework PHP moderne facilitant le développement structuré, la sécurité et la maintenabilité.
- **Twig** : Moteur de templates pour générer dynamiquement les vues HTML.
- **Tailwind CSS** : Framework CSS utilitaire pour un design rapide et réactif.
- **JsonServer** : Simule une API REST pour le développement et les tests front-end.
- **Docker** : Conteneurisation pour garantir la portabilité et la cohérence de l’environnement de développement et de test

La plupart des technologies qui sont utilisées ici sont celles que j'ai le plus pratiquées dans le cadre de mon travail. 
Symfony est mon framework PHP préféré, et j'apprécie particulièrement la flexibilité de Twig pour la création de vues. 
Tailwind CSS est devenu mon choix privilégié pour le design en raison de sa rapidité et de sa facilité d'utilisation.
JsonServer est un outil pratique pour simuler des API REST, ce qui facilite le développement front-end sans dépendre d'un backend réel.
Enfin, Docker rend le processus de développement et de déploiement beaucoup plus fluide en standardisant l'environnement.

A travers ce projet, j'ai pu mettre en pratique des tests unitaires et fonctionnels, ainsi que des tests d'intégration avec l'utilisation de Pest. 
Je n'avais jamais utilisé Pest auparavant, mais j'ai trouvé que c'était un excellent outil pour écrire des tests de manière fluide.

## Tests
Pour exécuter les tests, utilisez la commande suivante :
```bash
  ./vendor/bin/pest
```
