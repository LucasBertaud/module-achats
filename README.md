# Module Gestion des achats
Projet de groupe, réalisé par Lucas Bertaud et Lucas Madranges.
L'application a été conçue avec le language PHP.
Même si l'application est légère, nous recommandons au minimum PHP 8.0 pour éviter tout problème de compatibilité.  
## Conception
La table "produits" a été ajoutée dans les diagrammes MERISE, mais elle provient du module de stock réalisé par une autre équipe.
Nous l'avons schématisée pour mettre en lien la relation entre la table "produits" et la table "fournisseurs", qui est propre au module de Gestion des Achats.
## Au lancement
L'application est censée automiser lors du premier affichage la création de la base de données, de ses tables, de quelques données fictives, les triggers et les procédures stockées.
Toutefois, les requêtes sont disponibles dans le fichier **gestion_achats.sql**. Cependant, les requêtes ont été modifiées pour fonctionner via l'exécution d'une fonction PHP.
## Fonctionnalités
L'application offre la possibilité de voir - d'ajouter - de supprimer - de modifier une commande.