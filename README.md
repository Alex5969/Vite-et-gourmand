# Guide d'Installation et de Configuration Locale "Vite & Gourmand"

Ce document vous fournit des instructions pas à pas pour configurer votre environnement de développement local pour l'application "Vite & Gourmand" (ECF 2026). Il couvre l'installation de WampServer (Apache, PHP, MySQL), la configuration de la connectivité MongoDB Atlas, Composer, et le déploiement de votre code.

En cas de problème, n'hésitez pas à consulter la **Section 10. Dépannage Courant** à la fin de ce guide.



## 1. Prérequis

**Système d'exploitation :** Windows 10 ou supérieur (64 bits recommandé)

**Mémoire vive (RAM) :** 1 Go minimum (4 Go+ recommandé)

**Espace disque :** Suffisant pour les installations et votre projet

**Versions logicielles cibles :**

* Apache : 2.4.62.1
* PHP : 8.3.14
* MySQL : 9.3.0

**Conflits de ports :**

* Apache : 80
* MySQL : 3306



## 2. Installation de WampServer

**Téléchargement :** [wampserver.com/download/](http://www.wampserver.com/download/)

**Installation :**

* Lancez l’installateur en tant qu’administrateur.
* Répertoire par défaut : `C:\wamp64`.
* Assurez-vous que les versions : 
**Apache 2.4.62.1**, 
**PHP 8.3.14** et 
**MySQL 9.3.0** sont bien sélectionnées ou installées via les modules complémentaires.

**Vérification :**

* L’icône WampServer doit devenir **verte**.



## 3. Configuration de MongoDB Atlas (Base NoSQL)

Contrairement à MySQL, les données analytiques de "Vite & Gourmand" sont hébergées sur le Cloud via **MongoDB Atlas**.

**Préparation :**

* Créez un compte sur [mongodb.com/atlas](https://www.mongodb.com/cloud/atlas).
* Récupérez votre chaîne de connexion (URI) du type : `mongodb+srv://<user>:<password>@cluster0...`.
* La base de données `vite_et_gourmand_stats` et la collection `order_stats` seront créées automatiquement lors de la première commande.
* En production, ne jamais utiliser 0.0.0.0/0 mais restreindre aux IP serveur.
 

## 4. Configuration PHP pour MongoDB (Pilote)

Même avec une base Cloud, votre serveur local a besoin du pilote PHP pour communiquer.

**Accédez à** : `http://localhost/phpinfo.php`

* PHP Version : 8.3.14
* Thread Safety : TS
* Architecture : x64

**Téléchargement du pilote MongoDB :** 🔗 [https://pecl.php.net/package/mongodb](https://pecl.php.net/package/mongodb)

Fichier requis : `php_mongodb-X.X.X-8.3-ts-vs16-x64.zip` (Prendre la version compatible PHP 8.3).

**Installation :**

* Extraire et copier `php_mongodb.dll` dans `C:\wamp64\bin\php\php8.3.14\ext\`.
* Modifier `php.ini` :
 ini
extension=php_mongodb.dll

 



**Redémarrage de WampServer** - Redémarrer tous les services pour prendre en compte l'extension.

 

## 5. Installation de Composer et de la Bibliothèque PHP MongoDB

**Composer :**

* 🔗 [getcomposer.org/download](https://getcomposer.org/download/)
* Vérifiez avec : `composer --version`.

**Installation de la bibliothèque MongoDB dans le projet :**

 bash
cd C:\wamp64\www\ViteEtGourmand
composer require mongodb/mongodb

 

**Chargement des dépendances :**
Le fichier `includes/bootstrap.php` inclut automatiquement l'autoloader pour gérer les classes MongoDB.

 

## 6. Configuration du Pare-feu (Exceptions)

**Ajouter httpd.exe :**

* Chemin : `C:\wamp64\bin\apache\apache2.4.62.1\bin\httpd.exe`.

**Accès Sortant (Atlas) :**

* Autorisez le port **27017** en sortie pour permettre à PHP de se connecter au cluster MongoDB Atlas distant.

 

## 7. Gestion des Bases de Données (MySQL)

### 7.1. Base de Données MySQL 9.3.0

**Création via console :**
Ouvrez le terminal MySQL de WampServer et tapez :

 bash
mysql -u root
CREATE DATABASE vite_et_gourmand CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;

 

**Importation du fichier SQL :**

 bash
mysql -u root vite_et_gourmand < C:\wamp64\www\ViteEtGourmand\vite_et_gourmand.sql

 

 

## 8. Déploiement du Code "Vite & Gourmand"

1. Copiez vos fichiers dans `C:\wamp64\www\ViteEtGourmand\`.
2. Configurez vos accès (Cloud Atlas et MySQL Local) dans le fichier `secrets_config.php` situé à la racine ou dans le dossier de configuration.

 

## 9. Lancement et Accès à l'Application

**Démarrage de WampServer :**

* L’icône doit être verte.

**Accès navigateur (Front Controller) :**

 
http://localhost/ViteEtGourmand/public/index.php

 

 

## 10. Dépannage Courant

### Icône WampServer orange/rouge :

* Conflit de port 80 → Modifier `httpd.conf` pour écouter sur `8080`.
* Conflit de port 3306 (MySQL) → Vérifiez qu'une autre instance de MySQL (ou MariaDB) n'est pas déjà lancée.

### Erreurs PHP MongoDB (Driver) :

* Assurez-vous que la DLL est bien celle pour **PHP 8.3.14 TS x64**.
* Vérifiez que `extension=php_mongodb.dll` est présente dans le bon `php.ini`.

### Connexion Atlas refusée :

* Vérifiez que votre adresse IP actuelle est autorisée dans la "Network Access List" de votre interface MongoDB Atlas.
* Vérifiez l'URI de connexion dans `secrets_config.php` ou `MongoManager.php`.

### Erreur 404 / Route introuvable :

* Assurez-vous d'accéder au dossier `public/` qui contient le point d'entrée unique de l'architecture MVC.


### Vérification logs :

* logs Apache :
C:\wamp64\logs\apache_error.log
