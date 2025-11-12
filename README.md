# 📚 Guide Symfony pour Étudiants - 2ème Année

## 🎯 Introduction

Bienvenue dans ce guide Symfony destiné aux étudiants en deuxième année ! Ce projet utilise **Symfony 7.3** avec **Doctrine ORM** pour la gestion de la base de données.

Symfony est un framework PHP robuste qui suit l'architecture **MVC (Model-View-Controller)** et utilise des **design patterns** reconnus.

---

## 🏗️ Structure du Projet

Voici la structure principale d'un projet Symfony :

```
cours_symfony/
├── 📁 bin/                     # Scripts exécutables
│   ├── console                 # Console Symfony
│   └── phpunit                 # Tests unitaires
├── 📁 config/                  # Configuration de l'application
│   ├── bundles.php            # Bundles enregistrés
│   ├── services.yaml          # Configuration des services
│   ├── routes.yaml            # Routes globales
│   └── packages/              # Configuration des packages
├── 📁 public/                  # Point d'entrée web
│   ├── index.php              # Front controller
│   └── img/                   # Images publiques
├── 📁 src/                     # Code source de l'application
│   ├── Controller/            # Contrôleurs MVC
│   ├── Entity/                # Entités Doctrine (Modèles)
│   ├── Repository/            # Repositories Doctrine
│   └── Kernel.php             # Noyau de l'application
├── 📁 templates/               # Templates Twig (Vues)
│   ├── base.html.twig         # Template de base
│   └── [module]/              # Templates par module
├── 📁 migrations/              # Migrations de base de données
├── 📁 var/                     # Fichiers temporaires
│   ├── cache/                 # Cache de l'application
│   └── log/                   # Logs de l'application
├── 📁 vendor/                  # Dépendances Composer
├── 📁 assets/                  # Assets frontend
├── 📁 tests/                   # Tests automatisés
└── composer.json               # Dépendances PHP
```

### 📋 Rôle de chaque dossier

- **`src/Controller/`** : Contient la logique métier et gère les requêtes HTTP
- **`src/Entity/`** : Définit les modèles de données (tables de BDD)
- **`src/Repository/`** : Contient les requêtes personnalisées vers la BDD
- **`templates/`** : Contient les vues Twig (interface utilisateur)
- **`config/`** : Configuration de l'application et des services

---

## 🗄️ Doctrine ORM - Comprendre la Persistance

### 📊 Schéma de fonctionnement avec l'entité Post

```mermaid
flowchart TD
    Controller[🎮 Controller<br/>ActuController]
    Repository[🔍 Repository<br/>PostRepository]
    EntityManager[⚙️ EntityManager<br/>Gestionnaire principal]
    Entity[📋 Entity<br/>Post.php]
    DB[(🗄️ Base de Données<br/>Table: post)]
    
    Controller -->|1. Demande données| Repository
    Repository -->|2. Utilise| EntityManager
    EntityManager -->|3. Requête SQL| DB
    DB -->|4. Données brutes| EntityManager
    EntityManager -->|5. Hydrate| Entity
    Entity -->|6. Objets PHP| Repository
    Repository -->|7. Retourne| Controller
    
    Controller -->|Pour sauvegarder| EntityManager
    EntityManager -->|Méthode persist| Entity
    EntityManager -->|Méthode flush| DB
```

### 🔄 Cycle de vie d'une entité

```mermaid
stateDiagram-v2
    [*] --> New : new Post()
    New --> Managed : persist()
    Managed --> Database : flush()
    Database --> Managed : find()
    Managed --> Removed : remove()
    Removed --> Database : flush()
    Database --> [*]
    
    note right of New : Objet créé mais pas géré par Doctrine
    note right of Managed : Objet suivi par l'EntityManager
    note right of Removed : Marqué pour suppression
```

---

## ⚡ Commandes Symfony Essentielles

### 🚀 Commandes de développement

```bash
# Démarrer le serveur de développement
symfony server:start

# Démarrer en arrière-plan
symfony server:start -d

# Arrêter le serveur
symfony server:stop

# Voir les logs en temps réel
symfony server:log
```

### 🏗️ Génération de code

```bash
# Créer un contrôleur
php bin/console make:controller NomController

# Créer une entité
php bin/console make:entity NomEntite

# Créer un formulaire
php bin/console make:form

# Créer un repository personnalisé
php bin/console make:repository

# Créer un service
php bin/console make:service

# Créer un event et son listener
php bin/console make:event

# Créer un subscriber
php bin/console make:subscriber

# Voir toutes les commandes make
php bin/console list make
```

### 🗄️ Commandes Doctrine

#### Gestion de la base de données
```bash
# Créer la base de données
php bin/console doctrine:database:create

# Supprimer la base de données
php bin/console doctrine:database:drop --force

# Vérifier le mapping des entités
php bin/console doctrine:schema:validate
```

#### Migrations
```bash
# Générer une migration automatiquement
php bin/console make:migration

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# Voir le statut des migrations
php bin/console doctrine:migrations:status

# Revenir à une migration précédente
php bin/console doctrine:migrations:migrate prev
```

#### Fixtures (données de test)
```bash
# Installer le bundle fixtures
composer require --dev orm-fixtures

# Charger les fixtures
php bin/console doctrine:fixtures:load
```

### 🔧 Outils de développement

```bash
# Vider le cache
php bin/console cache:clear

# Lister toutes les routes
php bin/console debug:router

# Voir les services disponibles
php bin/console debug:container

# Voir la configuration
php bin/console debug:config

# Debug d'un service spécifique
php bin/console debug:container PostService

# Voir les événements disponibles
php bin/console debug:event-dispatcher

# Valider les contraintes des entités
php bin/console doctrine:schema:validate

# Lancer les tests
php bin/phpunit
```

---

## 💾 Doctrine en Action - Exemples Pratiques

### 📝 Entité Post (Modèle)

Notre entité `Post` représente un article de blog :

```php
// src/Entity/Post.php
#[ORM\Entity(repositoryClass: PostRepository::class)]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    // ... getters et setters
}
```

### 🔍 Repository (Requêtes personnalisées)

Le repository permet de créer des requêtes spécifiques :

```php
// src/Repository/PostRepository.php
class PostRepository extends ServiceEntityRepository
{
    // Trouver les posts publiés
    public function findPublishedPosts(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.is_published = :published')
            ->setParameter('published', true)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // Trouver par auteur
    public function findByAuthor(string $author): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.author = :author')
            ->setParameter('author', $author)
            ->getQuery()
            ->getResult();
    }
}
```

### 🎮 Utilisation dans un Contrôleur

```php
// src/Controller/ActuController.php
<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use App\Service\PostService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ActuController extends AbstractController
{
    #[Route('/actu', name: 'app_actu')]
    public function index(PostRepository $postRepository): Response
    {
        // Récupérer tous les posts publiés
        $posts = $postRepository->findPublishedPosts();

        return $this->render('actu/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/actu/new', name: 'app_actu_new')]
    public function new(PostService $postService): Response
    {
        // ✅ Bonne pratique : utiliser un service pour la logique métier
        $post = $postService->createPost(
            'Mon nouveau post',
            'Contenu du post...',
            'Auteur'
        );

        return $this->redirectToRoute('app_actu');
    }

    // Exemple avec EntityManager direct (pour l'apprentissage)
    #[Route('/actu/direct', name: 'app_actu_direct')]
    public function createDirect(EntityManagerInterface $entityManager): Response
    {
        // Créer un nouveau post
        $post = new Post();
        $post->setTitle('Post direct');
        $post->setContent('Contenu...');
        $post->setCreatedAt(new \DateTimeImmutable());
        $post->setIsPublished(true);
        $post->setAuthor('Admin');

        // 1. Dire à Doctrine de gérer cet objet
        $entityManager->persist($post);

        // 2. Exécuter les requêtes SQL
        $entityManager->flush();

        return $this->redirectToRoute('app_actu');
    }
}
```

### 💡 Comprendre `persist()` et `flush()`

#### 🔄 `persist()` : Préparer la sauvegarde
```php
$post = new Post();
$post->setTitle('Nouveau post');

// persist() dit à Doctrine : "surveille cet objet"
$entityManager->persist($post);
// ⚠️ Rien n'est encore sauvé en base !
```

#### 💾 `flush()` : Exécuter la sauvegarde
```php
// flush() exécute TOUTES les requêtes SQL en attente
$entityManager->flush();
// ✅ Maintenant le post est sauvé en base de données
```

#### 🎯 Exemple complet avec plusieurs objets
```php
$post1 = new Post();
$post1->setTitle('Post 1');
$entityManager->persist($post1);  // Préparé

$post2 = new Post();
$post2->setTitle('Post 2');
$entityManager->persist($post2);  // Préparé

$post3 = new Post();
$post3->setTitle('Post 3');
$entityManager->persist($post3);  // Préparé

// Une seule transaction pour tout sauvegarder
$entityManager->flush();  // 3 INSERT exécutés d'un coup
```

---

## 🔧 Services et Injection de Dépendances

### 🛠️ Qu'est-ce qu'un Service ?

Un **service** est une classe qui effectue une tâche spécifique. Dans Symfony, les services sont gérés par le **conteneur d'injection de dépendances**.

### 📝 Créer un Service PostService

```php
// src/Service/PostService.php
<?php

namespace App\Service;

use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;

class PostService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function createPost(string $title, string $content, ?string $author = null): Post
    {
        $post = new Post();
        $post->setTitle($title);
        $post->setContent($content);
        $post->setAuthor($author);
        $post->setCreatedAt(new \DateTimeImmutable());
        $post->setIsPublished(false); // Par défaut non publié

        $this->entityManager->persist($post);
        $this->entityManager->flush();

        return $post;
    }

    public function publishPost(Post $post): void
    {
        $post->setIsPublished(true);
        $post->setUpdatedAt(new \DateTimeImmutable());
        
        $this->entityManager->flush();
    }

    public function unpublishPost(Post $post): void
    {
        $post->setIsPublished(false);
        $post->setUpdatedAt(new \DateTimeImmutable());
        
        $this->entityManager->flush();
    }
}
```

### 🔄 Enregistrement automatique des Services

Symfony 7.3 enregistre automatiquement tous les services dans `src/`. Configuration dans `config/services.yaml` :

```yaml
services:
    _defaults:
        autowire: true      # Injection automatique des dépendances
        autoconfigure: true # Configuration automatique
        public: false       # Services privés par défaut

    App\:
        resource: '../src/'
        exclude:
            - '../src/DependencyInjection/'
            - '../src/Entity/'
            - '../src/Kernel.php'
```

---

## ✅ Validation des Données

### 🛡️ Ajouter des contraintes de validation

```php
// src/Entity/Post.php
<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PostRepository::class)]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le titre ne peut pas être vide')]
    #[Assert\Length(
        min: 5,
        max: 255,
        minMessage: 'Le titre doit faire au moins {{ limit }} caractères',
        maxMessage: 'Le titre ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Le contenu ne peut pas être vide')]
    #[Assert\Length(
        min: 10,
        minMessage: 'Le contenu doit faire au moins {{ limit }} caractères'
    )]
    private ?string $content = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    private ?bool $is_published = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Le nom de l\'auteur ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $author = null;

    // ... getters et setters
}
```

### 🔍 Validation dans un Service

```php
// src/Service/PostService.php (mise à jour)
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class PostService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator
    ) {}

    public function createPost(string $title, string $content, ?string $author = null): Post
    {
        $post = new Post();
        $post->setTitle($title);
        $post->setContent($content);
        $post->setAuthor($author);
        $post->setCreatedAt(new \DateTimeImmutable());
        $post->setIsPublished(false);

        // Validation
        $violations = $this->validator->validate($post);
        if (count($violations) > 0) {
            throw new ValidationFailedException($post, $violations);
        }

        $this->entityManager->persist($post);
        $this->entityManager->flush();

        return $post;
    }
}
```

---

## 🎭 Events et Listeners Symfony 7.3

### 📡 Créer un Event personnalisé

```php
// src/Event/PostCreatedEvent.php
<?php

namespace App\Event;

use App\Entity\Post;
use Symfony\Contracts\EventDispatcher\Event;

class PostCreatedEvent extends Event
{
    public function __construct(
        private Post $post
    ) {}

    public function getPost(): Post
    {
        return $this->post;
    }
}
```

### 👂 Créer un Listener

```php
// src/EventListener/PostCreatedListener.php
<?php

namespace App\EventListener;

use App\Event\PostCreatedEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: PostCreatedEvent::class)]
class PostCreatedListener
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    public function __invoke(PostCreatedEvent $event): void
    {
        $post = $event->getPost();
        
        $this->logger->info('Nouveau post créé', [
            'post_id' => $post->getId(),
            'title' => $post->getTitle(),
            'author' => $post->getAuthor(),
        ]);

        // Ici on pourrait envoyer un email, notifier les admins, etc.
    }
}
```

### 🚀 Dispatcher l'Event dans le Service

```php
// src/Service/PostService.php (mise à jour finale)
use App\Event\PostCreatedEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class PostService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private EventDispatcherInterface $eventDispatcher
    ) {}

    public function createPost(string $title, string $content, ?string $author = null): Post
    {
        $post = new Post();
        $post->setTitle($title);
        $post->setContent($content);
        $post->setAuthor($author);
        $post->setCreatedAt(new \DateTimeImmutable());
        $post->setIsPublished(false);

        // Validation
        $violations = $this->validator->validate($post);
        if (count($violations) > 0) {
            throw new ValidationFailedException($post, $violations);
        }

        $this->entityManager->persist($post);
        $this->entityManager->flush();

        // Dispatcher l'événement
        $this->eventDispatcher->dispatch(new PostCreatedEvent($post));

        return $post;
    }
}
```

---

## 🎨 AssetMapper - Gestion des Assets (Symfony 6.3+)

### 📦 Qu'est-ce qu'AssetMapper ?

AssetMapper est le nouveau système de gestion des assets de Symfony, remplaçant Webpack Encore pour les cas simples.

### 🔧 Commandes AssetMapper

```bash
# Installer une dépendance JavaScript
php bin/console importmap:install bootstrap

# Installer jQuery
php bin/console importmap:install jquery

# Voir les packages disponibles
php bin/console importmap:outdated

# Compiler les assets pour la production
php bin/console asset-map:compile

# Lister les assets mappés
php bin/console debug:asset-map
```

### 📝 Configuration (`importmap.php`)

```php
<?php

return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    'bootstrap' => [
        'version' => '5.3.2',
    ],
    'bootstrap/dist/css/bootstrap.min.css' => [
        'version' => '5.3.2',
        'type' => 'css',
    ],
];
```

### 🎯 Utilisation dans les templates

```twig
{# templates/base.html.twig #}
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>{% block title %}Cours Symfony{% endblock %}</title>
        
        {# CSS via AssetMapper #}
        {{ importmap('app') }}
        {% block stylesheets %}{% endblock %}
    </head>
    <body>
        {% block body %}{% endblock %}
        
        {# JavaScript via AssetMapper #}
        {{ importmap() }}
        {% block javascripts %}{% endblock %}
    </body>
</html>
```

---

## 🔧 Configuration de Base de Données

### 📝 Fichier `.env` et `.env.local`

```bash
# .env - Configuration par défaut (versionnée)
DATABASE_URL="mysql://username:password@127.0.0.1:3306/cours_symfony"
# ou pour PostgreSQL
# DATABASE_URL="postgresql://username:password@127.0.0.1:5432/cours_symfony"

# Variables d'environnement
APP_ENV=dev
APP_SECRET=your-secret-key
```

```bash
# .env.local - Configuration locale (NON versionnée)
# Créer ce fichier pour vos configurations personnelles
DATABASE_URL="mysql://root:password@127.0.0.1:3306/cours_symfony_local"
```

> ⚠️ **Important** : Ne jamais versionner `.env.local` - il contient vos configurations personnelles !

### ⚙️ Configuration Doctrine (`config/packages/doctrine.yaml`)
```yaml
doctrine:
    dbal:
        url: '%env(resolve:DATABASE_URL)%'
    orm:
        auto_generate_proxy_classes: true
        naming_strategy: doctrine.orm.naming_strategy.underscore_number_aware
        auto_mapping: true
```

---

## 🎨 Templates Twig - Les Vues

### 📄 Template de base (`templates/base.html.twig`)
```twig
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>{% block title %}Cours Symfony{% endblock %}</title>
        <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 128 128%22><text y=%221.2em%22 font-size=%2296%22>⚫️</text></svg>">
        {% block stylesheets %}{% endblock %}
    </head>
    <body>
        <nav class="navbar">
            <a href="{{ path('app_home') }}">Accueil</a>
            <a href="{{ path('app_actu') }}">Actualités</a>
        </nav>
        
        <main>
            {% block body %}{% endblock %}
        </main>
        
        {% block javascripts %}{% endblock %}
    </body>
</html>
```

### 📋 Template de liste (`templates/actu/index.html.twig`)
```twig
{% extends 'base.html.twig' %}

{% block title %}Actualités{% endblock %}

{% block body %}
<h1>Liste des Articles</h1>

<div class="posts">
    {% for post in posts %}
        <article class="post">
            <h2>{{ post.title }}</h2>
            <p class="meta">
                Par {{ post.author ?? 'Anonyme' }} 
                le {{ post.createdAt|date('d/m/Y') }}
            </p>
            <p>{{ post.content|slice(0, 200) }}...</p>
            <a href="{{ path('app_actu_show', {id: post.id}) }}">
                Lire la suite
            </a>
        </article>
    {% else %}
        <p>Aucun article publié pour le moment.</p>
    {% endfor %}
</div>
{% endblock %}
```

---

## 🚀 Workflow de Développement

### 1. 🎯 Créer une nouvelle fonctionnalité

```bash
# 1. Créer l'entité
php bin/console make:entity Article

# 2. Générer la migration
php bin/console make:migration

# 3. Appliquer la migration
php bin/console doctrine:migrations:migrate

# 4. Créer le contrôleur
php bin/console make:controller ArticleController

# 5. Créer le repository si nécessaire
php bin/console make:repository Article
```

### 2. 🔄 Cycle de développement typique

```bash
# Modifier le code...

# Vérifier les erreurs
php bin/console lint:twig templates/
php bin/console lint:yaml config/

# Vider le cache si nécessaire
php bin/console cache:clear

# Tester l'application
symfony server:start
```

---

## 🛠️ Conseils et Bonnes Pratiques

### ✅ Do (À faire)
- Toujours utiliser `persist()` avant `flush()`
- Gérer les erreurs avec des try/catch
- Valider les données avant la sauvegarde
- Utiliser les repositories pour les requêtes complexes
- Suivre la convention de nommage Symfony

### ❌ Don't (À éviter)
- Ne jamais faire de `flush()` sans `persist()`
- Éviter les requêtes SQL directes
- Ne pas oublier de faire les migrations
- Ne pas stocker de logique métier dans les entités
- Éviter les contrôleurs trop volumineux

### 🔍 Debug et Dépannage

```bash
# Voir les requêtes SQL exécutées
php bin/console doctrine:query:sql "SELECT * FROM post"

# Vérifier la configuration
php bin/console debug:config doctrine

# Voir les routes disponibles
php bin/console debug:router | grep post

# Profiler web (en développement)
# Accéder à /_profiler après une requête
```

---

## 📚 Ressources Supplémentaires

- 📖 [Documentation officielle Symfony](https://symfony.com/doc/current/)
- 🗄️ [Documentation Doctrine](https://www.doctrine-project.org/projects/orm/en/current/)
- 🎨 [Documentation Twig](https://twig.symfony.com/doc/)
- 🚀 [Symfony Casts](https://symfonycasts.com/) (tutoriels vidéo)

---

## 🎓 Exercices Pratiques

1. **Créer une entité `Category`** avec une relation OneToMany vers `Post`
2. **Ajouter une méthode dans `PostRepository`** pour trouver les posts par catégorie
3. **Créer un service `CategoryService`** pour gérer les catégories
4. **Implémenter la validation** sur l'entité Category avec des contraintes personnalisées
5. **Créer un Event `PostPublishedEvent`** qui se déclenche lors de la publication
6. **Ajouter un Listener** qui envoie un email lors de la publication d'un post
7. **Utiliser AssetMapper** pour ajouter Bootstrap et du JavaScript personnalisé
8. **Créer un formulaire** pour ajouter un nouveau post avec validation
9. **Implémenter la pagination** pour la liste des posts
10. **Créer des fixtures** pour générer des données de test

### 🎯 Projet final suggéré
Créer un **blog complet** avec :
- Gestion des catégories
- Système de commentaires
- Interface d'administration
- API REST pour les posts
- Tests unitaires et fonctionnels

---

**Bonne découverte de Symfony ! 🚀**

> **Rappel important** : Symfony suit le principe "Convention over Configuration". Plus vous respectez les conventions, moins vous avez de configuration à faire !

## 🆕 Nouveautés Symfony 7.3

- **AssetMapper** : Alternative simple à Webpack Encore
- **Attributs PHP 8** : Plus de configuration YAML pour les routes et entités
- **Performance améliorée** : Cache optimisé et autoloading plus rapide
- **Autowiring avancé** : Injection de dépendances encore plus automatique
- **Events avec attributs** : `#[AsEventListener]` pour simplifier la configuration

## 📊 Aide-mémoire - Commandes essentielles

```bash
# 🚀 Développement
symfony server:start              # Démarrer le serveur
php bin/console cache:clear       # Vider le cache

# 🏗️ Génération
php bin/console make:entity       # Créer une entité
php bin/console make:controller   # Créer un contrôleur
php bin/console make:service      # Créer un service

# 🗄️ Base de données
php bin/console make:migration    # Créer une migration
php bin/console doctrine:migrations:migrate  # Appliquer les migrations

# 🔍 Debug
php bin/console debug:router      # Voir les routes
php bin/console debug:container   # Voir les services
```